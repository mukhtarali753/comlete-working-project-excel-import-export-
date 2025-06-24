<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    // Show forget password form
    public function showForgetPasswordForm()
    {
        return view('auth.forgetPassword');
    }

    // Handle forget password form submit
    public function submitForgetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $token = Str::random(64);

        // Delete old tokens if exists
        DB::table('password_resets')->where('email', $request->email)->delete();

        DB::table('password_resets')->insert([
            'email' => $request->email, 
            'token' => $token, 
            'created_at' => Carbon::now()
        ]);

        Mail::send('email.forgetPassword', ['token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Reset Password Notification');
        });

        return back()->with('message', 'We have sent a password reset link to your email!');
    }

    // Show reset password form
    public function showResetPasswordForm($token)
    {
        return view('auth.forgetPasswordLink', ['token' => $token]);
    }

    // Handle new password submission
    public function submitResetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $resetRecord = DB::table('password_resets')
                         ->where([
                             'email' => $request->email,
                             'token' => $request->token
                         ])
                         ->first();

        if (!$resetRecord) {
            return back()->withInput()->with('error', 'Invalid or expired token!');
        }

        // Update user password
        User::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        // Delete reset record
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect('/login')->with('message', 'Your password has been successfully changed!');
    }
}
