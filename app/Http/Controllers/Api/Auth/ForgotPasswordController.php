<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\ResetCodePassword;
use App\Mail\SendCodeResetPassword;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Auth\ForgotPasswordRequest;

class ForgotPasswordController extends ApiController
{
    /**
     * Send random code to email of user to reset password (Setp 1)
     *
     * @param  mixed $request
     * @return void
     */
    public function __invoke(ForgotPasswordRequest $request)
    {
        ResetCodePassword::where('email', $request->email)->delete();

        $codeData = ResetCodePassword::create($request->data());

        Mail::to($request->email)->send(new SendCodeResetPassword($codeData->code));

        return $this->jsonResponse(null, trans('passwords.sent'), 200);
    }

}
