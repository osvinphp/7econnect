<?php

namespace App\Http\Controllers;
use \Firebase\JWT\JWT;
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
use Cartalyst\Stripe\Exception\ServerErrorException;
use PushNotification;
use Cartalyst\Stripe\Stripe;
use PaypalMassPayment;




class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    
    public function signup(Request $request){

        $data=array(
            'name'=>$request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'fb_id'=>$request->fb_id,
            'password'=>md5($request->password)
        );

        $date_of_birth=$request->date_of_birth;
        $marital_status=$request->marital_status;
        $occupation=$request->occupation;
        $user_id=$request->user_id;

        $time_zone=$request->time_zone;



		$device_id=$request->device_id;
		$unique_deviceId=$request->unique_deviceId;
		$token_id=$request->token_id;
		// print_r($data);die;

        $type=$request->type;
        $level=$request->signup_level;
        if ($level==1) {
            if ($type==1) {

                $results = DB::select( DB::raw("SELECT * FROM 7e_users where email='".$data['email']."'"));

               if (empty($results)) {
           			// print_r($data);die;
               		// $zoom_id=$this->zoomdata($data);
               		// print_r($zoom_id);die;
               	$zoom_id='4125';
                     $id = DB::table('7e_users')->insertGetId(
                        ['email' => $data['email'], 'name' => $data['name'],'phone' => $data['phone'],'password' => $data['password'],'is_complete'=>0,'user_type'=>1,'is_suspend'=>0,'time_zone'=>$time_zone,'zoom_id'=>$zoom_id]
                    );




					$logindata = DB::table('7e_login')->insertGetId(
					['device_id' => $device_id,'unique_deviceId' => $unique_deviceId, 'token_id' => $token_id,'user_id'=>$id,'status'=>1]
					);  
                    $results['userdata'] = DB::select( DB::raw("SELECT * FROM 7e_users where id='".$id."'"));  
                    $results['timezones']=DB::select( DB::raw("SELECT * FROM 7e_timezones ") );

                }
                // elseif($results[0]->is_complete==0){
                //     $results= 2;//already exist
                // }
                else{
                    $results= 1;//already exist
                }
                
            }
            elseif ($type==2) {
            $results = DB::select( DB::raw("SELECT * FROM 7e_users where fb_id='".$data['fb_id']."'") );

               if (empty($results)) {
                    if (empty($data['email'])) {
                    	
                        $id = DB::table('7e_users')->insertGetId(
                        ['fb_id' => $data['fb_id'], 'name' => $data['name'],'phone' => $data['phone'],'password' => $data['password'],'is_complete'=>0,'user_type'=>1,'is_suspend'=>0]
                        ); 

						$logindata = DB::table('7e_login')->insertGetId(
						['device_id' => $device_id,'unique_deviceId' => $unique_deviceId, 'token_id' => $token_id,'user_id'=>$id,'status'=>1]
						);  
                        $results['userdata'] = DB::select( DB::raw("SELECT * FROM 7e_users where id='".$id."'") ); 
                        $results['timezones']=DB::select( DB::raw("SELECT * FROM 7e_timezones ") ); 
                    }
                    else{
                        $results = DB::select( DB::raw("SELECT * FROM 7e_users where email='".$data['email']."'") );
                        if (!empty($results)) {
                            $users=DB::table('7e_users')
                                ->where('email', $data['email'])
                                    ->update(array('fb_id' => $data['fb_id'], 'name' => $data['name'],'phone' => $data['phone'],'password' => $data['password'],'is_complete'=>1,'is_suspend'=>0));
							$logindata = DB::table('7e_login')->insertGetId(
								['device_id' => $device_id,'unique_deviceId' => $unique_deviceId, 'token_id' => $token_id,'user_id'=>$results[0]->id,'status'=>1]
							);  

                            $results['userdata'] = DB::select( DB::raw("SELECT * FROM 7e_users where email='".$data['email']."'") );
                            $results['timezones']=DB::select( DB::raw("SELECT * FROM 7e_timezones ") );
                        }
                        else{
                        	
                            $id = DB::table('7e_users')->insertGetId(
                            ['fb_id' => $data['fb_id'],'email' => $data['email'], 'name' => $data['name'],'phone' => $data['phone'],'password' => $data['password'],'is_complete'=>0,'user_type'=>1,'is_suspend'=>0]
                            );

							$logindata = DB::table('7e_login')->insertGetId(
							['device_id' => $device_id,'unique_deviceId' => $unique_deviceId, 'token_id' => $token_id,'user_id'=>$id,'status'=>1]
							);   
                            $results['userdata'] = DB::select( DB::raw("SELECT * FROM 7e_users where id='".$id."'") );
                            $results['timezones']=DB::select( DB::raw("SELECT * FROM 7e_timezones ") );
                        }

                    }

                }
                // elseif($results[0]->is_complete==0){
                //     $results= 2;//profile not completed
                // }
                else{
                    $results= 1;//already exist
                }
                
            }
            if ($results!=1  && !empty($results)){
                return Response::json([
                'ResponseCode'=>true,
                'message'=>"Sucessfully signed up.",
                'response' => $results,
                ], 200);
            }
            elseif($results==1){
                return Response::json([
                    'ResponseCode'=>false,
                    'message' => "Email already exists",
                    ], 200);
            }

            else{
                return Response::json([
                	'ResponseCode'=>false,
                    'message' => "Something went wrong."
                    ], 200);
            }
        }
        elseif($level==2){
            $data=array('date_of_birth' => $date_of_birth,'marital_status' => $marital_status,'occupation' => $occupation,'is_complete'=>1,'time_zone'=>$time_zone);


        	if (isset($_FILES['profile_pic'])) {
	            $image = $request->file('profile_pic');
	            $name=str_replace(' ', '_',$_FILES['profile_pic']['name']);
	            $destinationPath = public_path('apiimage/');
	            $request=array('image'=>$image,'name'=>$name,'destinationPath'=>$destinationPath);
	            $abc=$this->file_upload($request['image'],$request['name'],$request['destinationPath']);
	            $data['profile_pic']=$abc;
            }


            $users=DB::table('7e_users')
            ->where('id', $user_id)
            ->update($data);


            $results = DB::select( DB::raw("SELECT * FROM 7e_users where id='".$user_id."'") );
            if (!empty($results)){
                return Response::json([
                'ResponseCode'=>true,
                'message'=>"Sucessfully signed up.",
                'response' => $results,
                ], 200);
            }

        }
    }
    /*common file upload function*/
    public function file_upload($image,$name,$destinationPath){
    	$name=rand(10000,100000);
        $input['imagename'] = $name.time().'.'.$image->getClientOriginalExtension();
        $image->move($destinationPath, $input['imagename']);
        $input['imagename']='/apiimage/'.$input['imagename'];
        return $input['imagename'];
    }
    public function login(Request $request){
    	if (isset($request->email) && isset($request->fb_id) && isset($request->password) && isset($request->type) && isset($request->device_id) &&  isset($request->unique_deviceId) && isset($request->token_id) ) {
	        $data=array(
	            'email'=>$request->email,
	            'fb_id'=>$request->fb_id,
	            'password'=>md5($request->password),
	            );
	        $type=$request->type;

	        $device_id=$request->device_id;
	        $unique_deviceId=$request->unique_deviceId;
	        $token_id=$request->token_id;

	        if ($type==1) {
	            $res = DB::select( DB::raw("SELECT * FROM 7e_users where email='".$data['email']."'"));
	            if (!empty($res)) {
	                $res = DB::select( DB::raw("SELECT * FROM 7e_users where email='".$data['email']."' and password='".$data['password']."' "));
	                if (empty($res)) {
	                	$results['type']=1;
	                    $results['result']="Wrong Password!";//password not completed
	                }
	                elseif($res[0]->is_suspend==0) {
	                    $id = DB::table('7e_login')->insertGetId(
	                            ['device_id' => $device_id,'unique_deviceId' => $unique_deviceId, 'token_id' => $token_id,'user_id'=>$res[0]->id,'status'=>1]
	                            ); 
	                    $results['result'] = DB::table('7e_users')->where('email', $data['email'])->first();
	                    $results['timezones']=DB::select( DB::raw("SELECT * FROM 7e_timezones ") );
	                    $results['type']=2;
	                    $results['message']="Sucessfully logged in.";

	                }
	                else{
	                	$results['type']=7;
	                    $results['result']="Account suspended by admin!";//password not completed
	                }

	            }
	            else{
	            	$results['type']=3;
	                $results['result']="Email  does not exist.";//email or password not exists
	            } 
	        }
	        elseif ($type==2) {
	            $res = DB::select( DB::raw("SELECT * FROM 7e_users where fb_id='".$data['fb_id']."'"));

	            if (empty($res)) {
            		if (!empty($data['email'])) {
            			$emailres = DB::select( DB::raw("SELECT * FROM 7e_users where email='".$data['email']."'"));
            			if (!empty($emailres) && $emailres[0]->is_suspend==0) {
							$data=array_filter($data);
							$upt1=DB::table('7e_users')
							->where('id', $emailres[0]->id)
							->update($data);
							$id = DB::table('7e_login')->insertGetId(
							['device_id' => $device_id,'unique_deviceId' => $unique_deviceId, 'token_id' => $token_id,'user_id'=>$res[0]->id,'status'=>1]
							); 
							$results['result'] = DB::table('7e_users')->where('id', $emailres[0]->id)->first();
							$results['type']=2;
            				$results['message']="Sucessfully logged in.";
            				$results['timezones']=DB::select( DB::raw("SELECT * FROM 7e_timezones ") );
							

            			}
            			elseif($emailres[0]->is_suspend==1){
            				$results['type']=7;
	                    	$results['result']="Account suspended by admin!";//Account suspend
            			}
            			else{
            				$results['type']=4;
            				$results['result']="User does not exists.";
            			}

					}
					else{
						$results['type']=5;
	                	$results['result']="Facebook account does not exist.";//email or fb_id not exists 
					}
	            }
	            elseif($res[0]->is_suspend==0){
	            		$id = DB::table('7e_login')->insertGetId(
						['device_id' => $device_id,'unique_deviceId' => $unique_deviceId, 'token_id' => $token_id,'user_id'=>$res[0]->id,'status'=>1]
						); 
						$results['type']=2;
						$results['result'] = DB::table('7e_users')->where('fb_id', $data['fb_id'])->first();
						$results['mesasage']['message']="Sucessfully logged in.";
						$results['timezones']=DB::select( DB::raw("SELECT * FROM 7e_timezones ") );
	            } 
	            else{
	            		$results['type']=7;
	                    $results['result']="Account suspended by admin!";//password not completed

	            }
	        }
	            if ($results['type']==2)  {
	                return Response::json([
	                'ResponseCode'=>true,
	                'message' => 'Your data shows sucessfully.',
	                'response'=>$results,
	                ], 200);
	            }
	            elseif($results['type']==1){
	                return Response::json([
	                'ResponseCode'=>false,
	                'message' => $results
	                ], 200);
	            }
				elseif($results['type']==3){
					return Response::json([
					'ResponseCode'=>false,
					'message' => $results
					], 200);
				}
				elseif($results['type']==4){
					return Response::json([
					'ResponseCode'=>false,
					'message' => $results
					], 200);
				}
				elseif($results['type']==5){
					return Response::json([
					'ResponseCode'=>false,
					'message' => $results
					], 200);
				}
				else{
					return Response::json([
					'ResponseCode'=>false,
					'message' => "Something went wrong!",
					], 200);
				}
            }
        else{
			return Response::json([
			'ResponseCode'=>false,
			'message' => "Please fill required parameter!",
			], 200);
        }
    }
    public function logout(Request $request){
    	if (isset($request->user_id) && isset($request->unique_deviceId)  ) {
	        $user_id=$request->user_id;
	        $unique_deviceId=$request->unique_deviceId;
	        $users=DB::table('7e_login')
	        ->where('user_id', $user_id)
	        ->where('unique_deviceId', $unique_deviceId)
	        ->update(array('status' => 0));
	        if ($users) {
	            return Response::json([
	            'ResponseCode'=>true,
	            'message' => "Succesfully logged out!"
	            ], 200);
	        }
	        else{
	            return Response::json([
	            'ResponseCode'=>false,
	            'message' => "Something Went Wrong!"
	            ], 200);
	        }
    	}
    	else{
			return Response::json([
			'ResponseCode'=>false,
			'message' => "Please fill required parameter!",
			], 200);
    	}
    }

    public function forgotpassword(Request $request){
    	if (isset($request->email)) {
	        $to=$request->email;
	        $results= DB::table('7e_users')->where('email', $to)->first();
	        if ($results) {
	            $static_key = "afvsdsdjkldfoiuy4uiskahkhsajbjksasdasdgf43gdsddsf";
	            $id = $results->id . "_" . $static_key;
	            $result = base64_encode($id);
	            $url= url('/api/newpassword?id='.$result);

	            $getforgot = DB::select( DB::raw("SELECT * FROM 7e_forgotpassword where user_id='".$results->id."'"));
	            if (empty($getforgot)) {
	                $id = DB::table('7e_forgotpassword')->insertGetId(
	                ['user_id' =>$results->id,'date_modified' =>date('Y-m-d H:i:s'), 'status' =>1]
	                ); 
	            }
	            else{
	                $users=DB::table('7e_forgotpassword')
	                ->where('user_id', $results->id)
	                ->update(array('status' =>1,'date_modified' => date('Y-m-d H:i:s')));
	            }
	            $body= '
	            <html>
	            <head>
	            <title>HEllo '.$results->name.'</title>
	            </head>
	            <body>
	            <table cellspacing="0" style="border: 2px dashed #FB4314; width: 300px; height: 200px;">
	            <tr>
	            <th>Please press the link to reset password.</th><td><a href=' . $url.'>Reset Password</a></td>
	            </tr>
	            </table>
	            </body>
	            </html>';
	            // $body='<!DOCTYPE html>
	            // <html>
	            // <head>
	            // </head>

	            // <body style="font-family: Arial; font-size: 12px;">
	            // <div>
	            // <p>
	            // You have requested a password reset, please follow the link below to reset your password.
	            // </p>
	            // <p>
	            // Please ignore this email if you did not request a password change.
	            // </p>

	            // <p>
	            // <a href="">
	            // Follow this link to reset your password.
	            // </a>
	            // </p>
	            // </div>
	            // </body>
	            // </html>';

	            $subject = "Forgot Password";
	            $headers = "MIME-Version: 1.0" . "\r\n";
	            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	            $headers .= 'From: <7econnect@gmail.com>' . "\r\n";
	            $abc=mail($to,$subject,$body,$headers);
	            return Response::json([
	            	'ResponseCode'=>true,
	            'message' => "Please check your email."
	            ], 200);
	        }
	        else{
	            return Response::json([
	            	'ResponseCode'=>false,
	            'message' => "Email does not exists!"
	            ], 200);
	        }
    	}
        else{
			return Response::json([
				'ResponseCode'=>false,
			'message' => "Please fill required parameter!",
			], 200);
		}
    }

    public function newpassword(Request $request){

        $user_id=$request->id;
        if ($user_id!="") {
           $user_id = base64_decode($user_id);
        }
        else{
           $user_id = base64_decode($user_id);
        }
        $useridArr = explode("_", $user_id);
        $user_id = $useridArr[0];
        $data['id'] = $user_id;
        $data['title'] = "new Password";
        return view("resetpassword",$data);
    }
    function updateNewpassword(Request $request){
        $id=$request->id;
        $password=$request->password;
        $passconf=$request->passconf;
        if ($password!=$passconf) {
            return Redirect::back()->with('success', 'First password and conrfirm password not matched.');
        }
        else{
            $getforgot = DB::select( DB::raw("SELECT * FROM 7e_forgotpassword where user_id='".$id."'"));
            $sendtime=$getforgot[0]->date_modified;
            $time=date('Y-m-d H:i:s');
            $det= date('Y-m-d H:i:s', strtotime("$sendtime  +30 minutes"));
            /*checking that user can update password only in 30 minute*/
            if ($time <= $det && $getforgot[0]->status==1) {
                $upt1=DB::table('7e_users')
                ->where('id', $id)
                ->update(array('password' =>md5($password)));
                $upt2=DB::table('7e_forgotpassword')
                ->where('user_id', $id)
                ->update(array('status' =>0));
                return Redirect::back()->with('success', 'Sucessfyully changed password.');
            }
            else{
                return Redirect::back()->with('success', 'Session expired.');
            }
        }
    }
    public function editprofile(Request $request){
    	if (isset($request->name) && isset($request->email) && isset($request->phone) && isset($request->date_of_birth) && isset($request->marital_status) &&  isset($request->occupation) && isset($_FILES['profile_pic'])  ) {

			$data=array(
			'name'=>$request->name,
			'email'=>$request->email,
			'phone'=>$request->phone,
			'date_of_birth'=>$request->date_of_birth,
			'marital_status'=>$request->marital_status,
			'occupation'=>$request->occupation,
			'time_zone'=>$request->time_zone
			);
			$user_id=$request->user_id;
			$image = $request->file('profile_pic');
			$name=str_replace(' ', '_',$_FILES['profile_pic']['name']);
			$destinationPath = public_path('apiimage/');
			$request=array('image'=>$image,'name'=>$name,'destinationPath'=>$destinationPath);
			$data['profile_pic']=$this->file_upload($request['image'],$request['name'],$request['destinationPath']);

			$data=array_filter($data);
			$upt1=DB::table('7e_users')
			->where('id', $user_id)
			->update($data);

			$result = DB::table('7e_users')->where('id', $user_id)->first();

			return Response::json([
			'ResponseCode'=>true,
			'response' => $result,
			'message'=>'Your profile updated Sucessfully.'
			], 200);
		}
		else{
			return Response::json([
			'ResponseCode'=>false,
			'message' => "Please fill required parameter!",
			], 200);
		}
    }
    public function changepassword(Request $request){
		if (isset($request->oldpassword) && isset($request->newpassword) && isset($request->user_id)   ) {
			$oldpassword=md5($request->oldpassword);
			$newpassword=md5($request->newpassword);
			$user_id=$request->user_id;
			$data=array('password'=>$newpassword);
			$result = DB::table('7e_users')->where('id', $user_id)->where('password', $oldpassword)->first();
			if ($result) {
				$upt1=DB::table('7e_users')
				->where('id', $user_id)
				->update($data);
				return Response::json([
					'ResponseCode'=>true,
				'message' => 'Your password changed sucessfully.'
				], 200);
			}
			else{
				return Response::json([
					'ResponseCode'=>false,
				'message' => 'Old password does not matched!'
				], 200);

			}
		}
		else{
			return Response::json([
				'ResponseCode'=>false,
			'message' => "Please fill required parameter!",
			], 200);
		}

    }
    public function pushnotification(Request $request){
    	if (isset($request->pushstatus) && isset($request->user_id)) {
    		$pushstatus=$request->pushstatus;
    		$user_id=$request->user_id;

    		$data=array('push_notification'=>$pushstatus);
			$upt1=DB::table('7e_users')
			->where('id', $user_id)
			->update($data);
			if($upt1==1){
				return Response::json([
					'ResponseCode'=>true,
				'message' => 'Your Push status updated sucessfully.'
				], 200);
			}
			else{
				return Response::json([
					'ResponseCode'=>false,
				'message' => 'Something went wrong.'
				], 200);
			}

    	}
    	else{
			return Response::json([
				'ResponseCode'=>false,
			'message' => "Please fill required parameter!",
			], 200);
    	}    
    }
    public function addrating(Request $request){
		if (isset($request->rating) && isset($request->user_id) && isset($request->to_id)) {
			$rating=$request->rating;
			$comment=$request->comment;
			$user_id=$request->user_id;
			$to_id=$request->to_id;
			$id = DB::table('7e_rating')->insertGetId(
			['user_id' =>$user_id,'rating' =>$rating,'to_id'=>$to_id,'comment'=>$comment]
			); 
			if (!empty($id)) {
				return Response::json([
				'ResponseCode'=>true,
				'message' => "Your rating sucessfully submitted.",
				], 200);
			}
			else{
				return Response::json([
				'ResponseCode'=>false,
				'message' => 'Something went wrong.'
				], 200);
			}
		}
		else{
			return Response::json([
			'ResponseCode'=>false,
			'message' => "Please fill required parameter!",
			], 200);
		}
    }
    public function addcard(Request $request){
    	$user_id=$request->user_id;
    	$card_number=$request->card_number;
    	$card_token=$request->card_token;
    	$card_type=$request->card_type;
    	$expiry_month=$request->expiry_month;
    	$expiry_year=$request->expiry_year;
    	$stripe = Stripe::make('sk_test_AV45bRuYmLu0nLFvLggzTQjF', '2016-07-06');



    	$users = DB::table('7e_cards')->select('*')->where('user_id',$user_id)->where('card_number',$card_number)->first();
    	if (empty($users)) {

			$customer = $stripe->customers()->create([
				"description" => "work",
				'source'=>$card_token
			]);
			// print_r($customer);die;
			$id=$customer['id'];

			$carddata = DB::table('7e_cards')->insertGetId(
				['user_id' =>$user_id,'card_number' =>$card_number,'card_type' =>$card_type,'expiry_year'=>$expiry_year,'expiry_month'=>$expiry_month,'card_token'=>$card_token]
			);
			$stripedata = DB::table('7e_stripedetails')->insertGetId(
				['user_id' =>$user_id,'cus_id' =>$id,'card_number'=>$card_number]
			);
			return Response::json([
			'ResponseCode'=>true,
			'message' => "You have sucessfully added card.",
			], 200);
    		
    	}
    	else{
			return Response::json([
			'ResponseCode'=>false,
			'message' => 'Card already exists.'
			], 200);

    	}

    }
    public function cardlisting(Request $request){
    	$user_id=$request->user_id;
    	$users = DB::table('7e_cards')->select('*')->where('user_id',$user_id)->get();
    	if (!empty($users)) {
			return Response::json([
			'ResponseCode'=>true,
			'message'=>"Your data show sucessfully.",
			'response' => $users,
			], 200);
    	}
    	elseif (empty($users)) {
    		return Response::json([
			'ResponseCode'=>false,
			'message'=>"Data does not exists.",
			], 200);	
    	}
    	else{
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"Something went wrong.",
			], 200);	
    	}
    }
    public function getprofile(Request $request){
    	$id=$request->id;
    	$type=$request->type;
    	if ($type==1) {
    		$users['usersdata'] = DB::table('7e_users')->select('*')->where('id',$id)->first();
    		$users['timezonedata']=DB::table('7e_timezones')->get();
    		if ($users) {
				return Response::json([
				'ResponseCode'=>true,
				'message'=>"Your data show sucessfully.",
				'response' => $users,
				], 200);
    		}
    		elseif(empty($users)){
				return Response::json([
				'ResponseCode'=>false,
				'message'=>"Data does not exists.",
				], 200);
    		}
    		else{
				return Response::json([
				'ResponseCode'=>false,
				'message'=>"Something went wrong.",
				], 200);	
    		}
    	}
    	elseif ($type==2) {
    		$users['usersdata'] = DB::table('7e_users')
				->join('7e_professional', '7e_users.id', '=', '7e_professional.prof_id')
				->where('7e_users.id',$id)
				->first();
			$users['timezonedata']=DB::table('7e_timezones')->get();

    		if ($users) {
				return Response::json([
				'ResponseCode'=>true,
				'message'=>"Your data show sucessfully.",
				'response' => $users,
				], 200);
    		}
    		elseif(empty($users)){
				return Response::json([
				'ResponseCode'=>false,
				'message'=>"Data does not exists.",
				], 200);
    		}
    		else{
				return Response::json([
				'ResponseCode'=>false,
				'message'=>"Something went wrong.",
				], 200);	
    		}
    		
    	}
    }
    public function profsignup(Request $request){
    	$email=$request->email;
    	$password=md5($request->password);
    	$type=$request->type;

	    	if ($type==1) {
	    		$data = DB::select( DB::raw("SELECT * FROM 7e_users where email='".$email."' and password='".$password."'  and user_type=2 "));
	    		if (empty($data)) {
					return Response::json([
						'ResponseCode'=>false,
						'message'=>"email or password does not exists.",
						], 200);	
	    		}
	    		else{
	    			$users = DB::table('7e_users')->select('*')->where('id',$data[0]->id)->first();
					return Response::json([
						'ResponseCode'=>true,
						'message'=>"Sucessfully login.",
						'response'=>$users,
						], 200);	
	    		}
	    	}
    	
    }
    public function basicprofile(Request $request){

    	    $name=$request->name;
    		$email=$request->email;
    		$phone=$request->phone;
    		$paypal_id =$request->paypal_id ;
    		$facebook=$request->facebook;
    		$twitter=$request->twitter;
    		$youtube=$request->youtube;
    		$website=$request->website;
    		$prof_id=$request->prof_id;
			$time_zone=$request->time_zone;


			$mind=$request->mind;
    		$body=$request->body;
			$face=$request->face;




    		$userarray=array('name'=>$name,'email'=>$email,'phone'=>$phone,'time_zone'=>$time_zone);

    		if (isset($_FILES['profile_pic'])) {
	            $image = $request->file('profile_pic');
	            $name=str_replace(' ', '_',$_FILES['profile_pic']['name']);
	            $destinationPath = public_path('apiimage/');
	            $request=array('image'=>$image,'name'=>$name,'destinationPath'=>$destinationPath);
	            $abc=$this->file_upload($request['image'],$request['name'],$request['destinationPath']);
	            $userarray['profile_pic']=$abc;
            }
    		$userarray=array_filter($userarray);

    		$detailarray=array('paypal_id'=>$paypal_id,'facebook'=>$facebook,'twitter'=>$twitter,'youtube'=>$youtube, 'website'=>$website,'mind'=>$mind,'body'=>$body,'face'=>$face);
    		$detailarray=array_filter($detailarray);


    		

    		$users = DB::table('7e_users')
				 ->where('7e_users.id',$prof_id)
				->get();

    		$checkemail = DB::select( DB::raw("SELECT * FROM 7e_users where email='".$email."' and id !='".$prof_id."'"));
    		if (!empty($checkemail)) {
    			return Response::json([
				'ResponseCode'=>false,
				'message'=>"Email already exists.",
				], 200);	
				die;
    		}
    		if (empty($users[0]->signup_level)) {
    			$userarray['signup_level']=1;
    		}


    		$uptuser=DB::table('7e_users')
			->where('id', $prof_id)
			->update($userarray);

			if (!empty($mind) || !empty($body) || !empty($face) || !empty($paypal_id) || !empty($facebook) || !empty($website) || !empty($youtube) ) {
				$uptdetail=DB::table('7e_professional')
				->where('prof_id', $prof_id)
				->update($detailarray);
			}


			$users = DB::table('7e_users')
				->join('7e_professional', '7e_users.id', '=', '7e_professional.prof_id')
				 ->where('7e_users.id',$prof_id)
				->get();

				return Response::json([
				'ResponseCode'=>true,
				'message'=>"Your data show sucessfully.",
				'response' => $users,
				], 200);
			// }
			// else{
			// 	return Response::json([
			// 	'ResponseCode'=>false,
			// 	'message'=>"Something went wrong.",
			// 	], 200);	
			// }
    }
    public function addtreatment(Request $request){
    	    $treatment_name=$request->treatment_name;
    		$consulation_fee=$request->consulation_fee;
    		$consulation_detail=$request->consulation_detail;
    		$extra_detail=$request->extra_detail;
    		$prof_id=$request->prof_id;
    		$consultation_time=$request->consultation_time;


    		$question1=$request->question1;
    		$type1=$request->type1;

    		$question2=$request->question2;
    		$type1=$request->type2;

    		$question3=$request->question3;
    		$type3=$request->type3;

    		$question4=$request->question4;
    		$type4=$request->type4;

    		$question5=$request->question5;
    		$type5=$request->type5;
    		$abc=$request->file();
 



    		if(!empty($abc['image1'])){
				$image = $request->file('image1');
				$name=str_replace(' ', '_',$_FILES['image1']['name']);
				$destinationPath = public_path('apiimage/');
				$request=array('image'=>$image,'name'=>$name,'destinationPath'=>$destinationPath);
				$img1=$this->file_upload($request['image'],$request['name'],$request['destinationPath']);
		    }
		    else{
		    	$img1='';
		    }
		    if(!empty($abc['image2'])){
				$image = $abc['image2'];
				$name=str_replace(' ', '_',$_FILES['image2']['name']);
				$destinationPath = public_path('apiimage/');
				$request=array('image'=>$image,'name'=>$name,'destinationPath'=>$destinationPath);
				$img2=$this->file_upload($request['image'],$request['name'],$request['destinationPath']);
		    }
		    else{
		    	$img2='';
		    }
		    if(!empty($abc['image3'])){
				$image = $abc['image3'];
				$name=str_replace(' ', '_',$_FILES['image3']['name']);
				$destinationPath = public_path('apiimage/');
				$request=array('image'=>$image,'name'=>$name,'destinationPath'=>$destinationPath);
				$img3=$this->file_upload($request['image'],$request['name'],$request['destinationPath']);
		    }
		    else{
		    	$img3='';
		    }
		    if(!empty($abc['image4'])){
				$image = $abc['image4'];
				$name=str_replace(' ', '_',$_FILES['image4']['name']);
				$destinationPath = public_path('apiimage/');
				$request=array('image'=>$image,'name'=>$name,'destinationPath'=>$destinationPath);
				$img4=$this->file_upload($request['image'],$request['name'],$request['destinationPath']);
		    }
		    else{
		    	$img4='';
		    }
		    if(!empty($abc['image5'])){
				$image = $abc['image5'];
				$name=str_replace(' ', '_',$_FILES['image5']['name']);
				$destinationPath = public_path('apiimage/');
				$request=array('image'=>$image,'name'=>$name,'destinationPath'=>$destinationPath);
				$img5=$this->file_upload($request['image'],$request['name'],$request['destinationPath']);
		    }
		    else{
		    	$img5='';
		    }

			/*item images serilize start*/
			$seru=array($img1,$img2,$img3,$img4,$img5);
			$result_image=serialize($seru);
			/*item images serialize end*/
		    $treatmentdata = DB::table('7e_treatment')->insertGetId(
				['prof_id' =>$prof_id,'treatment_name' =>$treatment_name,'consulation_fee' =>$consulation_fee,'consulation_detail'=>$consulation_detail,'consultation_time'=>$consultation_time,'extra_detail'=>$extra_detail,'result_image'=>$result_image]
			);

			if (!empty($question1) && !empty($type1)) {
				$questiondata = DB::table('7e_question')->insertGetId(
				['prof_id' =>$prof_id,'treatment_id' =>$treatmentdata,'question' =>$question1,'type'=>$type1]
				);
			}
			if (!empty($question2) && !empty($type2)) {
				$questiondata = DB::table('7e_question')->insertGetId(
				['prof_id' =>$prof_id,'treatment_id' =>$treatmentdata,'question' =>$question2,'type'=>$type2]
				);
			}
			if (!empty($question3) && !empty($type3)) {
				$questiondata = DB::table('7e_question')->insertGetId(
				['prof_id' =>$prof_id,'treatment_id' =>$treatmentdata,'question' =>$question3,'type'=>$type3]
				);
			}
			if (!empty($question4) && !empty($type4)) {
				$questiondata = DB::table('7e_question')->insertGetId(
				['prof_id' =>$prof_id,'treatment_id' =>$treatmentdata,'question' =>$question4,'type'=>$type4]
				);
			}
			if (!empty($question5) && !empty($type5)) {
				$questiondata = DB::table('7e_question')->insertGetId(
				['prof_id' =>$prof_id,'treatment_id' =>$treatmentdata,'question' =>$question5,'type'=>$type5]
				);
			}

			$users = DB::table('7e_users')
				->where('7e_users.id',$prof_id)
					->get();
			if (($users[0]->signup_level==1)) {
				$userarray['signup_level']=2;
				$uptuser=DB::table('7e_users')
				->where('id', $prof_id)
				->update($userarray);
			}
			if ($treatmentdata) {
				$users = DB::table('7e_users')
				->join('7e_professional', '7e_users.id', '=', '7e_professional.prof_id')
				->where('7e_users.id',$prof_id)
				->get();
				return Response::json([
				'ResponseCode'=>true,
				'message'=>"Your data show sucessfully.",
				'response' => $users,
				], 200);
			}
			else{
				return Response::json([
				'ResponseCode'=>false,
				'message'=>"Something went wrong.",
				], 200);	
			}
    }

    public function addmodeconsulation(Request $request){
    	$prof_id=$request->prof_id;
    	$text_chat=$request->text_chat;
    	$audio_call =$request->audio_call ;
    	$video_call =$request->video_call ;
    	$data=array('text_chat'=>$text_chat,'audio_call'=>$audio_call,'video_call'=>$video_call);
    	$data=array_filter($data);

    	$users = DB::table('7e_users')
				 ->where('7e_users.id',$prof_id)
				  ->get();

		if (($users[0]->signup_level==2)) {
    			$userarray['signup_level']=3;
				$uptuser=DB::table('7e_users')
					->where('id', $prof_id)
					->update($userarray);
    	}
    	$uptuser=DB::table('7e_professional')
			->where('prof_id', $prof_id)
			->update($data);
		// if ($uptuser) {
			$users = DB::table('7e_users')
			->join('7e_professional', '7e_users.id', '=', '7e_professional.prof_id')
			->where('7e_users.id',$prof_id)
			->get();
			return Response::json([
				'ResponseCode'=>true,
				'message'=>"Your mode of consulation updated sucessfully.",
				'response'=>$users
				], 200);
		// }
		// else{
		// 	return Response::json([
		// 		'ResponseCode'=>false,
		// 		'message'=>"Something went wrong.",
		// 		], 200);
		// }
    }
    public function setschedule(Request $request){
    	$prof_id=$request->prof_id;
    	$date=$request->date;
    	$from_time=$request->from_time;
    	$to_time=$request->to_time;
    	$date=explode(',', $date);



    	$users = DB::table('7e_users')
				->where('7e_users.id',$prof_id)
				  ->get();

    	if (($users[0]->signup_level==3)) {
    			$userarray['signup_level']=4;
				$uptuser=DB::table('7e_users')
					->where('id', $prof_id)
					->update($userarray);
    	}
    	foreach ($date as $key => $value) {
	    	$scheduledata = DB::table('7e_schedule')->insertGetId(
					['prof_id' =>$prof_id,'date' =>$value,'from_time' =>$from_time,'to_time'=>$to_time]
				);    		
    	}
    	$users = DB::table('7e_schedule')
			->where('7e_schedule.prof_id',$prof_id)
			->get();

    	if ($scheduledata) {
    		return Response::json([
				'ResponseCode'=>true,
				'message'=>"Your sechdule inserted sucessfully.",
				'response'=>$users
				], 200);
    	}
    	else{
    		return Response::json([
				'ResponseCode'=>false,
				'message'=>"Something went wrong.",
				], 200);
    	}

    }
    public function expertstopics(Request $request){
    	$prof_id=$request->prof_id;
    	$offset=$request->offset;
    	$treatmentdata = DB::table('7e_treatment')
				 ->where('7e_treatment.prof_id',$prof_id)
				  ->get();
		$data=array();
		foreach ($treatmentdata as $key => $value) {
			$questiondata = DB::table('7e_question')
				  ->where('treatment_id',$value->id)
				   ->get();
			$treatmentdata[$key]->question=$questiondata;
			$data= unserialize($value->result_image); 
			$data=array_filter($data);
			$data=array_values($data);

			if (!empty($data)) {
				$treatmentdata[$key]->result_images=$data;

			}
			else{
				$treatmentdata[$key]->result_images=array();
			}
		}
		$count=count($treatmentdata);
		$treatmentdata = array_slice($treatmentdata, $offset, 10 ); 
		if (!empty($treatmentdata)) {
			return Response::json([
			'ResponseCode'=>true,
			'message'=>"Your data show sucessfully.",
			'response' => $treatmentdata,
			'count'=>$count
			], 200);
    	}
    	elseif (empty($treatmentdata)) {
    		return Response::json([
			'ResponseCode'=>false,
			'message'=>"Data does not exists.",
			], 200);	
    	}
    	else{
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"Something went wrong.",
			], 200);	
    	}
    }
    public function experttopicdetail(Request $request){
    	$topicid=$request->topicid;
    	$treatmentdata = DB::table('7e_treatment')
				  ->where('7e_treatment.id',$topicid)
				   ->first();
		$resultimage=unserialize($treatmentdata->result_image);
		$treatmentdata->result_images=$resultimage;

			$questiondata = DB::table('7e_question')
				  ->where('treatment_id',$topicid)
				   ->get();
			$treatmentdata->question=$questiondata;
		if (!empty($treatmentdata)) {
			return Response::json([
			'ResponseCode'=>true,
			'message'=>"Your data show sucessfully.",
			'response' => $treatmentdata,
			], 200);
    	}
    	elseif (empty($treatmentdata)) {
    		return Response::json([
			'ResponseCode'=>false,
			'message'=>"Data does not exists.",
			], 200);	
    	}
    	else{
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"Something went wrong.",
			], 200);	
    	}

    }
    public function deletetreatment(Request $request){
    	$treatment_id=$request->treatment_id;

		$appointmentdata=DB::select( DB::raw("SELECT * FROM 7e_appointments where treatment_id='".$treatment_id."' and is_pending=1 and is_accepted=1 and is_completed=0 and is_cancelled=0 "));
		if ($appointmentdata) {
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"You can not delete the treatment.",
			], 200);
			exit;
		}
    	DB::table('7e_treatment')->where('id', $treatment_id)->delete();
    	DB::table('7e_question')->where('treatment_id', $treatment_id)->delete();
    	return Response::json([
			'ResponseCode'=>true,
			'message'=>"your treatment deleted sucessfully.",
			], 200);
    }

    public function deletequestion(Request $request){
    	$questionid=$request->questionid;
    	DB::table('7e_question')->where('id', $questionid)->delete();
    	return Response::json([
			'ResponseCode'=>true,
			'message'=>"your question deleted sucessfully.",
			], 200);
    }

    public function deleteschedule(Request $request){
    	$scheduleid=$request->scheduleid;
    	DB::table('7e_schedule')->where('id', $scheduleid)->delete();
    	return Response::json([
			'ResponseCode'=>true,
			'message'=>"your schedule deleted sucessfully.",
			], 200);
    }

    public function schedulelisting(Request $request){
    	$prof_id=$request->prof_id;
    	$date=$request->date;
    	$offset=$request->offset;
    	$type=$request->type;

	    	if ($type==1) {
		    	$scheduledata = DB::table('7e_schedule')
						  ->where('prof_id',$prof_id)
						  ->where('date',$date)
						   ->get();
				$count=count($scheduledata);
		        $scheduledata = array_slice( $scheduledata, $offset, 10 ); 
				if (!empty($scheduledata)) {
					return Response::json([
					'ResponseCode'=>true,
					'message'=>"Your data show sucessfully.",
					'response' => $scheduledata,
					'count'=>$count
					], 200);
		    	}
		    	elseif (empty($scheduledata)) {
		    		return Response::json([
					'ResponseCode'=>true,
						'response' => $scheduledata,
						'count'=>"0",
					'message'=>"Data does not exists.",
					], 200);	
		    	}
		    	else{
					return Response::json([
					'ResponseCode'=>false,
					'message'=>"Something went wrong.",
					], 200);	
		    	}
			}
			elseif($type==2){
				$scheduledata = DB::table('7e_schedule')
				->where('prof_id',$prof_id)
				->where('date',$date)
				->get();

				$count=count($scheduledata);
				if (!empty($scheduledata)) {
					return Response::json([
					'ResponseCode'=>true,
					'message'=>"Your data show sucessfully.",
					'response' => $scheduledata,
					'count'=>$count
					], 200);
				}
				elseif (empty($scheduledata)) {
					return Response::json([
					'ResponseCode'=>true,
					'response' => $scheduledata,
					'count'=>"0",
					'message'=>"Data does not exists.",
					], 200);	
				}
				else{
					return Response::json([
					'ResponseCode'=>false,
					'message'=>"Something went wrong.",
					], 200);	
				}
			}
    }

    public function editschedule(Request $request){
    	$scheduleid=$request->scheduleid;
    	
    	$from_time=$request->from_time;
    	$to_time=$request->to_time;

    	$data=array('from_time'=>$from_time,'to_time'=>$to_time);

		$scheduledata=DB::table('7e_schedule')
		->where('id', $scheduleid)
		->update($data);
		if ($scheduledata) {
			return Response::json([
			'ResponseCode'=>true,
			'message'=>"your schedule updated sucessfully.",
			], 200);	
		}
		else{
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"This time already exists.",
			], 200);
		}



    }

    public function edittreatment(Request $request){

    	$data=array('treatment_name'=>$request->treatment_name,
    		'consulation_fee'=>$request->consulation_fee,
    		'consulation_detail'=>$request->consulation_detail,
    		'extra_detail'=>$request->extra_detail,
    		'prof_id'=>$request->prof_id,
    		'consultation_time'=>$request->consultation_time,
    		'is_approved'=>0,
    		'is_rejected'=>0
    	);

    	    $treatment_id=$request->treatment_id;
    	    $data=array_filter($data);

    	    $appointmentdata=DB::select( DB::raw("SELECT * FROM 7e_appointments where treatment_id='".$treatment_id."' and is_pending=1 and is_accepted=1 and is_completed=0 and is_cancelled=0 "));
    	    if ($appointmentdata) {
    	    	return Response::json([
				'ResponseCode'=>false,
				'message'=>"You can not edit the treatment.",
				], 200);
				exit;
    	    	
    	    }

    		if(!empty($_FILES['image1'])){
				$image = $request->file('image1');
				$name=str_replace(' ', '_',$_FILES['image1']['name']);
				$destinationPath = public_path('apiimage/');
				$request=array('image'=>$image,'name'=>$name,'destinationPath'=>$destinationPath);
				$img1=$this->file_upload($request['image'],$request['name'],$request['destinationPath']);
		    }

		    if(!empty($_FILES['image2'])){
				$image = $request->file('image2');
				$name=str_replace(' ', '_',$_FILES['image2']['name']);
				$destinationPath = public_path('apiimage/');
				$request=array('image'=>$image,'name'=>$name,'destinationPath'=>$destinationPath);
				$img2=$this->file_upload($request['image'],$request['name'],$request['destinationPath']);
		    }
	
		    if(!empty($_FILES['image3'])){
				$image = $request->file('image3');
				$name=str_replace(' ', '_',$_FILES['image3']['name']);
				$destinationPath = public_path('apiimage/');
				$request=array('image'=>$image,'name'=>$name,'destinationPath'=>$destinationPath);
				$img3=$this->file_upload($request['image'],$request['name'],$request['destinationPath']);
		    }
	
		    if(!empty($_FILES['image4'])){
				$image = $request->file('image4');
				$name=str_replace(' ', '_',$_FILES['image4']['name']);
				$destinationPath = public_path('apiimage/');
				$request=array('image'=>$image,'name'=>$name,'destinationPath'=>$destinationPath);
				$img4=$this->file_upload($request['image'],$request['name'],$request['destinationPath']);
		    }

		    if(!empty($_FILES['image5'])){
				$image = $request->file('image5');
				$name=str_replace(' ', '_',$_FILES['image5']['name']);
				$destinationPath = public_path('apiimage/');
				$request=array('image'=>$image,'name'=>$name,'destinationPath'=>$destinationPath);
				$img5=$this->file_upload($request['image'],$request['name'],$request['destinationPath']);
		    }
	
		    if (!empty($_FILES['image1']) || !empty($_FILES['image2']) || !empty($_FILES['image3']) || !empty($_FILES['image4']) || !empty($_FILES['image5'])) {
		    	if (empty($img1)) {
		    		$img1="";			    
				}
				if (empty($img2)) {
					$img2="";		    	
				}	
				if (empty($img3)) {
		    		$img3="";			    
				}
				if (empty($img4)) {
					$img4="";		    	
				}	
				if (empty($img5)) {
					$img5="";		    	
				}		    	
				/*item images serilize start*/
				$seru=array($img1,$img2,$img3,$img4,$img5);
				$data['result_image']=serialize($seru);
		    }
			


			$treatmentdata=DB::table('7e_treatment')
					->where('id', $treatment_id)
					->update($data);
			if ($treatmentdata) {
				$treatmentdata = DB::table('7e_treatment')
				  ->where('7e_treatment.id',$treatment_id)
				   ->first();
				return Response::json([
				'ResponseCode'=>true,
				'message'=>"Your data updated Succesfully.",
				'Response'=>$treatmentdata
				], 200);	
				
			}
	    	else{
				return Response::json([
				'ResponseCode'=>false,
				'message'=>"Something went wrong.",
				], 200);	
	    	}
    }
    public function questionview(Request $request){
    	$treatmentid=$request->treatment_id;

		$treatmentdata = DB::table('7e_question')
				  ->where('treatment_id',$treatmentid)
				   ->get();
		if (!empty($treatmentdata)) {
			return Response::json([
			'ResponseCode'=>true,
			'message'=>"Your data show sucessfully.",
			'response' => $treatmentdata,
			], 200);
    	}
    	elseif (empty($treatmentdata)) {
    		return Response::json([
			'ResponseCode'=>false,
			'message'=>"Data does not exists.",
			], 200);	
    	}
    	else{
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"Something went wrong.",
			], 200);	
    	}

    }
    public function editquestion(Request $request){
    	$question_id=$request->question_id;
    	$question=$request->question;
    	$type=$request->type;

    	$data=array('question'=>$question,'type'=>$type);
    	$data=array_filter($data);
		$questiondata=DB::table('7e_question')
				->where('id', $question_id)
				->update($data);
		if ($questiondata) {
			$questiondata = DB::table('7e_question')
			  ->where('7e_question.id',$question_id)
			   ->first();
			return Response::json([
			'ResponseCode'=>true,
			'message'=>"Your data updated Succesfully.",
			'Response'=>$questiondata
			], 200);	
			
		}
		else{
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"Something went wrong.",
			], 200);	
		}

    }
    public function addquestion(Request $request){

    	$treatment_id=$request->treatment_id;
    	$prof_id=$request->prof_id;
    	$question=$request->question;
    	$type=$request->type;

		$questiondata = DB::table('7e_question')->insertGetId(
		['prof_id' =>$prof_id,'treatment_id' =>$treatment_id,'question' =>$question,'type' =>$type]
		);
		if (!empty($questiondata)) {
			$questiondata = DB::table('7e_question')
			  ->where('7e_question.id',$questiondata)
			   ->first();
			return Response::json([
			'ResponseCode'=>true,
			'message'=>"Your data shows Succesfully.",
			'Response'=>$questiondata
			], 200);	
			
		}
		else{
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"Something went wrong.",
			], 200);	
		}



    }

    public function searchexperts(Request $request){
    	$issue=$request->issue;
    	$issue=explode(',',$issue);


    	$timezone=$request->timezone;
    	$offset=$request->offset;
    	$availability=$request->availability;
    	$availability=explode(',',$availability);
    	$modeofconsultaion=$request->modeofconsultaion;
    	$modeofconsultaion=explode(',',$modeofconsultaion);
    	$expertise=$request->expertise;
    	$expertise=explode(',',$expertise);
    	if (!empty($expertise)) {
	    	foreach ($expertise as $key => $value){
				if ($value==1) {
					$expert_type[0]=1;
				}
				if ($value==2) {
					$expert_type[1]=2;
				}
				if ($value==3) {
					$expert_type[2]=3;
				}
	    	}
    	}

    	if (!empty($availability)) {
	    	foreach ($availability as $key => $value){
				if ($value==1) {
					$available_type[0]=1;
				}
				if ($value==2) {
					$available_type[1]=2;
				}
	    	}
    	}

    	if (!empty($modeofconsultaion)) {
	    	foreach ($modeofconsultaion as $key => $value){
				if ($value==1) {
					$consult_type[0]=1;
				}
				if ($value==2) {
					$consult_type[1]=2;
				}
	    	}
    	}

		$result = DB::table('7e_professional')
			->join('7e_users', '7e_professional.prof_id', '=', '7e_users.id');

		if (!empty($issue)){
			 $result->join('7e_treatment', '7e_professional.prof_id', '=', '7e_treatment.prof_id')
			 		->where('7e_treatment.is_approved',1)
			 		->where('7e_treatment.is_rejected',0);
			 
				 foreach ($issue as $key => $value) {
				 	$result->orwhere('7e_treatment.treatment_name', 'LIKE', '%'.$value.'%');
				 }
		}

		if (!empty($timezone)) {
			$result->where('7e_users.time_zone',$timezone);
		}

		if ((!empty($available_type[0]) && $available_type[0]==1 ) && (empty($available_type[1])) ) {
			 $result->where('7e_professional.status',1);
		}
	

		if(!empty($expert_type[0]) && $expert_type[0]==1){
			$result->where('7e_professional.mind',1);
		}

		if(!empty($expert_type[1]) && $expert_type[1]==2){
			$result->where('7e_professional.body',1);
		}
		if(!empty($expert_type[2]) && $expert_type[2]==3){
			$result->where('7e_professional.face',1);
		}


		if(!empty($consult_type[0]) && $consult_type[0]==1){
			$result->where('7e_professional.audio_call',1);
		}
		if(!empty($consult_type[1]) && $consult_type[1]==2){
			$result->where('7e_professional.video_call',1);
		}

		$result->groupBy('7e_professional.prof_id');
		$data=$result->get();


        foreach ($data as $key => $value) {
        	$abc=  \DB::table('7e_rating')
            ->where('to_id',$value->prof_id)
            ->avg('rating');
            if (!empty($abc)) {
            	$data[$key]->avgrating=$abc;
            }
            else{
                $data[$key]->avgrating="0";	
            }

        }

		$count=count($data);
        $data = array_slice( $data, $offset, 10 ); 
	

		if (!empty($data)) {
			return Response::json([
			'ResponseCode'=>true,
			'message'=>"Your data shows Succesfully.",
			'Response'=>$data,
			'count'=>$count
			], 200);
		}
    	elseif (empty($data)) {
    		return Response::json([
			'ResponseCode'=>false,
			'message'=>"Data does not exists.",
			], 200);	
    	}
    	else{
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"Something went wrong.",
			], 200);	
    	}
		

    }
    public function setstatus(Request $request){
    	$prof_id=$request->prof_id;
    	$status=$request->status;
    	$data=array('status'=>$status);
  		$updateddata=DB::table('7e_professional')
		->where('prof_id', $prof_id)
		->update($data);
		if ($updateddata) {
			return Response::json([
			'ResponseCode'=>true,
			'message'=>"Your status updated Succesfully."
			], 200);
		}
		else{
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"Something went wrong.",
			], 200);	
		}
    }


    public function setpushstatus(Request $request){
    	$user_id=$request->user_id;
    	$status=$request->status;
    	$data=array('push_notification'=>$status);
  		$updateddata=DB::table('7e_users')
		->where('id', $user_id)
		->update($data);
		if ($updateddata) {
			return Response::json([
			'ResponseCode'=>true,
			'message'=>"Your status updated Succesfully."
			], 200);
		}
		else{
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"Something went wrong.",
			], 200);	
		}
    }

    public function upcomingapointment(Request $request){
    	$user_id=$request->user_id;
    	$type=$request->type;
    	$offset=$request->offset;
    	if ($type==1) {
    		$results['timezonedata']=DB::select( DB::raw("SELECT * FROM 7e_timezones "));
    		$results['appointmentdata']=DB::select( DB::raw("SELECT 7e_appointments.*,7e_users.id as userid,7e_users.name, 7e_users.email,7e_users.profile_pic FROM 7e_appointments join 7e_users on 7e_users.id=7e_appointments.prof_id where user_id='".$user_id."' and is_pending=1  and is_completed=0 and is_cancelled=0 "));

				$count=count($results['appointmentdata']);
				$results['appointmentdata'] = array_slice( $results['appointmentdata'], $offset, 10 ); 

				return Response::json([
				'ResponseCode'=>true,
				'message'=>"Your data shows Succesfully.",
				'Response'=>$results,
				'count'=>$count
				], 200);
    	}
    	elseif($type==2){
			$results['timezonedata']=DB::select( DB::raw("SELECT * FROM 7e_timezones "));
    		$results['appointmentdata']=DB::select( DB::raw("SELECT 7e_appointments.*,7e_users.id as userid,7e_users.name, 7e_users.email,7e_users.profile_pic FROM 7e_appointments join 7e_users on 7e_users.id=7e_appointments.prof_id where user_id='".$user_id."' and is_pending=1 and is_accepted=1 and is_completed=1 and is_cancelled=0 "));

    			$count=count($results['appointmentdata']);
				$results['appointmentdata'] = array_slice( $results['appointmentdata'], $offset, 10 ); 

    			return Response::json([
				'ResponseCode'=>true,
				'message'=>"Your data shows Succesfully.",
				'Response'=>$results,
				'count'=>$count
				], 200);
    	}
    	elseif($type==3){
			$results['timezonedata']=DB::select( DB::raw("SELECT * FROM 7e_timezones "));
    		$results['appointmentdata']=DB::select( DB::raw("SELECT 7e_appointments.*,7e_users.id as userid,7e_users.name, 7e_users.email,7e_users.profile_pic FROM 7e_appointments join 7e_users on 7e_users.id=7e_appointments.prof_id where user_id='".$user_id."' and  is_cancelled=1 "));

    		    $count=count($results['appointmentdata']);
				$results['appointmentdata'] = array_slice( $results['appointmentdata'], $offset, 10 ); 

    			return Response::json([
				'ResponseCode'=>true,
				'message'=>"Your data shows Succesfully.",
				'Response'=>$results,
				'count'=>$count
				], 200);
    	}
    }

    public function addappointment(Request $request){
    	$user_id=$request->user_id;
    	$prof_id=$request->prof_id;
    	$treatment_id=$request->treatmentid;
    	$booking_date=$request->booking_date;
    	$booking_time=$request->booking_time;
    	$consultation_mode=$request->consultation_mode;
    	$consultation_time=$request->consultation_time;
    	$consultation_fee=$request->consultation_fee;
    	$questions=$request->questions;
    	$card_id=$request->card_id;
    	$type=$request->type;

    	if ($type==1) {
			$endTime = strtotime("+15 minutes", strtotime($booking_time));
			$newtime= date('H:i:s', $endTime);
    	}
    	elseif($type==2){
			$booking_time= date('H:i:s');
			$endTime = strtotime("+15 minutes", strtotime($booking_time));
			$newtime= date('H:i:s', $endTime);
			$booking_date=date('Y-m-d');
    	}


		$treatmentdata = DB::table('7e_treatment')
		->where('id',$treatment_id)
		 ->first();

		$timedata = DB::table('7e_setting')
			->first();
		$time=$timedata->buffer_time;

		$results1=DB::select( DB::raw("SELECT * from 7e_schedule where prof_id='".$prof_id."' and  date='".$booking_date."' and (('".$booking_time."' between from_time and to_time) AND ('".$newtime."' between from_time and to_time))    "));
		if (empty($results1)) {
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"No schedule available.",
			], 200);
			die;
		}
		$results=DB::select( DB::raw("SELECT *  FROM `7e_appointments`  WHERE prof_id='".$prof_id."' and  (is_pending=1 and is_accepted=1) AND is_completed!=1 AND is_cancelled !=1  AND (CONCAT('".$booking_date."', ' ', '".$booking_time."') BETWEEN CONCAT(booking_date, ' ', booking_time) AND DATE_ADD(CONCAT(booking_date, ' ', booking_time) , INTERVAL ('".round($treatmentdata->consultation_time)."'+15+ (('".$time."')*2)) MINUTE) OR DATE_ADD(CONCAT('".$booking_date."', ' ', '".$booking_time."'), INTERVAL('".round($treatmentdata->consultation_time)."' +15+ (('".$time."')*2)) MINUTE) BETWEEN CONCAT(booking_date, ' ', booking_time) AND DATE_ADD(CONCAT(booking_date, ' ', booking_time) , INTERVAL ('".round($treatmentdata->consultation_time)."' +15+ (('".$time."')*2)) MINUTE))"));

		if (!empty($results)) {
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"Already booked for that booking date and time.",
			], 200);
			die;
		}

		// $carddata=DB::select( DB::raw("SELECT *  FROM `7e_cards`  WHERE id='".$card_id."'  "));
		// $carddata=DB::select( DB::raw("SELECT *  FROM `7e_stripedetails`  WHERE user_id='".$carddata[0]->user_id."' and card_number='".$carddata[0]->card_number."'  "));
		// print_r($carddata);die;

		// $stripe = Stripe::make('sk_test_AV45bRuYmLu0nLFvLggzTQjF', '2016-07-06');
  //       $charge =$stripe->Charges()->create(array(
  //           'customer' => $carddata[0]->cus_id,
  //           'amount'   => $consultation_fee,
  //           'currency' => 'USD'
  //       ));
  //       $txn_id=$charge['id'];

        // if ($charge['status']=='succeeded') {

        	// $zoomdata=$this->zoommeeting($user_id);

        	// print_r($zoomdata);die;
	        $appointmentdata = DB::table('7e_appointments')->insertGetId(
				['user_id' =>$user_id,'prof_id' =>$prof_id,'treatment_id' =>$treatment_id,'is_pending'=>1,'booking_time' =>$booking_time,'booking_date' =>$booking_date,'consultation_mode' =>$consultation_mode,'consultation_time' =>$consultation_time,'consultation_fee' =>$consultation_fee,'card_id' =>$card_id]
			);
			// $calldata = DB::table('7e_calls')->insertGetId(
			// 	['user_id' =>$user_id,'appointment_id' =>$appointmentdata,'start_url' =>$zoomdata->start_url,'join_url'=>$zoomdata->join_url]
			// );
			// $txndata = DB::table('7e_transaction')->insertGetId(
			// 	['user_id' =>$user_id,'appointment_id' =>$appointmentdata,'type'=>1,'txn_id' =>$txn_id,'amount'=>$consultation_fee,'prof_id'=>$prof_id]
			// );
			if (!empty($questions)) {
				$question=json_decode($questions);
				foreach ($question as $key => $value) {
					$questiondata = DB::table('7e_answer')->insertGetId(
					['appointment_id' =>$appointmentdata,'treatment_id' =>$value->treatment_id,'ques_id' =>$value->ques_id,'type'=>$value->type,'user_id'=>$user_id,'answer'=>$value->answer]
					);
				}
			}

			/*push start*/
			$logindata = DB::table('7e_login')
				->where('user_id',$prof_id)
				->where('status',1)
				->get();
			$userdata = DB::table('7e_users')
				->select('profile_pic')
				->where('id',$prof_id)
				->first();
			foreach ($logindata as $key => $value) {
				$pushdata['pushdata']=array('token'=>$value->token_id,'message'=>'You have new appointment.','action'=>'new_appointment','appointmentid'=>$appointmentdata,'profile_pic'=>$userdata->profile_pic);

				/*device id 0 for android*/
				if ($value->device_id==0) {
					$this->androidpush($pushdata['pushdata']);	
				}
				/*device id 0 for ios*/	
				if ($value->device_id==1) {
					// $this->iospush($pushdata['pushdata']);	
				}
			}
			// die;
			/*push end*/
			if ($appointmentdata) {
				return Response::json([
				'ResponseCode'=>true,
				'message'=>"Your appointment scheduled Succesfully."
				], 200);
			}
			else{
				return Response::json([
				'ResponseCode'=>false,
				'message'=>"Something went wrong.",
				], 200);
			}	
        // }
    //     else{
    //     	return Response::json([
				// 'ResponseCode'=>false,
				// 'message'=>"Payment failed.",
				// ], 200);
    //     }
    }

    public function checkavailability(Request $request){
    	$prof_id=$request->prof_id;
    	$booking_date=$request->booking_date;
	   	$booking_time=$request->booking_time;
	   	$type=$request->type;

	   	if ($type==1) {
			$endTime = strtotime("+15 minutes", strtotime($booking_time));
			$newtime= date('H:i:s', $endTime);
    	}
    	elseif($type==2){
			$booking_time= date('H:i:s');
			$endTime = strtotime("+15 minutes", strtotime($booking_time));
			$newtime= date('H:i:s', $endTime);
			$booking_date=date('Y-m-d');
    	}


		$timedata = DB::table('7e_setting')
					->first();
		$time=$timedata->buffer_time;

		$termsdata=DB::table('7e_setting')->select('term_condition')->first();

		$results1=DB::select( DB::raw("SELECT * from 7e_schedule where prof_id='".$prof_id."' and  date='".$booking_date."' and (('".$booking_time."' between from_time and to_time) AND ('".$newtime."' between from_time and to_time))    "));
		if (empty($results1)) {
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"No schedule available.",
			'termsdata'=>$termsdata
			], 200);
			die;
		}

		$results=DB::select( DB::raw("SELECT *  FROM `7e_appointments` join 7e_treatment on 7e_treatment.id=7e_appointments.treatment_id  WHERE 7e_appointments.prof_id='".$prof_id."' and  (is_pending=1 and is_accepted=1) and is_completed=0 and is_cancelled=0  and CONCAT('".$booking_date."', ' ', '".$booking_time."') BETWEEN CONCAT(booking_date, ' ', booking_time) AND DATE_ADD(CONCAT(booking_date, ' ', booking_time) , INTERVAL ('".round('consultation_time')."' + (('".$time."')*2)) MINUTE) OR DATE_ADD(CONCAT('".$booking_date."', ' ', '".$booking_time."'), INTERVAL('".round('consultation_time')."' + (('".$time."')*2)) MINUTE) BETWEEN CONCAT(booking_date, ' ', booking_time) AND DATE_ADD(CONCAT(booking_date, ' ', booking_time) , INTERVAL ('".round('consultation_time')."' + (('".$time."')*2)) MINUTE)"));


		if (!empty($results)) {
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"Already booked for that booking date and time.",
			'termsdata'=>$termsdata
			], 200);
			die;
		}
		else{
			return Response::json([
				'ResponseCode'=>true,
				'message'=>"Professional free for that time.",
				'termsdata'=>$termsdata
				], 200);
				die;
		}
    }

    public function profdetail(Request $request){
    	$prof_id=$request->prof_id;
    	$treatmentid=$request->treatmentid;
    	$results=DB::select( DB::raw("SELECT * from 7e_users join 7e_professional on 7e_users.id=7e_professional.prof_id    where 7e_users.id='".$prof_id."' "));
    	$treatmentdata=DB::select( DB::raw("SELECT * from 7e_treatment    where id='".$treatmentid."' "));
    	$questiondata=DB::select( DB::raw("SELECT * from 7e_question    where treatment_id='".$treatmentid."' "));
    	if (!empty($results)) {
    		return Response::json([
			'ResponseCode'=>false,
			'message'=>"Your data shows successfully.",
			'response'=>$results,
			'treatmentdata'=>$treatmentdata,
			'questiondata'=>$questiondata
			], 200);
    	}
    	else{
			return Response::json([
			'ResponseCode'=>false,
			'message'=>"Something went wrong."
			], 200);	
    	}
    }

    public function notificationdetail(Request $request){
    	$user_id=$request->user_id;
    	$results=DB::select( DB::raw("SELECT * from 7e_notification join 7e_users on 7e_users.id=7e_notification.prof_id    where 7e_notification.user_id='".$user_id."' "));
    	if (!empty($results)) {
    		 return Response::json([
			'ResponseCode'=>true,
			'message'=>"Your data shows successfully.",
			'response'=>$results,
			], 200);
    	}
    	else{
    		return Response::json([
			'ResponseCode'=>false,
			'message'=>"No data exists in table."
			], 200);
    	}
    }



    public function appointmentaction(Request $request){
    	$appointment_id=$request->appointment_id;
    	$prof_id=$request->prof_id;
    	$type=$request->type;



    	$appointmentdata = DB::table('7e_appointments')
			->where('id',$appointment_id)
			 ->first();

		$settingdata = DB::table('7e_setting')
			->where('id',1)
			 ->first();

    	if ($type==1) {
    		if ($appointmentdata->is_pending==1 && $appointmentdata->is_accepted==0 && $appointmentdata->is_completed==0 && $appointmentdata->is_cancelled==0  ) {
	    		$data=array('is_accepted'=>1);


				$carddata=DB::select( DB::raw("SELECT *  FROM `7e_cards`  WHERE id='".$appointmentdata->card_id."'  "));
				$carddata=DB::select( DB::raw("SELECT *  FROM `7e_stripedetails`  WHERE user_id='".$carddata[0]->user_id."' and card_number='".$carddata[0]->card_number."'  "));


				$stripe = Stripe::make('sk_test_AV45bRuYmLu0nLFvLggzTQjF', '2016-07-06');
				      $charge =$stripe->Charges()->create(array(
				          'customer' => $carddata[0]->cus_id,
				          'amount'   => ($appointmentdata->consultation_fee*100),
				          'currency' => 'USD'
				      ));
				      $txn_id=$charge['id'];

				if ($charge['status']=='succeeded') {


					$profwallet=$this->getprofwallet($appointmentdata->prof_id);
					$payamount=$profwallet[0]->unpaid_amount;
					// print_r($profwallet);die;
					/*push start*/
					$logindata = DB::table('7e_login')
					->where('user_id',$appointmentdata->user_id)
					->where('status',1)
					->get();
					$userdata = DB::table('7e_users')
					->select('profile_pic')
					->where('id',$appointmentdata->user_id)
					->first();
					foreach ($logindata as $key => $value) {
						$pushdata['pushdata']=array('token'=>$value->token_id,'message'=>'Your appointment has been accepted by professional.','action'=>'accepted_appointment','appointmentid'=>$appointment_id,'profile_pic'=>$userdata->profile_pic);

						/*device id 0 for android*/
						if ($value->device_id==0 && !empty($value->token_id) ) {
							$this->androidpush($pushdata['pushdata']);	
						}
						/*device id 0 for ios*/	
						if ($value->device_id==1 && !empty($value->token_id)){
							// $this->iospush($pushdata['pushdata']);	
						}
					}


					/*push end*/
					$txndata = DB::table('7e_transaction')->insertGetId(
						['user_id' =>$appointmentdata->user_id,'appointment_id' =>$appointment_id,'type'=>1,'txn_id' =>$txn_id,'amount'=>$appointmentdata->consultation_fee,'prof_id'=>$appointmentdata->prof_id]
					);
					$upt1=DB::table('7e_appointments')
					->where('id', $appointment_id)
					->update($data);

			

					return Response::json([
					'ResponseCode'=>true,
					'message'=>"Your appointment accepted succesfully.",
					], 200);
				}
				else{
					    	return Response::json([
					'ResponseCode'=>false,
					'message'=>"Payment failed.",
					], 200);
				}
    		}
    		else{
    			return Response::json([
				'ResponseCode'=>false,
				'message'=>"Your appointment accepted failed.",
				], 200);
    		}
    	}
    	elseif ($type==2) {
    		if ($appointmentdata->is_pending==1 && $appointmentdata->is_accepted==0  && $appointmentdata->is_completed==0 && $appointmentdata->is_cancelled==0  ) {

    			/*push start*/
				$logindata = DB::table('7e_login')
				->where('user_id',$appointmentdata->user_id)
				->where('status',1)
				->get();
				$userdata = DB::table('7e_users')
				->select('profile_pic')
				->where('id',$appointmentdata->user_id)
				->first();


				foreach ($logindata as $key => $value) {
					$pushdata['pushdata']=array('token'=>$value->token_id,'message'=>'Your appointment has been declined by professional.','action'=>'declined_appointment','appointmentid'=>$appointment_id,'profile_pic'=>$userdata->profile_pic);
					
					/*device id 0 for android*/
					if ($value->device_id==0) {
						$this->androidpush($pushdata['pushdata']);	
					}
					/*device id 0 for ios*/	
					if ($value->device_id==1) {
						$this->iospush($pushdata['pushdata']);	
					}
				}
				/*push end*/
	    		$data=array('is_cancelled'=>1);
				$upt1=DB::table('7e_appointments')
				->where('id', $appointment_id)
				->update($data);
				return Response::json([
				'ResponseCode'=>true,
				'message'=>"Your appointment cancelled succesfully.",
				], 200);
			}
			else{
				return Response::json([
				'ResponseCode'=>false,
				'message'=>"Your appointment cancelled failed.",
				], 200);
			}	
    	}
    }

    public function appointmentdetail(Request $request){
    	$appointmentid=$request->appointment_id;
    	$usertype=$request->usertype;

    	$data['appointmentdata']=DB::table('7e_appointments')->where('id',$appointmentid)->first();
    	$data['questiondata']=DB::table('7e_question')->where('treatment_id',$data['appointmentdata']->treatment_id)->get();
    	foreach ($data['questiondata'] as $key => $value) {	
    		$value1=DB::table('7e_answer')->where('ques_id',$value->id)->where('appointment_id',$appointmentid)->first();
    		if (!empty($value1)) {
    			$data['questiondata'][$key]->answer=$value1->answer;
    		}
    		else{
    			$data['questiondata'][$key]->answer="";	
    		}
    	}

    	$data['treatmentdata']=DB::table('7e_treatment')->where('id',$data['appointmentdata']->treatment_id)->get();

    	if ($usertype==1) {
    		$data['userdata']=DB::table('7e_users')->where('id',$data['appointmentdata']->user_id)->first();
    	}
    	if ($usertype==2) {
    		$data['userdata']=DB::table('7e_users')->where('id',$data['appointmentdata']->prof_id)->first();
    		$data['paymentdata']=DB::table('7e_transaction')->where('appointment_id',$data['appointmentdata']->id)->first();
    		if (empty($data['paymentdata'])) {
    			$data['paymentdata']="";
    		}
    		$data['profdata']=DB::table('7e_professional')->where('prof_id',$data['appointmentdata']->prof_id)->first();
    		$abc= DB::table('7e_rating')
            	->where('to_id',$data['appointmentdata']->prof_id)
            		->avg('rating');
            if (!empty($abc)) {
            	$data['profdata']->avgrating=$abc;
            }
            else{
                $data['profdata']->avgrating="0";	
            }


    	}
    	
    	if (!empty($data)) {
    		 return Response::json([
			'ResponseCode'=>true,
			'message'=>"Your data shows successfully.",
			'response'=>$data,
			], 200);
    	}
    	else{
    		return Response::json([
			'ResponseCode'=>false,
			'message'=>"No data exists in table."
			], 200);
    	}
    }

    public function appointmentlistingprof(Request $request){
    	$prof_id=$request->prof_id;
    	$type=$request->type;
    	$offset=$request->offset;
    	$booking_date=$request->booking_date;

    	if ($type==1) {
    		$results['appointmentdata']=DB::select( DB::raw("SELECT 7e_appointments.*,7e_users.id as userid,7e_users.name, 7e_users.email,7e_users.profile_pic FROM 7e_appointments join 7e_users on 7e_users.id=7e_appointments.user_id where prof_id='".$prof_id."' and booking_date='".$booking_date."' and is_pending=1 and is_accepted=0  and is_completed=0 and is_cancelled=0 "));

    		    $count=count($results['appointmentdata']);
				$results['appointmentdata'] = array_slice( $results['appointmentdata'], $offset, 10 ); 
				if (!empty($results['appointmentdata'])) {
					return Response::json([
					'ResponseCode'=>true,
					'message'=>"Your data shows Succesfully.",
					'Response'=>$results,
					'count'=>$count
					], 200);
				}
				elseif(empty($results['appointmentdata'])){
					return Response::json([
					'ResponseCode'=>true,
					'message'=>"No data exists in table.",
					'Response'=>$results,
					'count'=>$count
					], 200);

				}
				else{
					return Response::json([
					'ResponseCode'=>false,
					'message'=>"Something went wrong.",
					], 200);
				}
    		
    	}
    	elseif ($type==2) {
    		$results['appointmentdata']=DB::select( DB::raw("SELECT 7e_appointments.*,7e_users.id as userid,7e_users.name, 7e_users.email,7e_users.profile_pic FROM 7e_appointments join 7e_users on 7e_users.id=7e_appointments.user_id where prof_id='".$prof_id."' and booking_date='".$booking_date."' and is_pending=1 and is_accepted=1  and is_completed=0 and is_cancelled=0 "));
    		   	$count=count($results['appointmentdata']);
				$results['appointmentdata'] = array_slice( $results['appointmentdata'], $offset, 10 ); 
				if (!empty($results['appointmentdata'])) {
					return Response::json([
					'ResponseCode'=>true,
					'message'=>"Your data shows Succesfully.",
					'Response'=>$results,
					'count'=>$count
					], 200);
				}
				elseif(empty($results['appointmentdata'])){
					return Response::json([
					'ResponseCode'=>true,
					'message'=>"No data exists in table.",
					'Response'=>$results,
					'count'=>$count
					], 200);

				}
				else{
					return Response::json([
					'ResponseCode'=>false,
					'message'=>"Something went wrong.",
					], 200);
				}
    		
    	}

    }


    public function getprofwallet($id){
    	$data=DB::select( DB::raw("SELECT * FROM 7e_profwallet where prof_id='".$id."'"));
    	return $data;
    }

    public function androidpush($pushData){
    	$mytime = date('Y-m-d H:i:s');
		$devices = PushNotification::Device($pushData['token']);
		$message = PushNotification::Message($pushData['message'],array(
			"time" => $mytime,
			"message" => $pushData['message'],
			"action" => $pushData['action'],
			"appointmentid" => $pushData['appointmentid'],
			"profile_pic" => $pushData['profile_pic'],
		));

		$collection = PushNotification::app('appNameAndroid')
		->to($devices)
		->send($message);
		foreach ($collection->pushManager as $push) {
			$response = $push->getAdapter()->getResponse();
		}
		$push = PushNotification::app('appNameAndroid');
		$abc=$push->adapter->setAdapterParameters(['sslverifypeer' => false]);
    } 
    public function iospush($pushData){
    	if (!empty($pushData['token'])){
    	$mytime = date('Y-m-d H:i:s');
		$devices = PushNotification::Device($pushData['token']);
		$message = PushNotification::Message($pushData['message'],array(
			"time" => $mytime,
			"message" => $pushData['message'],
			"action" => $pushData['action'],
			"appointmentid" => $pushData['appointmentid'],
			"profile_pic" => $pushData['profile_pic'],
		));


		$collection = PushNotification::app('appNameIOS')
		->to($devices)
		->send($message);
		foreach ($collection->pushManager as $push) {
			$response = $push->getAdapter()->getResponse();
		
		}
		$push = PushNotification::app('appNameIOS');
		$push->adapter->setAdapterParameters(['sslverifypeer' => false]);
		}

    } 

    public function paymenthistory(Request $request){
    	$user_id=$request->user_id;
    	$type=$request->type;
    	if ($type==1) {
			$users = DB::table('7e_transaction')
	        ->join('7e_users', '7e_users.id', '=', '7e_transaction.user_id')
	        	  ->select('7e_transaction.*', '7e_users.name', '7e_users.email','7e_users.profile_pic')
	        	  	->where('7e_transaction.user_id',$user_id)
	        		->get();
    		if (!empty($users)) {
				return Response::json([
				'ResponseCode'=>false,
				'message'=>"Your data shows successfully.",
				'response'=>$users,
				], 200);
    		}
    		elseif (empty($users)) {
				return Response::json([
				'ResponseCode'=>true,
				'message'=>"No data exists in table."
				], 200);
    		}
    		else{
				return Response::json([
				'ResponseCode'=>false,
				'message'=>"Something went wrong."
				], 200);

    		}
    	}



    }

    public function zoomdata($zoomdata){

       	$key = 'H5Wz9yRFRfuZOXbqlhspew';
		$secret = 'AOMQIGRQRbBTdXSneJIfxHHDMZgGys98mqNg';
		$token = array(
		"iss" => $key,
		"exp" => time() + 60000
		);
		$abc= JWT::encode($token, $secret);
		// print_r($abc);die;
    	 $email=(string)trim($zoomdata['email']);
    	 $name=(string)trim($zoomdata['name']);
		$vars = array(
					'action'=>'create',
					'user_info'=>array(
						'email'=>$email,
						'type'=>'1',
						'first_name'=>$name,
						'last_name'=>$name,
						'password'=>'123456'
						),
					);
				$aa=json_encode($vars);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,"https://api.zoom.us/v2/users");
				curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,$aa);  //Post Fields
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $headers = [
	                'Content-Type: application/json',
					"Accept: application/json,application/xml",
					"Authorization: Bearer $abc",
					"Cache-Control: no-cache",
					"Content-Type: application/json"
                ];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $server_output = curl_exec ($ch);
                curl_close ($ch);
                $arr=json_decode($server_output);
				$zoom_id=$arr->id;
				return $zoom_id;
    }

    public function zoommeeting($zoommeeting){

    	$userdetail=DB::table('7e_users')->where('id',$zoommeeting)->first();

    	$zoomuserid=$userdetail->zoom_id;
    	$key = 'H5Wz9yRFRfuZOXbqlhspew';
		$secret = 'AOMQIGRQRbBTdXSneJIfxHHDMZgGys98mqNg';
		$token = array(
		"iss" => $key,
		"exp" => time() + 60000
		);
		$abc= JWT::encode($token, $secret);

		$vars = array(
					'topic'=>'test'
					);
				$aa=json_encode($vars);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,"https://api.zoom.us/v2/users/".$zoomuserid."/meetings");
				curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,$aa);  //Post Fields
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $headers = [
	                'Content-Type: application/json',
					"Accept: application/json,application/xml",
					"Authorization: Bearer $abc",
					"Cache-Control: no-cache",
					"Content-Type: application/json"
                ];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $server_output = curl_exec ($ch);
                curl_close ($ch);
                $arr=json_decode($server_output);
				// $zoom_id=$arr->id;
				// print_r($arr);die;
				return $arr;
    	// print_r($zoommeeting);die;

    }

  
    public function querybuilder(Request $request){


//     	print_r(base_path().'/Admin/public/iospush/iospush.pem');
//     	die;


// 		$key = 'H5Wz9yRFRfuZOXbqlhspew';
// 		$secret = 'AOMQIGRQRbBTdXSneJIfxHHDMZgGys98mqNg';
// 		$token = array(
// 			"iss" => $key,
// 			"exp" => time() + 60000
// 		);
// 		$abc= JWT::encode($token, $secret);
// 		$curl = curl_init();
// 		curl_setopt_array($curl, array(
// 		CURLOPT_URL => "https://api.zoom.us/v2/users",
// 		CURLOPT_RETURNTRANSFER => true,
// 		CURLOPT_ENCODING => "",
// 		CURLOPT_MAXREDIRS => 10,
// 		CURLOPT_TIMEOUT => 30,
// 		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
// 		CURLOPT_CUSTOMREQUEST => "POST",
// 		CURLOPT_POSTFIELDS => "{\n    \"action\": \"create\",\n    \"user_info\": {\n        \"email\": \"ravindr836ff458@gmail.com\",\n        \"type\": \"1\",\n        \"first_name\": \"gf\",\n        \"last_name\": \"gfdg\",\n        \"password\": \"dgffdgfd\"\n    }\n}",
// 		CURLOPT_HTTPHEADER => array(
// 		"Accept: application/json,application/xml",
// 		"Authorization: Bearer $abc",
// 		"Cache-Control: no-cache",
// 		"Content-Type: application/json",
// 		"Postman-Token: 04f290db-dcb2-dadd-2bdc-d3258d26b008"
// 		),
// 		));

// 		$response = curl_exec($curl);
// print_r(json_decode($response));die;
//     	$a = "ababb";

// $b =  strrev($a);

//     $string_reverse = str_split($b);

//     $palin = '';

//     foreach($string_reverse as $value){

//         $palin.= $value; 
//     }

//     print $palin;

//     if($a == $palin){

//         print "<br>Palindrome";

//     } else {

//         print "<br>Not Palindrome"; 

//     }
//     die;



// 	// $key = 'EpmPqUWgQZ2QBefYV7fFpw';
// 	// $secret = '9D1Sqp8NH3VFEMGZX3ooHIVqcHBQSm15KDBg';
// 	// $token = array(
// 	// 	"iss" => $key,
//  //        // The benefit of JWT is expiry tokens, we'll set this one to expire in 1 minute
// 	// 	"exp" => time() + 60000
// 	// );
// 	// $abc=array('token'=>$token,'secret'=>$secret)
// 	// $abc= JWTAuth::encode($token, $secret);
// 	// print_r($abc);die;




$receivers = array(
		  0 => array(
		    'ReceiverEmail' => "something@somewhere.com", 
		    'Amount'        => "0.01",
		    'UniqueId'      => "id_001", 
		    'Note'          => " Test Streammer 1"), 
		  1 => array(
		    'ReceiverEmail' => "something@somewhere.com",
		    'Amount'        => "0.01",
		    'UniqueId'      => "id_002", 
		    'Note'          => " Test Streammer 2"), 
		);
		
$response = PaypalMassPayment::executeMassPay('Some Subject', $receivers);

print_r($response);die;


print_r($response);die;
    		$users = DB::table('7e_users')
				->join('7e_stripedetails', '7e_users.id', '=', '7e_stripedetails.user_id')
				->join('7e_cards', '7e_users.id', '=', '7e_cards.user_id')
				->where('7e_users.id',1)
				->get();
				print_r($users);die;




$stripe = Stripe::make('sk_test_AV45bRuYmLu0nLFvLggzTQjF', '2006-07-06');
    	$charge = $stripe->charges()->create([
    'customer' => 'cus_4EBumIjyaKooft',
    'currency' => 'USD',
    'amount'   => 50.49,
]);
print_r($charge);die;
echo $charge;


die;
// \Stripe::setApiKey(env('STRIPE_SECRET'));
\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $customer =\Stripe\Customer::create(array(
            'email' => $request->stripeEmail,
            'source'  => $request->stripeToken
        ));
        print_r($customer);die;

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
