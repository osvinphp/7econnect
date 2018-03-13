<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


Route::group(['prefix' => '/api'], function () {
	Route::post('/signup', 'Controller@signup');
	Route::post('/login', 'Controller@login');
	Route::post('/logout', 'Controller@logout');
	Route::post('/forgotpassword', 'Controller@forgotpassword');
	Route::get('/newpassword', 'Controller@newpassword');
	Route::post('/updateNewpassword', 'Controller@updateNewpassword');
	Route::post('/editprofile', 'Controller@editprofile');
	Route::post('/changepassword', 'Controller@changepassword');
	Route::post('/pushnotification', 'Controller@pushnotification');
	Route::post('/addrating', 'Controller@addrating');
	Route::post('/addcard', 'Controller@addcard');
	Route::post('/cardlisting', 'Controller@cardlisting');
	Route::post('/getprofile', 'Controller@getprofile');
	Route::post('/professionalsignup', 'Controller@profsignup');
	Route::post('/basicprofile', 'Controller@basicprofile');
	Route::post('/addtreatment', 'Controller@addtreatment');
	Route::post('/addmodeconsulation', 'Controller@addmodeconsulation');
	Route::post('/setschedule', 'Controller@setschedule');
	Route::post('/expertstopics', 'Controller@expertstopics');
	Route::post('/experttopicdetail', 'Controller@experttopicdetail');
	Route::post('/deletetreatment', 'Controller@deletetreatment');
	Route::post('/edittreatment', 'Controller@edittreatment');
	Route::post('/questionview', 'Controller@questionview');
	Route::post('/editquestion', 'Controller@editquestion');
	Route::post('/deletequestion', 'Controller@deletequestion');
	Route::post('/addquestion', 'Controller@addquestion');
	Route::post('/searchexperts', 'Controller@searchexperts');
	Route::post('/setstatus', 'Controller@setstatus');
	Route::post('/deleteschedule', 'Controller@deleteschedule');
	Route::post('/editschedule', 'Controller@editschedule');
	Route::post('/setpushstatus', 'Controller@setpushstatus');
	Route::post('/upcomingapointment', 'Controller@upcomingapointment');
	Route::post('/schedulelisting', 'Controller@schedulelisting');
	Route::post('/checkavailability', 'Controller@checkavailability');
	Route::post('/profdetail', 'Controller@profdetail');
	Route::post('/notificationdetail', 'Controller@notificationdetail');	
	Route::post('addappointment', 'Controller@addappointment');
	Route::post('appointmentaction', 'Controller@appointmentaction');
	Route::post('paymenthistory', 'Controller@paymenthistory');
	Route::post('appointmentdetail', 'Controller@appointmentdetail');
	Route::post('appointmentlistingprof', 'Controller@appointmentlistingprof');

	


	

	Route::get('/querybuilder', 'Controller@querybuilder');
});






Route::get('manageMailChimp', 'MailChimpController@manageMailChimp');

Route::post('subscribe',['as'=>'subscribe','uses'=>'MailChimpController@subscribe']);

Route::post('sendCompaign',['as'=>'sendCompaign','uses'=>'MailChimpController@sendCompaign']);







Route::get('/', function () {
return view('examples.login');
});



Route::post('/adminlogin', 'AdminController@checklogin');

Route::group(['middleware' => 'usersession'], function () {

		Route::get('/dashboard',  'AdminController@dashboard');
		Route::get('/logout', 'AdminController@logout');
		Route::get('/users',  'AdminController@userlisting');
		// Route::get('users/userlisting1', ['as'=>'userlisting1','uses'=>'AdminController@userlisting1']);
		// Route::post('/userlisting1',  'AdminController@userlisting1');
		Route::get('/professional',  'AdminController@professional');
		Route::get('/adduser', function () {
			return view('examples.adduser');
		});
		Route::post('/adduser',  'AdminController@adduser');
		
		Route::get('/addprofessional', function () {
		return view('examples.addprofessional');
		});
		Route::post('/addprofessional',  'AdminController@addprofessional');


		Route::get('activate/{id}', array('as' => 'group', 'uses' => 'AdminController@activate'));
		Route::get('suspend/{id}', array('as' => 'group', 'uses' => 'AdminController@suspend'));
		Route::get('userdetail/{id}', array('as' => 'group', 'uses' => 'AdminController@userdetail'));
		Route::post('edituserdetail', 'AdminController@edituserdetail');

		Route::get('professionaldetail/{id}', array('as' => 'group', 'uses' => 'AdminController@profdetail'));


        Route::post('/updateprofessional', ['as' => 'update_professional', 'uses' => 'AdminController@updateProDetail']);


        Route::get('deletetime/{id}', ['as' => 'deletetime', 'uses' => 'AdminController@deletetime']);
        Route::get('edittime/{id}', ['as' => 'edittime', 'uses' => 'AdminController@edittime']);


        Route::post('getuserdata', ['as' => 'getuserdata', 'uses' => 'AdminController@getuserdata']);


		Route::get('/listtimezone',  'AdminController@listtimezone');

		Route::get('/addtime', function () {
			return view('examples.addtimezone');
		});
		// Route::get('addtime','AdminController@addedtime');



		Route::get('/treatmentpending','AdminController@pendingtreatment');
		Route::get('/approvedtreat/{id}',array('as' => 'group', 'uses' => 'AdminController@approvedtreat'));
		Route::get('/rejecttreat/{id}',array('as' => 'group', 'uses' => 'AdminController@rejecttreat'));
		Route::get('/treatmentapprove','AdminController@approvedtreatment');
		Route::get('/treatmentDeatils/{id}',array('as' => 'group', 'uses' => 'AdminController@treatmentdeatil'));
		Route::get('/treatmentreject','AdminController@rejecttreatment');
		

		

		Route::get('appointmentdetail/{id}', ['as' => 'appointmentdetail', 'uses' => 'AdminController@appointmentdetail']);

		Route::get('approveappointment/{id}', ['as' => 'approveappointment', 'uses' => 'AdminController@approveappointment']);


		Route::get('rejectappointment/{id}', ['as' => 'rejectappointment', 'uses' => 'AdminController@rejectappointment']);



		Route::get('/reviews','AdminController@reviews');
		Route::post('edittimezone',  'AdminController@edittimezone');
		Route::post('edittimezonedata',  'AdminController@edittimezonedata');
		Route::post('addtime',  'AdminController@addtime');
		Route::get('appointpending',  'AdminController@appointpending');
		Route::get('appointapproved',  'AdminController@appointapproved');
		Route::get('appointcompleted',  'AdminController@appointcompleted');
		Route::get('appointcancelled',  'AdminController@appointcancelled');

		Route::get('adminsetting', 'AdminController@adminsetting');
		Route::post('adminsetting', ['as' => 'adminsetting', 'uses' => 'AdminController@editsetting']);


		Route::get('pendingpayments', ['as' => 'pendingpayments', 'uses' => 'AdminController@pendingpayments']);
		Route::get('readypayments', ['as' => 'readypayments', 'uses' => 'AdminController@readypayments']);
		Route::get('completepayments', ['as' => 'completepayments', 'uses' => 'AdminController@completepayments']);
		Route::get('refundpayments', ['as' => 'refundpayments', 'uses' => 'AdminController@refundpayments']);




		// Route::get('/readypayments', function () {
		// return view('examples.paymentready');
		// });

		// Route::get('/completepayments', function () {
		// return view('examples.paymentcompleted');
		// });

		// Route::get('/refundpayments', function () {
		// return view('examples.paymentrefund');
		// });
		Route::get('/refundpending', function () {
		return view('examples.refundpending');
		});
		Route::get('/refundapproved', function () {
		return view('examples.refundapproved');
		});
		Route::get('/refundrejected', function () {
		return view('examples.refundrejected');
		});

});









// Route::auth();

Route::get('/home', 'HomeController@index');
