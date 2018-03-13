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
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Support\Facades\Input as Input;
use Illuminate\Support\Facades\Redirect;
use Session;

class ProfController extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    
    public function checklogin(Request $request){
    	$email=$request->email;
    	$password=$request->password;
    	$checkdata = DB::select( DB::raw("SELECT * FROM 7e_admin where email='".$email."' and password='".$password."'   "));
    	if (empty($checkdata)) {
    		return Redirect::back()->with('wrong', 'Email or password  is incorrect.');
    	}
    	else{
    		Session::set('variableName', $checkdata);
    		echo "fsdjfgdfsg";die;
    	}
    }

   
    /*common file upload function*/
    public function file_upload($image,$name,$destinationPath){
    	$name=rand(10000,100000);
        $input['imagename'] = $name.time().'.'.$image->getClientOriginalExtension();
        $image->move($destinationPath, $input['imagename']);
        return $input['imagename'];
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

}
