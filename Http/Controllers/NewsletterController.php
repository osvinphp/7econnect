<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class NewsletterController extends Controller
{
	protected $mailchimp;

	public function __construct()
	{
		$this->mailchimp = new \MailChimp(config('services.mailchimp.api_key'));
	}
	public function subscribe(Request $request){
			$this->validate($request, [
			'email' => 'required|email'
			]);
			$listId = '6f0047b0f6'; // Replace this with your own List ID

			try {
				$this->mailchimp->lists->subscribe($listId, $request->only(['email']));
			} 
			catch (\MailChimp_Error $e) {
				if ($e->getMessage()) {
				$request->session()->flash('errors',
				$this->createViewError('mailchimp_error', $e->getMessage()));
				return redirect()->back();
				} else {
				$request->session()->flash('errors',
				$this->createViewError('mailchimp_error', 'An unknown error occurred'));
				return redirect()->back();
				}
			}
	}
}
