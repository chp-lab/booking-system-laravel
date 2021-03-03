<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TokenVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */


    public function handle(Request $request, Closure $next)
    {   
        $user_token = $request->header('Authorization');
        $bot_token = 'Bearer '. env("GET_PROFILE_BOT_TOKEN");
        $bot_id = env("GET_PROFILE_BOT_ID");
        $api_path = env("GET_PROFILE_API");
        $client = new \GuzzleHttp\Client();
        try{
            $guzzle_request = $client->post($api_path,[
                'headers' => [
                    'Authorization' => $bot_token,
                    'Content-Type' => 'application/json',
                ],
    
                'body' => json_encode([
                    'bot_id'=> $bot_id,
                    'source'=> $user_token,
                ])
            ]);
            $response = json_decode($guzzle_request->getBody()->getContents());
        }catch (\Exception $ex) {
            if(($ex->getResponse()->getStatusCode() == 401)){
                $m = 'token invalid';
            }else if(($ex->getResponse()->getStatusCode() == 400)){
                $m = 'bad request';
            }else if(($ex->getResponse()->getStatusCode() == 500)){
                $m = 'server error';
            }else{
                $m = '';
            }
            return response()->json([ 'Status' => 'fail',
                                        'Message' => $m
                                    ], $ex->getResponse()->getStatusCode());
        }
        if($response->status == 'success'){
            return $next($request);
        }else{
            return response()->json(['Status' => 'fail', 'Message' => 'unauthorized'], 401);
        }
    }
}
