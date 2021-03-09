<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Models\Guest;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $one_email =  'one_email';
    public $meeting_time_start =  'meeting_start';
    public $meeting_time_end =  'meeting_end';
    public $room_num =  'room_num';
    public $agenda =  'agenda';
    public $eject =  'eject_at';
    public $booking_num =  'booking_number';
    public $guest_email =  'guest_email';
    public $main_door =  'main_door';
    public $role =  'role';
    
    public $meeting_start_get;
    public $meeting_end_get;

    public function dev($booking_num = null){
        $guest_available = [];

        $users = User::all();
        $booking = Booking::where($this->booking_num, '=', $booking_num)->first();
        $guests = Guest::where($this->booking_num, '=', $booking_num)->get();

        $found = false;
        foreach($users as $u){
            foreach($guests as $g){
                if(($u->one_email == $g->guest_email)){
                    $found = true;
                }
            }
            if($u->one_email == $booking->one_email){
                $found = true; 
            }

            if(!$found){
                $guest_available[] = [
                    "email" => $u->one_email,
                    "name" => $u->name
                ];
            }
            $found = false;
        }
        return response()->json(['Status' => 'success', 'Message' => '', 'Value' => $guest_available], 200);
    }
    
    
    public function availableGuest($booking_num = null, $select = null){
        $guest_available = [];

        $users = User::all();
        $booking = Booking::where($this->booking_num, '=', $booking_num)->first();
        $guests = Guest::where($this->booking_num, '=', $booking_num)->get();
        if($booking == null){
            return response()->json(['Status' => 'success', 'Message' => 'booking number is invalid', 'Value' => ''], 400);
        }
        $found = false;
        foreach($users as $u){
            foreach($guests as $g){
                if(($u->one_email == $g->guest_email)){
                    $found = true;
                }
            }
            if($u->one_email == $booking->one_email){
                $found = true; 
            }

            if(!$found){
                if($select == 'email'){
                    array_push($guest_available, $u->one_email);
                }else{
                    $guest_available[] = [
                        "email" => $u->one_email,
                        "name" => $u->name
                    ]; 
                }
            }
            $found = false;
        }
        return response()->json(['Status' => 'success', 'Message' => '', 'Value' => $guest_available], 200);
    }
    
    public function guestManager(Request $request, $select = null){
        $timeNow = Carbon::now();
        $timeNow->tz = new \DateTimeZone('Asia/Bangkok');

        if( is_null($request->get($this->booking_num)) ||  is_null($request->get($this->guest_email)) ){
            return response()->json(['Status' => 'fail',
                                     'Message' => 'some value might be null',
                                     'Value' => ""], 400);
        }
    

        $bookingTable = Booking::where($this->booking_num , '=', $request->get($this->booking_num))
                                ->where($this->meeting_time_end, '>', $timeNow)
                                ->where($this->eject , '=', null)
                                ->first();

        if($bookingTable == null){
            return response()->json(['Status' => 'fail',
                                    'Message' => 'booking number is invalid',
                                    'Value' => ""], 400);
        }

        if($select == 'insert'){
            try{
                $get_guests = $request->get($this->guest_email);
                foreach($get_guests as $g){
                    $userTable = User::where($this->one_email , '=', $g)->first();
                    if($userTable == null){
                        return response()->json(['Status' => 'fail', 
                                                'Message' => 'guest email is invalid',
                                                'Value' => ""], 400);
                    }
                    $guests = Guest::where($this->booking_num , '=', $request->get($this->booking_num))
                                ->where($this->guest_email , '=', $g)
                                ->get();
                    
                    $booking = Booking::where($this->booking_num , '=', $request->get($this->booking_num))
                                        ->where($this->one_email , '=', $g)
                                        ->get();

                    //if guest dosn't exit then add
                    if( (count($guests) == 0) && (count($booking) == 0) ){
                        $guest = new Guest();
                        $guest->booking_number = $request->get($this->booking_num);
                        $guest->guest_email = $g;
                        $guest->save();
                    }  
                }  
            }catch(\Exception $ex){
                return response()->json(['Status' => 'fail',
                                 'Message' => 'bad request',
                                 'Value' => ""
                                ],400);
            }
        }else if($select == 'delete'){
            try{
                $get_guests = $request->get($this->guest_email);
                $userTable = User::where($this->one_email , '=', $get_guests)->first();
                if($userTable == null){
                    return response()->json(['Status' => 'fail', 
                                            'Message' => 'guest email is invalid',
                                            'Value' => ""], 400);
                }

                $guests = Guest::where($this->booking_num , '=', $request->get($this->booking_num))
                                ->where($this->guest_email , '=', $request->get($this->guest_email));
            
                if(count($guests->get()) == 0){
                    return response()->json(['Status' => 'fail',
                                    'Message' => 'do not found record',
                                    'Value' => ""
                                    ],400);
                }
                $guests->delete();
            }catch(\Exception $ex){
                return response()->json(['Status' => 'fail',
                                 'Message' => 'bad request',
                                 'Value' => ""
                                ],400);
            }
        }else{
            return response()->json(['Status' => 'fail',
                                'Message' => 'page not found',
                                'Value' => ""
                            ],404);
        }

        return response()->json(['Status' => 'success',
                                 'Message' => $select. ' successful',
                                 'Value' => $this->bookingTable($one_email = 'any', $select = 'booking', $input = $request->get($this->booking_num))->getData()->Value
                                ],200);
    }

    public function test(){
        $database = \Config::get('app.database');
        $timeNow = Carbon::now();
        $timeNow->tz = new \DateTimeZone('Asia/Bangkok');
        $data[] = [
            "name" => "version",
            "value" => '1.2.1',
        ];
        $data[] = [
            "name" => "last update",
            "value" => '05/03/2021',
        ];
        $data[] = [
            "name" => "host name",
            "value" => \Request::getHttpHost(),
        ];
        $data[] = [
            "name" => "http user agent",
            "value" => \Request::server('HTTP_USER_AGENT'),
        ];
        $data[] = [
            "name" => "db host",
            "value" => $database,
        ];
        $data[] = [
            "name" => "time when request",
            "value" => $timeNow->isoFormat('dddd D-MM-YYYY HH:mm:ss')
        ];
        return response()->json(['Status' => 'success',
                                 'Message' => "this is test api",
                                 'Value' => $data
                                ],200);
    }

    public function availableStat($day = null){
        $time_day_data = [];
        $time_room_data = [];
        $time_room_month_data = [];
        $timeNow = Carbon::now();
        $timeNow->tz = new \DateTimeZone('Asia/Bangkok');
        $green = new \DateTime('03:00');
        $red = new \DateTime('06:00');
        $ref = new \DateTime('00:00'); 
        $status_all = "red";
        $total_month_time = new \DateTime('00:00');
        
        $roomsTable = Room::where($this->main_door, '=', null)->get();
        foreach($roomsTable as $room){
            $tot_time_month[$room->room_num] = new \DateTime('00:00');
        }
        
        if(is_null($day)){
            $start = Carbon::create(Carbon::parse($timeNow)->year, Carbon::parse($timeNow)->month, 1, 0, 0, 0, 'Asia/Bangkok');
            $tmr = Carbon::create(Carbon::parse($timeNow)->year, Carbon::parse($timeNow)->month, 2, 0, 0, 0, 'Asia/Bangkok');
            $end = Carbon::create(Carbon::parse($timeNow)->year, Carbon::parse($timeNow)->month + 1, 1, 0, 0, 0, 'Asia/Bangkok');
        }else{
            if(!is_numeric($day)){
                return response()->json(['Status' => 'fail', 'Message' => "day should be number"], 400);
            }    
            $start = Carbon::create(Carbon::parse($timeNow)->year, Carbon::parse($timeNow)->month, $day, 0, 0, 0, 'Asia/Bangkok');
            $tmr = Carbon::create(Carbon::parse($timeNow)->year, Carbon::parse($timeNow)->month, $day + 1, 0, 0, 0, 'Asia/Bangkok');
            $end = Carbon::create(Carbon::parse($timeNow)->year, Carbon::parse($timeNow)->month, $day + 1, 0, 0, 0, 'Asia/Bangkok');
        }
        
        while($start < $end){
            $status_all = "red";
            foreach($roomsTable as $room){ 
                $total_day_time = new \DateTime('00:00');

                $bookingTable = Booking::where($this->meeting_time_start, '>=', $start)
                                        ->where($this->meeting_time_end, '<=', $tmr)
                                        ->where($this->eject , '=',  null)
                                        ->where($this->room_num , '=', $room->room_num)
                                        ->get();

                // dd($bookingTable);
                foreach($bookingTable as $booking){
                    $meeting_start = Carbon::createFromFormat('Y-m-d H:i:s',  $booking->meeting_start);
                    $meeting_end = Carbon::createFromFormat('Y-m-d H:i:s',  $booking->meeting_end);
                    $interval = $meeting_start->diff($meeting_end);
                    $total_day_time->add($interval);
                    $total_month_time->add($interval);
                    $tot_time_month[$room->room_num] = $tot_time_month[$room->room_num]->add($interval);
                }
                $total_compare_time = new \DateTime($ref->diff($total_day_time)->format("%H:%I"));

                //set status for each day
                if($total_compare_time <= $green){
                    $status = 'green';
                }else if(($total_compare_time > $green) && ($total_compare_time < $red)){
                    $status = 'orange';
                }else if($total_compare_time >= $red){
                    $status = 'red';
                }else{
                    $status = 'undefined';
                }
                
                //set status_all
                if($status_all == "red"){
                    if($total_compare_time <= $green){
                        $status_all = "green";
                    }else if(($total_compare_time > $green) && ($total_compare_time < $red)){
                        $status_all = "green";
                    }else if($total_compare_time >= $red){
                        $status_all = "red";
                    }else{
                        $status_all = "green";
                    }
                }
                
                $time_room_data[] = [
                    "room" => $room->room_num,
                    "time" => $ref->diff($total_day_time)->format("%H:%I"),
                    "status" => $status,
                ];

            }

            $time_day_data[] = [
                "day" => Carbon::parse($start)->day,
                "status_all" => $status_all,
                "booking_sum_time" => $time_room_data,
            ];

            $time_room_data = [];
            $tmr->adddays(1);
            $start->adddays(1);
        }
        foreach($tot_time_month as $key => $item){
            $days = (int)$ref->diff($item)->format("%D");
            $hours = (int)$ref->diff($item)->format("%H");
            $minutes = (int)$ref->diff($item)->format("%I");
            $hours = $hours + ($days * 24);
            $time_room_month_data[] = [
                "room" => $key,
                "time" => $hours. " Hours ". $minutes. " Minutes",
            ];
        }

        $days = (int)$ref->diff($total_month_time)->format("%D");
        $hours = (int)$ref->diff($total_month_time)->format("%H");
        $minutes = (int)$ref->diff($total_month_time)->format("%I");
        $hours = $hours + ($days * 24);
        
        if($day == null){
            return response()->json(['Status' => 'success',
                                 'Message' => "",
                                 'Value' => $time_day_data,
                                 "sum_time_month" =>  $time_room_month_data,
                                 "sum_time_month_all" => $hours. " Hours ". $minutes. " Minutes"], 200);
        }else{
            return response()->json(['Status' => 'success',
                                 'Message' => "",
                                 'Value' => $time_day_data], 200);
        }
    }

    public function nowMeetingTable($one_email = null){
        $now_meeting = [];
        $timeNow = Carbon::now();
        $timeNow->tz = new \DateTimeZone('Asia/Bangkok');

        if(!is_null($one_email)){
            $userTable = User::where($this->one_email , '=', $one_email)->first();
            if($userTable == null){
                return response()->json(['Status' => 'fail', 'Message' => 'email is invalid', 'Value' => ''], 400);
            }

            //get nowMeeting
            $nowMeeting = Booking::where($this->meeting_time_start, '<=', $timeNow)
                                    ->where($this->meeting_time_end, '>', $timeNow)
                                    ->Where($this->eject , '=',  null)
                                    ->get();
            
            //it's kinda dumb but is necessary cos one_email it's gonna be overwrite by 'any'
            $this_one_email = $one_email;
            $found = false;
            foreach($nowMeeting as $bt){
                //get booking from booking_number of nowMeeting
                $booking = $this->bookingTable($one_email = 'any', $select = 'booking', $input = $bt->booking_number)->getData()->Value;
                $guest_email =  $booking[0]->guest_email;
                $book_email = $booking[0]->one_email;

                //cheack booking email from booking
                if($this_one_email == $book_email){
                    $found = true;
                }

                //cheack guest email from booking
                foreach($guest_email as $ge){
                    if($this_one_email == $ge){
                        $found = true;
                    }
                }

                if($found == true){
                    $access = true;
                }else{
                    $access = false;
                }

                $found = false;

                $guests_arr = array();
                $guests = Guest::where($this->booking_num , '=', $bt->booking_number)->get();
                foreach($guests as $g){
                    array_push($guests_arr, $g->guest_email);
                }

                if(Carbon::parse($bt->meeting_start)->day < 10){
                    $day = "0" . Carbon::parse($bt->meeting_start)->day;
                }else{
                    $day = "" . Carbon::parse($bt->meeting_start)->day;
                }
                
                $now_meeting[] = [
                    "booking_number" => $bt->booking_number,
                    "one_email" => $bt->one_email,
                    "guest_email" => $guests_arr,
                    "room_num" => $bt->room_num,
                    "agenda" => $bt->agenda,
                    "meeting_start" => $bt->meeting_start,
                    "meeting_start_day" => $day,
                    "meeting_end" => $bt->meeting_end,
                    // "created_at" => $booking->created_at,
                    "eject_at" => $bt->eject_at,
                    "access" => $access
                ];
            }
        }else{
            return response()->json([ 'Status' => 'fail', 'Message' => 'email might be null', 'Value' => ''], 400);
        }
        
        if(count($now_meeting) == 0){
            return response()->json([ 'Status' => 'success', 'Message' => 'no meeting right now', 'Value' => $now_meeting], 200);
        }else{
            return response()->json([ 'Status' => 'success', 'Message' => '', 'Value' => $now_meeting], 200);
        }
    }

    public function bookingTable($one_email = null, $select = null, $input = null){   
        $booking_data_this_month = [];
        $booking_data_next_month = [];
        $booking_data_json = [];
        $timeNow = Carbon::now();
        $timeNow->tz = new \DateTimeZone('Asia/Bangkok');

        if(is_null($one_email)){
            $this_month_start = Carbon::create($timeNow->year, $timeNow->month, 1, 0, 0, 0, 'Asia/Bangkok');
            $this_month_end = Carbon::create($timeNow->year, $timeNow->month, 1, 0, 0, 0, 'Asia/Bangkok');
            $this_month_end->addMonthsNoOverflow(1);
            $next_month_start = Carbon::create($timeNow->year, $timeNow->month, 1, 0, 0, 0, 'Asia/Bangkok');
            $next_month_start->addMonthsNoOverflow(1);
            $next_month_end = Carbon::create($timeNow->year, $timeNow->month, 1, 0, 0, 0, 'Asia/Bangkok');
            $next_month_end->addMonthsNoOverflow(2);

            //this month
            $bookingTable = Booking::join('users', 'bookings.one_email', '=', 'users.one_email')
                                    ->where($this->meeting_time_end, '>', $this_month_start)
                                    ->where($this->meeting_time_end, '<', $this_month_end)
                                    ->where($this->eject , '=',  null)
                                    ->get();
            foreach($bookingTable as $booking){
                $guests_mail_arr = array();
                $guests_name_arr = array();
                $guests = Guest::join('users', 'guests.guest_email', '=', 'users.one_email')
                                ->where($this->booking_num , '=', $booking->booking_number)->get();
                foreach($guests as $g){
                    array_push($guests_mail_arr, $g->guest_email);
                    array_push($guests_name_arr, $g->name);
                }

                if(Carbon::parse($booking->meeting_start)->day < 10){
                    $day = "0" . Carbon::parse($booking->meeting_start)->day;
                }else{
                    $day = "" . Carbon::parse($booking->meeting_start)->day;
                }

                $booking_data_this_month[] = [
                    "booking_number" => $booking->booking_number,
                    "name" => $booking->name,
                    "one_email" => $booking->one_email,
                    "guest_name" => $guests_name_arr,
                    "guest_email" => $guests_mail_arr,
                    "room_num" => $booking->room_num,
                    "agenda" => $booking->agenda,
                    "meeting_start" => $booking->meeting_start,
                    "meeting_start_day" => $day,
                    "meeting_end" => $booking->meeting_end,
                    // "created_at" => $booking->created_at,
                    "eject_at" => $booking->eject_at
                ];
            }

            //next month
            $bookingTable = Booking::join('users', 'bookings.one_email', '=', 'users.one_email')
                                    ->where($this->meeting_time_end, '>', $next_month_start)
                                    ->where($this->meeting_time_end, '<', $next_month_end)
                                    ->where($this->eject , '=',  null)
                                    ->get();
            foreach($bookingTable as $booking){
                $guests_mail_arr = array();
                $guests_name_arr = array();
                $guests = Guest::join('users', 'guests.guest_email', '=', 'users.one_email')
                                ->where($this->booking_num , '=', $booking->booking_number)->get();
                foreach($guests as $g){
                    array_push($guests_mail_arr, $g->guest_email);
                    array_push($guests_name_arr, $g->name);
                }

                if(Carbon::parse($booking->meeting_start)->day < 10){
                    $day = "0" . Carbon::parse($booking->meeting_start)->day;
                }else{
                    $day = "" . Carbon::parse($booking->meeting_start)->day;
                }

                $booking_data_next_month[] = [
                    "booking_number" => $booking->booking_number,
                    "name" => $booking->name,
                    "one_email" => $booking->one_email,
                    "guest_name" => $guests_name_arr,
                    "guest_email" => $guests_mail_arr,
                    "room_num" => $booking->room_num,
                    "agenda" => $booking->agenda,
                    "meeting_start" => $booking->meeting_start,
                    "meeting_start_day" => $day,
                    "meeting_end" => $booking->meeting_end,
                    // "created_at" => $booking->created_at,
                    "eject_at" => $booking->eject_at
                ];
            }

            $value = [
                'this_month' => $booking_data_this_month,
                'next_month' => $booking_data_next_month
            ];

            return response()->json(['Status' => 'success', 'Message' => '', "Value" => $value], 200);
        }else{
            if($select == 'history'){
                $booking_data_history = [];
                $bookingTable_notEject = Booking::join('users', 'bookings.one_email', '=', 'users.one_email')
                                        ->where('bookings.'.$this->one_email , '=', $one_email)
                                        ->where($this->meeting_time_end, '<', $timeNow)
                                        ->Where($this->eject , '=',  null)
                                        ->get();
                
                $bookingTable_Eject = Booking::join('users', 'bookings.one_email', '=', 'users.one_email')
                                        ->where('bookings.'.$this->one_email , '=', $one_email)
                                        ->Where($this->eject , '!=',  null)
                                        ->get();

                foreach($bookingTable_notEject as $booking){
                    $guests_mail_arr = array();
                    $guests_name_arr = array();
                    $guests = Guest::join('users', 'guests.guest_email', '=', 'users.one_email')
                                    ->where($this->booking_num , '=', $booking->booking_number)->get();
                    foreach($guests as $g){
                        array_push($guests_mail_arr, $g->guest_email);
                        array_push($guests_name_arr, $g->name);
                    }

                    if(Carbon::parse($booking->meeting_start)->day < 10){
                        $day = "0" . Carbon::parse($booking->meeting_start)->day;
                    }else{
                        $day = "" . Carbon::parse($booking->meeting_start)->day;
                    }
    
                    $booking_data_history[] = [
                        "booking_number" => $booking->booking_number,
                        "name" => $booking->name,
                        "one_email" => $booking->one_email,
                        "guest_name" => $guests_name_arr,
                        "guest_email" => $guests_mail_arr,
                        "room_num" => $booking->room_num,
                        "agenda" => $booking->agenda,
                        "meeting_start" => $booking->meeting_start,
                        "meeting_start_day" => $day,
                        "meeting_end" => $booking->meeting_end,
                        // "created_at" => $booking->created_at,
                        "eject_at" => $booking->eject_at,
                        "guest" => false
                    ];
                }
                
                foreach($bookingTable_Eject as $booking){
                    $guests_mail_arr = array();
                    $guests_name_arr = array();
                    $guests = Guest::join('users', 'guests.guest_email', '=', 'users.one_email')
                                    ->where($this->booking_num , '=', $booking->booking_number)->get();
                    foreach($guests as $g){
                        array_push($guests_mail_arr, $g->guest_email);
                        array_push($guests_name_arr, $g->name);
                    }

                    if(Carbon::parse($booking->meeting_start)->day < 10){
                        $day = "0" . Carbon::parse($booking->meeting_start)->day;
                    }else{
                        $day = "" . Carbon::parse($booking->meeting_start)->day;
                    }
                    
                    $booking_data_history[] = [
                        "booking_number" => $booking->booking_number,
                        "name" => $booking->name,
                        "one_email" => $booking->one_email,
                        "guest_name" => $guests_name_arr,
                        "guest_email" => $guests_mail_arr,
                        "room_num" => $booking->room_num,
                        "agenda" => $booking->agenda,
                        "meeting_start" => $booking->meeting_start,
                        "meeting_start_day" => $day,
                        "meeting_end" => $booking->meeting_end,
                        // "created_at" => $booking->created_at,
                        "eject_at" => $booking->eject_at,
                        "guest" => false
                    ];
                }

                $booking_guestTable_notEject = Booking::join('guests', 'bookings.booking_number', '=', 'guests.booking_number')
                        ->join('users', 'bookings.one_email', '=', 'users.one_email')
                        ->where($this->guest_email , '=', $one_email)
                        ->where($this->meeting_time_end, '<', $timeNow)
                        ->where($this->eject , '=',  null)
                        ->get();

                $booking_guestTable_Eject = Booking::join('guests', 'bookings.booking_number', '=', 'guests.booking_number')
                        ->join('users', 'bookings.one_email', '=', 'users.one_email')
                        ->where($this->guest_email , '=', $one_email)
                        ->where($this->eject , '!=',  null)
                        ->get();

                foreach($booking_guestTable_notEject as $bgt){
                    $guests_email_arr = array();
                    $guests_name_arr = array();
                    $guests = Guest::join('users', 'users.one_email', '=', 'guests.guest_email')
                                    ->where($this->booking_num , '=', $bgt->booking_number)->get();
                    foreach($guests as $g){
                        array_push($guests_email_arr, $g->guest_email);
                        array_push($guests_name_arr, $g->name);
                    }

                    if(Carbon::parse($bgt->meeting_start)->day < 10){
                        $day = "0" . Carbon::parse($bgt->meeting_start)->day;
                    }else{
                        $day = "" . Carbon::parse($bgt->meeting_start)->day;
                    }

                    $booking_data_history[] = [
                        "booking_number" => $bgt->booking_number,
                        "name" => $bgt->name,
                        "one_email" => $bgt->one_email,
                        "guest_name" => $guests_name_arr,
                        "guest_email" => $guests_email_arr,
                        "room_num" => $bgt->room_num,
                        "agenda" => $bgt->agenda,
                        "meeting_start" => $bgt->meeting_start,
                        "meeting_start_day" => $day,
                        "meeting_end" => $bgt->meeting_end,
                        // "created_at" => $guest->created_at,
                        "eject_at" => $bgt->eject_at,
                        "guest" => true
                    ];
                }

                foreach($booking_guestTable_Eject as $bgt){
                    $guests_email_arr = array();
                    $guests_name_arr = array();
                    $guests = Guest::join('users', 'users.one_email', '=', 'guests.guest_email')
                                    ->where($this->booking_num , '=', $bgt->booking_number)->get();
                    foreach($guests as $g){
                        array_push($guests_email_arr, $g->guest_email);
                        array_push($guests_name_arr, $g->name);
                    }

                    if(Carbon::parse($bgt->meeting_start)->day < 10){
                        $day = "0" . Carbon::parse($bgt->meeting_start)->day;
                    }else{
                        $day = "" . Carbon::parse($bgt->meeting_start)->day;
                    }

                    $booking_data_history[] = [
                        "booking_number" => $bgt->booking_number,
                        "name" => $bgt->name,
                        "one_email" => $bgt->one_email,
                        "guest_name" => $guests_name_arr,
                        "guest_email" => $guests_email_arr,
                        "room_num" => $bgt->room_num,
                        "agenda" => $bgt->agenda,
                        "meeting_start" => $bgt->meeting_start,
                        "meeting_start_day" => $day,
                        "meeting_end" => $bgt->meeting_end,
                        // "created_at" => $guest->created_at,
                        "eject_at" => $bgt->eject_at,
                        "guest" => true
                    ];
                }
                
                //sort
                $booking_data_sort_arr = array();
                $booking_data_sort = collect($booking_data_history)->sortBy("booking_number");
                foreach($booking_data_sort as $booking_data){
                    array_push($booking_data_sort_arr, $booking_data);
                }
                return response()->json(['Status' => 'success', 'Message' => '', "Value" => $booking_data_sort_arr], 200);
            }else if($select == 'future'){
                //guest for ant design table
                $booking_data_future = [];
                $bookingTable = Booking::join('users', 'bookings.one_email', '=', 'users.one_email')
                                    ->where('bookings.' .$this->one_email , '=', $one_email)
                                    ->where($this->meeting_time_end, '>', $timeNow)
                                    ->where($this->eject , '=',  null)
                                    ->get();
                                    
                foreach($bookingTable as $booking){
                    $guest_for_antd_table = [];
                    $guests_mail_arr = array();
                    $guests_name_arr = array();
                    $guests = Guest::join('users', 'guests.guest_email', '=', 'users.one_email')
                                    ->where($this->booking_num , '=', $booking->booking_number)->get();
                    foreach($guests as $g){
                        $guest_for_antd_table[] = [
                            "email" => $g->guest_email,
                            "name" => $g->name
                        ];
                        array_push($guests_mail_arr, $g->guest_email);
                        array_push($guests_name_arr, $g->name);
                    }

                    if(Carbon::parse($booking->meeting_start)->day < 10){
                        $day = "0" . Carbon::parse($booking->meeting_start)->day;
                    }else{
                        $day = "" . Carbon::parse($booking->meeting_start)->day;
                    }
    
                    $booking_data_future[] = [
                        "booking_number" => $booking->booking_number,
                        "name" => $booking->name,
                        "one_email" => $booking->one_email,
                        "guest_name" => $guests_name_arr,
                        "guest_email" => $guests_mail_arr,
                        "guest_table" => $guest_for_antd_table,
                        "room_num" => $booking->room_num,
                        "agenda" => $booking->agenda,
                        "meeting_start" => $booking->meeting_start,
                        "meeting_start_day" => $day,
                        "meeting_end" => $booking->meeting_end,
                        // "created_at" => $booking->created_at,
                        "eject_at" => $booking->eject_at,
                        "guest" => false
                    ];
                }
                
                $guestTable = Booking::join('guests', 'bookings.booking_number', '=', 'guests.booking_number')
                        ->join('users', 'bookings.one_email', '=', 'users.one_email')
                        ->where($this->guest_email , '=', $one_email)
                        ->where($this->meeting_time_end, '>', $timeNow)
                        ->where($this->eject , '=',  null)
                        ->get();

                foreach($guestTable as $gt){
                    $guest_for_antd_table = [];
                    $guests_mail_arr = array();
                    $guests_name_arr = array();
                    $guests = Guest::join('users', 'guests.guest_email', '=', 'users.one_email')
                                    ->where($this->booking_num , '=', $gt->booking_number)->get();
                    foreach($guests as $g){
                        $guest_for_antd_table[] = [
                            "email" => $g->guest_email,
                            "name" => $g->name
                        ];
                        array_push($guests_mail_arr, $g->guest_email);
                        array_push($guests_name_arr, $g->name);
                    }

                    if(Carbon::parse($gt->meeting_start)->day < 10){
                        $day = "0" . Carbon::parse($gt->meeting_start)->day;
                    }else{
                        $day = "" . Carbon::parse($gt->meeting_start)->day;
                    }

                    $booking_data_future[] = [
                        "booking_number" => $gt->booking_number,
                        "name" => $gt->name,
                        "one_email" => $gt->one_email,
                        "guest_name" => $guests_name_arr,
                        "guest_email" => $guests_mail_arr,
                        "guest_table" => $guest_for_antd_table,
                        "room_num" => $gt->room_num,
                        "agenda" => $gt->agenda,
                        "meeting_start" => $gt->meeting_start,
                        "meeting_start_day" => $day,
                        "meeting_end" => $gt->meeting_end,
                        // "created_at" => $guest->created_at,
                        "eject_at" => $gt->eject_at,
                        "guest" => true
                    ];
                }

                //sort
                $booking_data_sort_arr = array();
                $booking_data_sort = collect($booking_data_future)->sortBy("booking_number");
                foreach($booking_data_sort as $booking_data){
                    array_push($booking_data_sort_arr, $booking_data);
                }

                return response()->json(['Status' => 'success', 'Message' => '', "Value" => $booking_data_sort_arr], 200);
            }else if($select == 'time'){
                $booking_data_time = [];
                $start_date = $input . " 00:00:00";
                $end_date = $input. " 23:59:59";
                $bookingTable = Booking::join('users', 'bookings.one_email', '=', 'users.one_email')
                    ->where('bookings.'.$this->one_email , '=', $one_email)
                    ->where($this->meeting_time_start, '>=', $start_date)
                    ->where($this->meeting_time_start, '<=', $end_date)
                    ->where($this->eject , '=',  null)
                    ->get();

                foreach($bookingTable as $booking){
                    $guests_mail_arr = array();
                    $guests_name_arr = array();
                    $guests = Guest::join('users', 'guests.guest_email', '=', 'users.one_email')
                                    ->where($this->booking_num , '=', $booking->booking_number)->get();
                    foreach($guests as $g){
                        array_push($guests_mail_arr, $g->guest_email);
                        array_push($guests_name_arr, $g->name);
                    }

                    if(Carbon::parse($booking->meeting_start)->day < 10){
                        $day = "0" . Carbon::parse($booking->meeting_start)->day;
                    }else{
                        $day = "" . Carbon::parse($booking->meeting_start)->day;
                    }
    
                    $booking_data_time[] = [
                        "booking_number" => $booking->booking_number,
                        "name" => $booking->name,
                        "one_email" => $booking->one_email,
                        "guest_name" => $guests_name_arr,
                        "guest_email" => $guests_mail_arr,
                        "room_num" => $booking->room_num,
                        "agenda" => $booking->agenda,
                        "meeting_start" => $booking->meeting_start,
                        "meeting_start_day" => $day,
                        "meeting_end" => $booking->meeting_end,
                        // "created_at" => $booking->created_at,
                        "eject_at" => $booking->eject_at,
                        "guest" => false
                    ];
                }
                
                $guestTable = Booking::join('guests', 'bookings.booking_number', '=', 'guests.booking_number')
                        ->join('users', 'bookings.one_email', '=', 'users.one_email')
                        ->where($this->guest_email , '=', $one_email)
                        ->where($this->meeting_time_start, '>=', $start_date)
                        ->where($this->meeting_time_start, '<=', $end_date)
                        ->where($this->eject , '=',  null)
                        ->get();

                foreach($guestTable as $gt){
                    $guests_mail_arr = array();
                    $guests_name_arr = array();
                    $guests = Guest::join('users', 'guests.guest_email', '=', 'users.one_email')
                                    ->where($this->booking_num , '=', $gt->booking_number)->get();
                    foreach($guests as $g){
                        array_push($guests_mail_arr, $g->guest_email);
                        array_push($guests_name_arr, $g->name);
                    }

                    if(Carbon::parse($gt->meeting_start)->day < 10){
                        $day = "0" . Carbon::parse($gt->meeting_start)->day;
                    }else{
                        $day = "" . Carbon::parse($gt->meeting_start)->day;
                    }

                    $booking_data_time[] = [
                        "booking_number" => $gt->booking_number,
                        "name" => $gt->name,
                        "one_email" => $gt->one_email,
                        "guest_name" => $guests_name_arr,
                        "guest_email" => $guests_mail_arr,
                        "room_num" => $gt->room_num,
                        "agenda" => $gt->agenda,
                        "meeting_start" => $gt->meeting_start,
                        "meeting_start_day" => $day,
                        "meeting_end" => $gt->meeting_end,
                        // "created_at" => $guest->created_at,
                        "eject_at" => $gt->eject_at,
                        "guest" => true
                    ];
                }

                //sort
                $booking_data_sort_arr = array();
                $booking_data_sort = collect($booking_data_time)->sortBy("booking_number");
                foreach($booking_data_sort as $booking_data){
                    array_push($booking_data_sort_arr, $booking_data);
                }
                
                return response()->json(['Status' => 'success', 'Message' => '', "Value" => $booking_data_sort_arr], 200);
            }else if($select == 'booking' && $one_email == 'any'){
                $booking_data = [];
                $guest_for_antd_table = [];
                $guests_mail_arr = array();
                $guests_name_arr = array();
                $booking = Booking::join('users', 'bookings.one_email', '=', 'users.one_email')
                                    ->where($this->booking_num , '=', $input)
                                    ->first();
                
                if($booking == null){
                    return response()->json(['Status' => 'success', 'Message' => '', "Value" => $booking_data], 200);
                }

                $guests = Guest::join('users', 'guests.guest_email', '=', 'users.one_email')
                                ->where($this->booking_num , '=', $input)
                                ->get();
                foreach($guests as $g){
                    $guest_for_antd_table[] = [
                        "email" => $g->guest_email,
                        "name" => $g->name
                    ];
                    array_push($guests_mail_arr, $g->guest_email);
                    array_push($guests_name_arr, $g->name);
                }
                
                if(Carbon::parse($booking->meeting_start)->day < 10){
                    $day = "0" . Carbon::parse($booking->meeting_start)->day;
                }else{
                    $day = "" . Carbon::parse($booking->meeting_start)->day;
                }

                $booking_data[] = [
                    "booking_number" => $booking->booking_number,
                    "name" => $booking->name,
                    "one_email" => $booking->one_email,
                    "guest_name" => $guests_name_arr,
                    "guest_email" => $guests_mail_arr,
                    "guest_table" => $guest_for_antd_table,
                    "room_num" => $booking->room_num,
                    "agenda" => $booking->agenda,
                    "meeting_start" => $booking->meeting_start,
                    "meeting_start_day" => $day,
                    "meeting_end" => $booking->meeting_end,
                    // "created_at" => $booking->created_at,
                    "eject_at" => $booking->eject_at,
                ];
                return response()->json(['Status' => 'success', 'Message' => '', "Value" => $booking_data], 200);
            }else{
                return response()->json(['Status' => 'fail', 'Message' => 'page not found'], 404);
            }
        }    
    }
    
    public function userTable($select = null, $one_email = null){

        if($select == 'all'){
            if(is_null($one_email)){
                $userTable = User::all();
                return response()->json(['Status' => 'success','Message' => '' ,'Value' => $userTable], 200);
            }else{
                $userTable = User::where($this->one_email , '=', $one_email)->first();
                return response()->json(['Status' => 'success', 'Message' => '' , 'Value' => $userTable], 200);
            }
        }else if($select == 'email'){
            $user_email_arr = array();
            if(is_null($one_email)){
                $userTable = User::all();
                foreach($userTable as $user){
                    array_push($user_email_arr, $user->one_email);
                }
                return response()->json(['Status' => 'success', 'Message' => '', 'Value' => $user_email_arr], 200);
            }else{
                return response()->json(['Status' => 'fail', 'Message' => 'page not found'], 404);
            }
        }else{
            return response()->json(['Status' => 'fail', 'Message' => 'page not found'], 404);
        }
        
    }

    public function roomTable(){
        $roon_table = Room::all();
        return response()->json(['Status' => 'Query Successful', 'Value' => $roon_table], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function checkAvailableRoom(Request $request){
        $this->meeting_start_get = $request->get($this->meeting_time_start);
        $this->meeting_end_get = $request->get($this->meeting_time_end);
        $rooms_booked_arr = array();
        $rooms_not_booked_arr = array();
        $timeNow = Carbon::now();
        $timeNow->tz = new \DateTimeZone('Asia/Bangkok');

        $meeting_start_date = Carbon::parse($request->get($this->meeting_time_start));
        $meeting_end_date = Carbon::parse($request->get($this->meeting_time_end));

        if( is_null($request->get($this->meeting_time_start)) ||
            is_null($request->get($this->meeting_time_end))
        ){
            return response()->json(['Status' => 'fail', 'Message' => 'some value might be null'], 400);
        }

        if(($request->get($this->meeting_time_start) < $timeNow) || 
           ($meeting_end_date->day != $meeting_start_date->day)){
            return response()->json(['Status' => 'fail', 'Message' => 'datetime is invalid'], 400);
        }

        if($request->get($this->meeting_time_start) >= $request->get($this->meeting_time_end)){
            return response()->json(['Status' => 'fail', 'Message' => 'start time sould be less then end time'], 400);
        }
        
        // $booking = new Booking();

        $rooms = Room::where($this->main_door , '=', null)->get();

        $booked_list = Booking::where($this->eject , '=',  null)
                                ->where($this->meeting_time_end , '>', $timeNow)
                                ->where($this->meeting_time_end , '>',  $this->meeting_start_get)
                                ->where($this->meeting_time_start , '<',  $this->meeting_end_get)
                                ->get();
                            
        foreach($booked_list as $booked){
            array_push($rooms_booked_arr, $booked->room_num);
            $rooms_booked_arr = array_unique($rooms_booked_arr);
        }

        $found = false;
        foreach($rooms as $room){
            foreach($rooms_booked_arr as $room_booked){
                if($room->room_num == $room_booked){
                    $found = true;
                }
            }
            //if not found room in rooms_booked_arr
            if(!$found){
                array_push($rooms_not_booked_arr, $room->room_num);
            }
            $found = false;
        }

        // foreach($rooms_booked_arr as $room_booked_arr){
        //     //if query found somthing then room not available
        //     $checkBookingTime = Booking::where($this->room_num , $room_booked_arr)
        //                                 ->where($this->meeting_time_end , '>', $timeNow)
        //                                 ->where($this->meeting_time_end , '>',  $this->meeting_start_get)
        //                                 ->where($this->meeting_time_start , '<',  $this->meeting_end_get)
        //                                 ->where($this->eject , '=',  null)
        //                                 // ->orderBy($this->room_num , 'DESC')
        //                                 ->get();
        //     if(count($checkBookingTime) == 0){
        //         array_push($rooms_not_booked_arr, $room_booked_arr);
        //     }                           
        // }

        if(count($rooms_not_booked_arr) == 0){
            $rooms_not_booked_jason = [
                'Status' => 'success',
                'Message' => 'no room available',
                'Value' => $rooms_not_booked_arr
            ];
            return response()->json($rooms_not_booked_jason, 200);
        }else{
            $rooms_not_booked_jason = [
                'Status' => 'success',
                'Message' => 'these rooms are available',
                'Value' => $rooms_not_booked_arr
            ];
            return response()->json($rooms_not_booked_jason, 200);
        }    
    }

    public function booking(Request $request){

        if( is_null($request->get($this->one_email)) || 
            is_null($request->get($this->agenda)) ||
            is_null($request->get($this->room_num)) ||
            is_null($request->get($this->meeting_time_start)) ||
            is_null($request->get($this->meeting_time_end))
            //  || is_null($request->get("guests"))
        ){
            return response()->json(['Status' => 'fail', 'Message' => 'some value might be null'], 400);
        }

        $rooms = Room::where($this->room_num , '=', $request->get($this->room_num))
                    ->where($this->main_door , '=', null)
                    ->first();
        if($rooms == null){
            return response()->json(['Status' => 'fail', 'Message' => 'meeting room number is invalid'], 400);
        }
        $userTable = User::where($this->one_email , '=', $request->get($this->one_email))->first();
        if($userTable == null){
            return response()->json(['Status' => 'fail', 'Message' => 'booking email is invalid'], 400);
        }

        if($this->checkAvailableRoom($request)->getStatusCode() == 200) {
            $rooms_available_arr = $this->checkAvailableRoom($request)->getData()->Value;
            $user_room_select_stat = false;
        }else{
            return response()->json(['Status' => 'fail', 'Message' => $this->checkAvailableRoom($request)->getData()->Message], 400);
        }

        //if guest not null
        if(!is_null($request->get("guests"))){
            $guests = $request->get("guests");
            foreach($guests as $g){
                if($g == $request->get($this->one_email)){
                    return response()->json(['Status' => 'fail', 'Message' => 'booking email cannot be guest'], 400);
                }
                $userTable = User::where($this->one_email , '=', $g)->first();
                if($userTable == null){
                    return response()->json(['Status' => 'fail', 'Message' => 'guest email is invalid'], 400);
                }
            }
        }else{
            $guests = [];
        }

        foreach($rooms_available_arr as $room_available){
            if($room_available == $request->get($this->room_num)){
                $user_room_select_stat = true;
            }
        }

        if($user_room_select_stat){
            $booking = new Booking();
            $booking->one_email = $request->get($this->one_email);
            $booking->room_num = $request->get($this->room_num);
            $booking->meeting_start = $request->get($this->meeting_time_start);
            $booking->meeting_end = $request->get($this->meeting_time_end);
            $booking->agenda = $request->get($this->agenda);
            $booking->save();

            $bookingTable = Booking::where($this->one_email , '=', $request->get($this->one_email))
                                    ->where($this->room_num , '=', $request->get($this->room_num))
                                    ->where($this->meeting_time_start , '=', $request->get($this->meeting_time_start))
                                    ->where($this->meeting_time_end , '=', $request->get($this->meeting_time_end))
                                    ->where($this->eject , '=',  null)
                                    ->first();

            //if guest not null
            if(!is_null($request->get("guests"))){
                foreach($guests as $g){
                    $guest = new Guest();
                    $guest->booking_number = $bookingTable->booking_number;
                    $guest->guest_email = $g;
                    $guest->save();
                }
            }
            
            return response()->json(['Status' => 'success', 'Message' => '', 'Booking Info' => $bookingTable, 'Guests' => $guests], 201);
        }else{
            return response()->json(['Status' => 'fail', 'Message' => 'room not available'], 400);
        }
    }

    public function ejectBooking($one_email = null, $booking_number = null){
        
        if(is_null($booking_number) || is_null($one_email)){
            return response()->json(['Status' => 'fail', 'Message' => 'some value might be null'], 400);
        }else{
            $userTable = User::where($this->one_email , '=', $one_email)->first();
            if($userTable == null){
                return response()->json(['Status' => 'fail', 'Message' => 'email is invalid'], 400);
            }

            $bookingTable = Booking::where($this->booking_num , '=', $booking_number)
                                    ->where($this->one_email , '=', $one_email)    
                                    ->first();

            if(is_null($bookingTable)){
                return response()->json(['Status' => 'fail', 'Message' => 'booking number is invalid'], 400);
            }

            if(!is_null($bookingTable->eject_at)){
                return response()->json(['Status' => 'fail','Message' => 'this booking number already eject', 'Eject at' => $bookingTable->eject_at], 400);
            }

            $timeNow = Carbon::now();
            $timeNow->tz = new \DateTimeZone('Asia/Bangkok');
            // $timeNow = Carbon::createFromFormat('Y-m-d H:i:s', $timeNow, 'Asia/Bangkok');

            $bookingTable = Booking::where($this->booking_num , '=', $booking_number)
                                    ->update([$this->eject => $timeNow]);

            $bookingTable = Booking::where($this->booking_num , '=', $booking_number)->first();
            return response()->json(['Status' => 'success', 'Message' => '', 'Eject at' =>  $bookingTable->eject_at], 200);
        }

    }

    public function getProfile($user_token = null){
        // $bot_token = 'Bearer '. env("GET_PROFILE_BOT_TOKEN");
        // $bot_id = env("GET_PROFILE_BOT_ID");
        // $api_path = env("GET_PROFILE_API");
        $bot_token = 'Bearer Af58c5450f3b45c71a97bc51c05373ecefabc49bd2cd94f3c88d5b844813e69a17e26a828c2b64ef889ef0c10e2aee347';
        $bot_id = 'B75900943c6205ce084d1c5e8850d40f9';
        $api_path = 'https://chat-api.one.th/manage/api/v1/getprofile';
        $client = new \GuzzleHttp\Client();

        try{
            $request = $client->post($api_path,[
                'headers' => [
                    'Authorization' => $bot_token,
                    'Content-Type' => 'application/json',
                ],
    
                'body' => json_encode([
                    'bot_id'=> $bot_id,
                    'source'=> $user_token,
                ])
            ]);
            $response = json_decode($request->getBody()->getContents());
        }catch(\GuzzleHttp\Exception\ConnectException $ex){
            return response()->json([ 'Status' => 'fail',
                                        'Message' => 'cannot connect to server'
                                    ], 500);
        }catch (\Exception $ex) {
            // return response()->json(['Status' => $ex->getResponse()->getReasonPhrase()], $ex->getResponse()->getStatusCode());
            if(($ex->getResponse()->getStatusCode() == 401)){
                $m = 'token invalid';
            }else if(($ex->getResponse()->getStatusCode() == 400)){
                $m = 'bad request';
            }else if(($ex->getResponse()->getStatusCode() == 404)){
                $m = 'not found';
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
            //cheack user role
            $userTable = User::where($this->one_email , '=', $response->data->email)
                                ->where($this->role , '=', 'admin')
                                ->first();
            if(is_null($userTable)){
                $role = 'user';
            }else{
                $role = 'admin';
            }

            foreach($response->data as $key => $item){
                $data[$key] =  $item;
            }

            $data['role'] = $role;
            return response()->json(['Status' => 'success', 'Message' => '', 'Value' => $data ], 200);
        }else{
            return response()->json(['Status' => 'fail', 'Message' => 'token invalid'], 401);
        }
    }

    public function unlock(Request $request, $user_token = null){
        // $server = env("UNLOCK_API");
        $server = 'http://203.151.164.229:5003';
        $client = new \GuzzleHttp\Client();

        if(is_null($request->get($this->room_num)) || is_null($request->get($this->one_email))){
            return response()->json([ 'Status' => 'fail',
                                      'Message' => 'some value might be null'
                                    ], 400);
        }

        $userTable = User::where($this->one_email , '=', $request->get($this->one_email))->first();
        if($userTable == null){
            return response()->json(['Status' => 'fail', 'Message' => 'booking email is invalid'], 404);
        }
        
        $rooms = Room::where($this->room_num, '=', $request->get($this->room_num))->first();
        if(is_null($rooms)){
            return response()->json([ 'Status' => 'fail',
                                      'Message' => 'room number is invalid'
                                    ], 404);
        }

        //cheack token
        if($this->getProfile($user_token = $user_token)->getStatusCode() == 401){
            return response()->json([ 'Status' => 'fail',
                                      'Message' => 'tokan invalid'
                                    ], 401);
        }

        //check main door
        if($rooms->main_door == 1){
            $guest_req = 'none';
            try {
                $request = $client->post($server. '/api/v1/unlock/'. $request->get($this->room_num),[
                    'headers' => [
                        'Authorization' => $user_token,
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode([
                        'guest_req'=> $guest_req,
                    ])
                ]);
                $response = json_decode($request->getBody()->getContents());
            } catch (\Exception $ex) {
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

            if($response->result[0]->door_action == 'open'){
                return response()->json([ 'Status' => 'success', 
                                          'Message' => 'main door accessed',
                                          'Value' => $response
                                        ], 200);
            }else{
                return response()->json([ 'Status' => 'success', 
                                          'Message' => 'cannot access main door',
                                          'Value' => $response
                                        ], 200);
            } 
        }

        $userTable = User::where($this->one_email , '=', $request->get($this->one_email))
                            ->where($this->role , '=', 'admin')
                            ->first();

        //if this user is admin
        if(!is_null($userTable)){
            $guest_req = 'admin';
        }else{
            $timeNow = Carbon::now();
            $timeNow->tz = new \DateTimeZone('Asia/Bangkok');

            $booking_table = Booking::join('rooms', 'bookings.room_num', '=', 'rooms.room_num')
                                    ->where($this->one_email , '=', $request->get($this->one_email))
                                    ->where('rooms.'.$this->room_num , '=', $request->get($this->room_num))
                                    ->Where($this->eject , '=',  null)
                                    ->where($this->meeting_time_start , '<=', $timeNow)
                                    ->where($this->meeting_time_end , '>', $timeNow)
                                    ->get();

            $guest_table = Guest::join('bookings', 'bookings.booking_number', '=', 'guests.booking_number')
                                    ->join('rooms', 'bookings.room_num', '=', 'rooms.room_num')
                                    ->where($this->guest_email , '=', $request->get($this->one_email))
                                    ->where('rooms.'.$this->room_num , '=', $request->get($this->room_num))
                                    ->Where($this->eject , '=',  null)
                                    ->where($this->meeting_time_start , '<=', $timeNow)
                                    ->where($this->meeting_time_end , '>', $timeNow)
                                    ->get();

            //cheack email is guest or not
            if(count($booking_table) != 0 && count($guest_table) == 0){
                $guest_req = 'no';
            }else if(count($booking_table) == 0 && count($guest_table) != 0){
                $guest_req = 'yes';
            }else if(count($booking_table) == 0 && count($guest_table) == 0){
                return response()->json([ 'Status' => 'success', 
                                      'Message' => 'cannot access meeting room',
                                    ], 200);
            }
        }

        try {
            $request = $client->post($server. '/api/v1/unlock/'. $request->get($this->room_num),[
                'headers' => [
                    'Authorization' => $user_token,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'guest_req'=> $guest_req,
                ])
            ]);
            $response = json_decode($request->getBody()->getContents());
        }catch(\GuzzleHttp\Exception\ConnectException $ex){
            return response()->json([ 'Status' => 'fail',
                                        'Message' => 'cannot connect to server'
                                    ], 500);
        }catch (\Exception $ex) {
            if(($ex->getResponse()->getStatusCode() == 401)){
                $m = 'token invalid';
            }else if(($ex->getResponse()->getStatusCode() == 400)){
                $m = 'bad request';
            }else if(($ex->getResponse()->getStatusCode() == 500)){
                $m = 'server error';
            }else if(($ex->getResponse()->getStatusCode() == 404)){
                $m = 'not found';
            }else{
                $m = '';
            }
            return response()->json([ 'Status' => 'fail',
                                        'Message' => $m
                                    ], $ex->getResponse()->getStatusCode());
        }
        return response()->json([ 'Status' => 'success', 
                                        'Message' => 'meeting room accessed',
                                        'Value' => $response
                                    ], 200);
        
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
