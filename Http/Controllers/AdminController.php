<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use JWTAuth;
use DB;
use Session;
use View;
use Response;
use PaypalMassPayment;


use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Support\Facades\Input as Input;
use Illuminate\Support\Facades\Redirect;

class AdminController extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    
    public function checklogin(Request $request){
    	$email = $request->input('email');
    	$password = $request->input('password');
    	$checkdata = DB::select( DB::raw("SELECT * FROM 7e_admin where email='".$email."' and password='".$password."'"));
    	if (empty($checkdata)) {
    		return Redirect::back()->with('wrong', 'Email or password  is incorrect.');
    	}else{
            Session::put('loginSession',$checkdata);
            return redirect('/dashboard');
    	}
    }
    public function dashboard(){


        $date=date('Y-m-d');
        $date1=date('Y-m-d', strtotime('-1 day', strtotime($date)));
        $date2=date('Y-m-d', strtotime('-2 day', strtotime($date)));
        $date3=date('Y-m-d', strtotime('-3 day', strtotime($date)));
        $date4=date('Y-m-d', strtotime('-4 day', strtotime($date)));
        $date5=date('Y-m-d', strtotime('-5 day', strtotime($date)));
        $date6=date('Y-m-d', strtotime('-6 day', strtotime($date)));


        
        $dashboard['users'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 1"));
        $dashboard['professional'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 2"));
        $dashboard['appointments'] = count(DB::select("SELECT * FROM 7e_appointments "));
        $dashboard['question'] =count( DB::select("SELECT * FROM 7e_question "));



        $dashboard['date1']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 1 and DATE(date_created) = '".$date."'"));
        $dashboard['profdate1']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 2 and DATE(date_created) = '".$date."'"));
        $dashboard['appoint1']['count'] =count(DB::select("SELECT * FROM 7e_appointments WHERE  DATE(date_created) = '".$date."'"));

        $dashboard['date1']['dayname'] =date("D", strtotime($date));


        $dashboard['date2']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 1 and DATE(date_created) = '".$date1."'"));
        $dashboard['profdate2']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 2 and DATE(date_created) = '".$date1."'"));
        $dashboard['appoint2']['count'] =count(DB::select("SELECT * FROM 7e_appointments WHERE  DATE(date_created) = '".$date1."'"));
        $dashboard['date2']['dayname'] =date("D", strtotime($date1));




        $dashboard['date3']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 1 and DATE(date_created) = '".$date2."'"));
        $dashboard['profdate3']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 2 and DATE(date_created) = '".$date2."'"));
        $dashboard['appoint3']['count'] =count(DB::select("SELECT * FROM 7e_appointments WHERE  DATE(date_created) = '".$date2."'"));
        $dashboard['date3']['dayname'] =date("D", strtotime($date2));



        $dashboard['date4']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 1 and DATE(date_created) = '".$date3."'"));
        $dashboard['profdate4']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 2 and DATE(date_created) = '".$date3."'"));
        $dashboard['appoint4']['count'] =count(DB::select("SELECT * FROM 7e_appointments WHERE  DATE(date_created) = '".$date3."'"));
        $dashboard['date4']['dayname'] =date("D", strtotime($date3));


        $dashboard['date5']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 1 and DATE(date_created) = '".$date4."'"));
        $dashboard['profdate5']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 2 and DATE(date_created) = '".$date4."'"));
        $dashboard['appoint5']['count'] =count(DB::select("SELECT * FROM 7e_appointments WHERE  DATE(date_created) = '".$date4."'"));


        $dashboard['date5']['dayname'] =date("D", strtotime($date4));



        $dashboard['date6']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 1 and DATE(date_created) = '".$date5."'"));
        $dashboard['profdate6']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 2 and DATE(date_created) = '".$date5."'"));
        $dashboard['appoint6']['count'] =count(DB::select("SELECT * FROM 7e_appointments WHERE  DATE(date_created) = '".$date5."'"));

        $dashboard['date6']['dayname'] =date("D", strtotime($date5));



        $dashboard['date7']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 1 and DATE(date_created) = '".$date6."'"));
        $dashboard['profdate7']['count'] =count(DB::select("SELECT * FROM 7e_users WHERE user_type = 2 and DATE(date_created) = '".$date6."'"));
        $dashboard['appoint7']['count'] =count(DB::select("SELECT * FROM 7e_appointments WHERE  DATE(date_created) = '".$date6."'"));
        $dashboard['date7']['dayname'] =date("D", strtotime($date6));

        return view('examples.dashboard',[
            'dashboard' => $dashboard
        ]);
    }
    public function logout(){
        Session::forget('loginSession');
        return redirect('/');
    }
    public function userlisting(){
        $userlist = DB::select("SELECT * FROM 7e_users WHERE user_type = 1");
        foreach ($userlist as $key => $value) {
            $pen = DB::select("SELECT count(id) as count FROM 7e_appointments WHERE is_pending = 1 AND is_accepted = 0 AND  is_completed = 0 AND is_cancelled = 0 AND user_id = '".$value->id."'");
            $appr = DB::select("SELECT count(id) as count FROM 7e_appointments WHERE is_pending = 1 AND is_accepted = 1 AND is_completed = 0 AND is_cancelled = 0 AND user_id = '".$value->id."'");
            $value->pending = $pen[0]->count;
            $value->approved = $appr[0]->count;
        }
        return view('examples.user',[
            'users' => $userlist
        ]);
    }
    public function professional(){
        $proflist = DB::select("SELECT * FROM 7e_users WHERE user_type = 2");
        return view('examples.professional',[
            'users' => $proflist
        ]);
    }

    public function userdetail($id){
        $detail = DB::select("SELECT * FROM 7e_users  where id='".$id."'");
        $appoint['pending'] = DB::select("SELECT 7e_appointments.*,7e_users.name as cus_name,abc.name as professional FROM 7e_appointments join 7e_users on 7e_appointments.user_id=7e_users.id join 7e_users as abc on abc.id=7e_appointments.prof_id  WHERE user_id = '".$id."' and is_pending=1 and is_accepted=0 and is_completed=0 and is_cancelled=0 ");
     

        $appoint['accepted'] = DB::select("SELECT 7e_appointments.*,7e_users.name as cus_name,abc.name as professional FROM 7e_appointments join 7e_users on 7e_appointments.user_id=7e_users.id join 7e_users as abc on abc.id=7e_appointments.prof_id  WHERE user_id = '".$id."' and is_pending=1 and is_accepted=1 and is_completed=0 and is_cancelled=0 ");
 


        $appoint['completed'] = DB::select("SELECT 7e_appointments.*,7e_users.name as cus_name,abc.name as professional FROM 7e_appointments join 7e_users on 7e_appointments.user_id=7e_users.id join 7e_users as abc on abc.id=7e_appointments.prof_id  WHERE user_id = '".$id."' and is_pending=1 and is_accepted=1 and is_completed=1 and is_cancelled=0 ");
 

        $appoint['cancelled'] = DB::select("SELECT 7e_appointments.*,7e_users.name as cus_name,abc.name as professional FROM 7e_appointments join 7e_users on 7e_appointments.user_id=7e_users.id join 7e_users as abc on abc.id=7e_appointments.prof_id  WHERE user_id = '".$id."' and  is_cancelled=1 ");
        // echo "<pre>";
        // print_r($appoint['cancelled']);die;

        return view('examples.userdetail',[
            'detail' => $detail,'appointment'=>$appoint
        ]);
    }

    public function profdetail($id){
        $detail = DB::select("SELECT * FROM 7e_users  where id='".$id."'");
        return view('examples.professionaldetail',[
            'detail' => $detail
        ]);
    }

    public function approvedtreat($id){
        $update = DB::table('7e_treatment')
            ->where('id' , $id)
            ->update([
                'is_approved'=>1
            ]);
        return Redirect::back()->with('success', 'Sucessfully approved treatment.');
    }
    public function rejecttreat($id){
        $update = DB::table('7e_treatment')
            ->where('id' , $id)
            ->update([
                'is_rejected'=>1
            ]);
        return Redirect::back()->with('success', 'Sucessfully rejected treatment.');
    }




    public function pendingtreatment(){
        $sel = DB::select("SELECT * FROM 7e_treatment WHERE is_approved = 0 and is_rejected=0");
        return view('examples.treatmentpending',['pending'=>$sel]);
    }

    public function approvedtreatment(){
        $sel = DB::select("SELECT * FROM 7e_treatment WHERE is_approved = 1 and is_rejected = 0");
        return view('examples.treatmentapproved',['approved'=>$sel]);
    }
    

    public function rejecttreatment(){
        $sel = DB::select("SELECT * FROM 7e_treatment WHERE is_rejected = 1");
        return view('examples.treatmentrejected',['reject'=>$sel]);
    }



    public function treatmentdeatil($id){
        $sel = DB::select("SELECT 7e_treatment.*,7e_users.name,7e_users.email,7e_users.phone FROM 7e_treatment  JOIN 7e_users ON 7e_users.id = 7e_treatment.prof_id WHERE 7e_treatment.id = '".$id."'");
        return view('examples.treatmentdetail',['detail'=>$sel]);
    }



    Public function listtimezone(){
        $timezone=DB::select("SELECT * from 7e_timezones ");
        return view('examples.timezone',['time'=>$timezone]); 
    }

    public function adminsetting(){
        $setting = DB::select("SELECT * FROM 7e_setting");
        return view('examples.adminsetting',['setting'=>$setting]);
    }


    public function editsetting(Request $request){

        $percentage=$request->percentage;

        if ($percentage<0 || $percentage>100 ) {
            return Redirect::back()->with('success', 'Please enter valid percentage.'); 
        }
        $terms=$request->terms;
        $time=$request->time;
        $id=$request->id;
        $update_setting = DB::table('7e_setting')
            ->where('id' , $id)
            ->update([
            'admin_percentage' => $percentage,
            'term_condition' => $terms,
            'buffer_time' => $time
            ]);
        return Redirect::back()->with('success', 'Sucessfully updated setting.');



    }
    public function addtime(Request $request){
        $time_zone=$request->time_zone;
        $description=$request->description;
        $timezones = DB::table('7e_timezones')->insertGetId(
        ['time_zone' =>$time_zone,'description' =>$description]
        );
        return Redirect::back()->with('success', 'Sucessfully added time zone.');
    }

    public function reviews(){
        $review = DB::select("SELECT user_id,to_id,rating,comment,7e_users.name,abc.name as profname FROM 7e_rating join 7e_users on 7e_users.id=7e_rating.user_id join 7e_users as abc on abc.id=7e_rating.to_id ");
        return view('examples.reviews',['review'=>$review]);
    }


    public function addprofessional(Request $request){
        $checkdata = DB::select( DB::raw("SELECT * FROM 7e_users where email='".$_POST['email']."'"));

        if (empty($checkdata)) {
            $email=$request->email;
            $name=$request->name;
            $phone=$request->phone;
            $profession=$request->profession;
            $mind=$request->mind;
            $body=$request->body;
            $face=$request->face;

            if (empty($email) || empty($name)  || empty($phone) || empty($profession) ) {
                return Redirect::back()->with('wrong', 'Please fill required field.');  
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return Redirect::back()->with('wrong', 'Email not valid.');
            }

            if (empty($mind)) {
                $mind=0;
            }
            if (empty($body)) {
                $body=0;
            }
            if (empty($face)) {
                $face=0;
            }

            $password=rand(12000,150000);
            $body="Your password for login ".$password;
            $subject = "Password";
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: <7econnect@gmail.com>' . "\r\n";
            $abc=mail($email,$subject,$body,$headers);

     
            $users = DB::table('7e_users')->insertGetId(
                ['email' =>$email,'password'=>md5($password),'name' =>$name,'phone' =>$phone,'signup_level' =>0, 'user_type' =>2, 'is_complete' =>0]
            ); 
            $professional = DB::table('7e_professional')->insertGetId(
                ['prof_id' =>$users,'mind' =>$mind,'body' =>$body,'face' =>$face, 'text_chat' =>0,'audio_call' =>0,'video_call' =>0,'status'=>1]
            );
            $wallet = DB::table('7e_profwallet')->insertGetId(
                ['prof_id' =>$users]
            );
            return Redirect::back()->with('success', 'Sucessfully added professional.');
        }
        else{
            return Redirect::back()->with('wrong', 'Email already exists.');
        }

    }
    public function pendingpayments(){
        $payments = DB::table('7e_transaction')
            ->join('7e_users', '7e_users.id', '=', '7e_transaction.user_id')
            ->join('7e_users as abc', 'abc.id', '=', '7e_transaction.prof_id')
                  ->select('7e_transaction.*', '7e_users.name', '7e_users.email','7e_users.profile_pic','abc.name as prof_name')
                  ->where('7e_transaction.type',1)
                    ->get();
        return view('examples.paymentpending',['payments'=>$payments]);
    }




    public function readypayments(){
        $payments = DB::table('7e_transaction')
            ->join('7e_users', '7e_users.id', '=', '7e_transaction.user_id')
            ->join('7e_users as abc', 'abc.id', '=', '7e_transaction.prof_id')
                  ->select('7e_transaction.*', '7e_users.name', '7e_users.email','7e_users.profile_pic','abc.name as prof_name')
                  ->where('7e_transaction.type',1)
                    ->get();
        return view('examples.paymentready',['payments'=>$payments]);
    }




    public function completepayments(){
        $payments = DB::table('7e_transaction')
            ->join('7e_users', '7e_users.id', '=', '7e_transaction.user_id')
            ->join('7e_users as abc', 'abc.id', '=', '7e_transaction.prof_id')
                  ->select('7e_transaction.*', '7e_users.name', '7e_users.email','7e_users.profile_pic','abc.name as prof_name')
                  ->where('7e_transaction.type',1)
                    ->get();
        return view('examples.paymentcompleted',['payments'=>$payments]);
    }


    public function refundpayments(){
        $payments = DB::table('7e_transaction')
            ->join('7e_users', '7e_users.id', '=', '7e_transaction.user_id')
            ->join('7e_users as abc', 'abc.id', '=', '7e_transaction.prof_id')
                  ->select('7e_transaction.*', '7e_users.name', '7e_users.email','7e_users.profile_pic','abc.name as prof_name')
                  ->where('7e_transaction.type',1)
                    ->get();
        return view('examples.paymentrefund',['payments'=>$payments]);
    }


    public function adduser(Request $request){
        $checkdata = DB::select( DB::raw("SELECT * FROM 7e_users where email='".$_POST['email']."'"));

        if (empty($checkdata)) {
            $email=$request->email;
            $name=$request->name;
            $phone=$request->phone;
            $password=$request->password;

            if (empty($email) || empty($name)  || empty($phone) || empty($password) ) {
                return Redirect::back()->with('wrong', 'Please fill required field.');  
            }



            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return Redirect::back()->with('wrong', 'Email not valid.');
            }



            $users = DB::table('7e_users')->insertGetId(
                ['email' =>$email,'name' =>$name,'phone' =>$phone,'password' =>md5($password), 'user_type' =>1, 'is_complete' =>0]
            ); 
            return Redirect::back()->with('success', 'Sucessfully added user.');
        }
        else{
            return Redirect::back()->with('wrong', 'Email already exists.');
        }

    }
    public function edituserdetail(Request $request){
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return Redirect::back()->with('wrong', 'Email not valid.');
        }
        if (empty($request->name) || empty($request->email)  || empty($request->phone)  ) {
            return Redirect::back()->with('wrong', 'Please fill required field.');  
        }

        $update_user = DB::table('7e_users')
            ->where('id' , $request->id)
            ->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone
            ]);
        return Redirect::back()->with('success', 'Sucessfully updated user.');
    }


    public function activate($id){
        $data=array('is_suspend' =>'0');
        $update_user = DB::table('7e_users')
                      ->where('id' , $id)
                     ->update(
                        $data
                        );
        return Redirect::back()->with('success', 'Sucessfully activated.');
    } 
    public function deletetime($id){
        DB::table('7e_timezones')->where('id', $id)->delete();
        return Redirect::back()->with('success', 'Sucessfully deleted.');
    }

    public function appointmentdetail($id){
        $detail = DB::select("SELECT *,7e_appointments.id as appoitid,7e_users.name as username,abc.name as profname,abc.email as profemail,7e_users.email as usersemail  FROM 7e_appointments LEFT JOIN 7e_users on 7e_users.id=7e_appointments.user_id LEFT JOIN 7e_users as abc on abc.id=7e_appointments.prof_id where 7e_appointments.id='".$id."' ");
        return view('examples.appointmnetdetail',[
            'detail' => $detail
        ]);
    }







    public function getuserdata(Request $request){
            $userdata = DB::table('7e_users')
                  ->where('7e_users.id',$request->user_id)
                   ->get();
                   print_r(json_encode($userdata));die;
    }


    public function suspend($id){
        $update_user = DB::table('7e_users')
                      ->where('id' , $id)
                     ->update([
                        'is_suspend' =>'1',
                        ]);
        return Redirect::back()->with('success', 'Sucessfully suspended.');
    } 
    /*common file upload function*/
    public function file_upload($image,$name,$destinationPath){
    	$name=rand(10000,100000);
        $input['imagename'] = $name.time().'.'.$image->getClientOriginalExtension();
        $image->move($destinationPath, $input['imagename']);
        return $input['imagename'];
    }

    public function appointpending(){
        $detail = DB::select("SELECT *,7e_appointments.id as appoitid,7e_users.name as username,abc.name as profname  FROM 7e_appointments LEFT JOIN 7e_users on 7e_users.id=7e_appointments.user_id LEFT JOIN 7e_users as abc on abc.id=7e_appointments.prof_id where 7e_appointments.is_pending=1 and 7e_appointments.is_accepted=0 and 7e_appointments.is_completed=0 and 7e_appointments.is_cancelled=0 ");
        return view('examples.appointmentpending',[
            'detail' => $detail
        ]);
    }

    public function appointapproved(){
        $detail = DB::select("SELECT *,7e_appointments.id as appoitid,7e_users.name as username,abc.name as profname  FROM 7e_appointments LEFT JOIN 7e_users on 7e_users.id=7e_appointments.user_id LEFT JOIN 7e_users as abc on abc.id=7e_appointments.prof_id where 7e_appointments.is_pending=1 and 7e_appointments.is_accepted=1 and  7e_appointments.is_completed=0  and 7e_appointments.is_cancelled=0 ");
        return view('examples.appointmentapproved',[
            'detail' => $detail
        ]);

    }


    public function appointcompleted(){
        $detail = DB::select("SELECT *,7e_appointments.id as appoitid,7e_users.name as username,abc.name as profname  FROM 7e_appointments LEFT JOIN 7e_users on 7e_users.id=7e_appointments.user_id LEFT JOIN 7e_users as abc on abc.id=7e_appointments.prof_id where 7e_appointments.is_pending=1 and 7e_appointments.is_accepted=1 and  7e_appointments.is_completed=1 and 7e_appointments.is_cancelled=0 ");
        return view('examples.appointmentcompleted',[
            'detail' => $detail
        ]);

    }

    public function appointcancelled(){
        $detail = DB::select("SELECT *,7e_appointments.id as appoitid,7e_users.name as username,abc.name as profname  FROM 7e_appointments LEFT JOIN 7e_users on 7e_users.id=7e_appointments.user_id LEFT JOIN 7e_users as abc on abc.id=7e_appointments.prof_id where  7e_appointments.is_cancelled=1 ");
        return view('examples.appointmentcancelled',['detail' => $detail]);
    }
    public function addedtime(Request $request){
        print_r($request->id);die;
        // if (!empty($_GET)) {
        //     print_r($_GET);die;
        // }
        // else{
        //     return view('examples.addtimezone');
        // }
    }
    public function edittime($id){
        $detail = DB::select("SELECT *  FROM 7e_timezones  where  id='".$id."' ");
        return view('examples.edittimezone',['detail' => $detail]);
        // print_r($id);die;
    }


    public function edittimezone(Request $request){
        $user=$request->user_id;
        $getforgot = DB::select( DB::raw("SELECT * from 7e_timezones where id='".$user."'"));
        // print_r(json_encode($getforgot));die;
    }

    public function edittimezonedata(Request $request){

                $data=array('time_zone'=>$request->timezone,'description'=>$request->description);
                $data=array_filter($data);
                $update_user = DB::table('7e_timezones')
                      ->where('id' , $request->id)
                     ->update($data);
        return Redirect::back()->with('success', 'Sucessfully updated.');
    }



    public function querybuilder(Request $request){
  //   	$users = DB::table('7e_users')->select('name', 'email as user_email')->where('id',1)->first();
		// $upt1=DB::table('7e_users')
		// ->where('id', $user_id)
		// ->update($data);


		// $bac="SELECT * from 7e_users";
    	/*FILTERING QUERIES*/
		$result = DB::table('7e_users');
			if ($request->id) {
				$result->where('id', $request->id);
			}
			if ($request->is_complete) {
				$result->where('is_complete', $request->is_complete);
			}
		$abc=$result->get();
		/*FILTERING QUERIES END*/
		print_r($abc);die;
		$getforgot = DB::select( DB::raw("SELECT * from 7e_users"));
		$getforgot->where('id', '=', 1);
		$getforgot = DB::select( DB::raw("SELECT * FROM 7e_forgotpassword where user_id='".$id."'"));
		$id = DB::table('7e_forgotpassword')->insertGetId(
			['user_id' =>$results->id,'date_modified' =>date('Y-m-d H:i:s'), 'status' =>1]
		); 


    	print_r($users);die;

    }

    public function updateProDetail(Request $request) {
        if (empty($request->name) || empty($request->country)  || empty($request->city) || empty($request->user_id) ) {
            return Redirect::back()->with('wrong', 'Please fill required field.');  
        }
        if (!filter_var($request->country, FILTER_VALIDATE_EMAIL)) {
            return Redirect::back()->with('wrong', 'Email not valid.');
        }
        $update_user = DB::table('7e_users')
        ->where('id' , $request->user_id)
        ->update([
        'name' => $request->name,
        'email' => $request->country,
        'phone' => $request->city
        ]);
        return Redirect::back()->with('success', 'Sucessfully updated professional.');
    }






    public function approveappointment($id){
        $updateappointment = DB::table('7e_appointments')
        ->where('id' , $id)
        ->update([
        'is_accepted' => 1,
        ]);
        return Redirect::back()->with('success', 'Sucessfully approved appointment.');
    }
    public function rejectappointment($id){

        $updateappointment = DB::table('7e_appointments')
        ->where('id' , $id)
        ->update([
        'is_cancelled' => 1,
        ]);
        return Redirect::back()->with('success', 'Sucessfully cancelled appointment.');
    }

}
