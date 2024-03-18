<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterSubscriptionMail;
use App\Models\NewsletterSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;

class NewsletterSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'age_check' => 'required|accepted',
            'marketing_permission' => 'required|accepted'
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        $data = NewsletterSubscription::where('email', $validated['email'])->first();

        if($data) {
            if($data->active) {
                return sendError('Already Subscribed', ['error' => Lang::get("messages.already_subscribed")], 200);
            } else {
                $content = '<p>Congratulations ' . $request->input('email') . '<br/>You are now subscribed to Buvons Local Pro newsletter.<br/>';

                $mailData = [
                    'body' => $content,
                    'subject' => Lang::get('messages.newsletter_subscription_subject')
                ];

                Mail::to($request->input('email'))->send(new NewsletterSubscriptionMail($mailData));   
                
                $data->active = 1;
                $data->save();
                $success = [];
                $message = Lang::get("messages.newsletter_subscription_created_successfully");
                return sendResponse($success, $message);
            }
        } else {
            try {
                $content = '<p>Congratulations ' . $request->input('email') . '<br/>You are now subscribed to Buvons Local Pro newsletter.<br/>';

                $mailData = [
                    'body' => $content,
                    'subject' => Lang::get('messages.newsletter_subscription_subject')
                ];

                Mail::to($request->input('email'))->send(new NewsletterSubscriptionMail($mailData));                
                NewsletterSubscription::create([
                    'email' => $validated['email'],
                    'marketing_permission' => isset($validated['marketing_permission']) ? $validated['marketing_permission'] : 0,
                    'start_date' => Carbon::now()
                ]);

                $success = [];
                $message = Lang::get("messages.newsletter_subscription_created_successfully");
                return sendResponse($success, $message);

            }
            catch(Exception $e){
                 return sendError('Something went wrong', ['error' => Lang::get('messages.something_went_wrong')], 500);
            }
            return sendError('Something went wrong', ['error' => Lang::get('messages.something_went_wrong')], 500);
        }

    }
}
