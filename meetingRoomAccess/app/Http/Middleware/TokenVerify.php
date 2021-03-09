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
        // $bot_token = 'Bearer '. env("GET_PROFILE_BOT_TOKEN");
        // $bot_id = env("GET_PROFILE_BOT_ID");
        // $api_path = env("GET_PROFILE_API");
        $bot_token = 'Bearer Af58c5450f3b45c71a97bc51c05373ecefabc49bd2cd94f3c88d5b844813e69a17e26a828c2b64ef889ef0c10e2aee347';
        $bot_id = 'B75900943c6205ce084d1c5e8850d40f9';
        $api_path = 'https://chat-api.one.th/manage/api/v1/getprofile';
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
