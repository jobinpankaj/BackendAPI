<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactUs;
use App\Models\ContactUs as ContactUsModel;

class ContactUsController extends Controller
{
    public function contactUs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required',
            'type' => 'required',
            // 'website' => 'nullable|url',
            'website'   => 'required',
            'message' => 'required'
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);

        try {

            $content = '<p>First Name : ' . $request->first_name . '<br/>Last Name : ' . $request->last_name . '<br/>Email : ' . $request->email . '<br/>Phone : ' . $request->phone . '<br/>Type : ' . $request->type . '<br/>';

            if ($request->has('website')) {
                $content .= 'Website : ' . $request->website . '<br/>';
            }

            $content .= 'Message: <br/><p>' . $request->message . '</p></p>';

            $data = [
                'body' => $content,
                'subject' => Lang::get('messages.contact_us_subject')
            ];

            Mail::to(config('app.support_email'))->send(new ContactUs($data));
        } catch (Exception $e) {
            return sendError('Error', ['error' => Lang::get('messages.error_in_sending_enquiry')], 500);
        }

        $contactUs = ContactUsModel::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => $request->type,
            'website' => $request->website,
            'message' => $request->message,
        ]);

        $success = $contactUs;
        return sendResponse($success, Lang::get('messages.enquiry_sent_successfully'));
    }
}
