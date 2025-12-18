<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Add this line to import Auth
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;   
use Illuminate\Support\Facades\Session; // Import Session
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Requests\Admin\ForgotPasswordLinkRequest;
use App\Http\Requests\Admin\UpdatePasswordRequest;


class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login'); // Adjust the view path as needed
    }
    
    public function login(AdminLoginRequest  $request)
    {
       

        
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
        ]);
    
        // Get the credentials from the request
        $credentials = $request->only('email', 'password');
       
        // Find the user by email
        $user = User::where('email', $credentials['email'])->first();
      
        // Check if the user exists and the plain text password matches
        if ($user && Hash::check($credentials['password'], $user->password)) {
            
        // Log the user in
        Auth::login($user);
        
        // Store the entire user object in session
        Session::put('user', $user);

        // Remember Me functionality: save email and password in cookies if checked
        if ($request->has('remember')) {
            Cookie::queue('email', $request->email, 43200); // Store for 30 days (43200 minutes)
            Cookie::queue('password', $request->password, 43200); // Store for 30 days (Note: storing plaintext passwords is generally not recommended)
        } else {
            // Forget cookies if Remember Me is unchecked
            Cookie::queue(Cookie::forget('email'));
            Cookie::queue(Cookie::forget('password'));
        }
            // Redirect based on user role
            if ($user->role === '1') {
                return redirect()->route('dashboard.admin.dashboard'); // Admin Dashboard
            } 
        }

        // If credentials are incorrect, redirect back with error
        return redirect()->back()->withInput($request->only('email'))
            ->withErrors([
                'password' => 'Incorrect email address or password.',
            ]);
    }

    public function logout(Request $request)
    {
        
        Auth::logout(); // Log the user out

        // Optionally, you can invalidate the session
        $request->session()->invalidate();
        
        // Regenerate the session token
        $request->session()->regenerateToken();

        // Redirect the user to a desired route after logout
        return redirect('/')->with('success', 'You have been successfully logged out!');
    }

    public function forgotPassword(){
        return view('admin.auth.forgotPassword.forgot_password'); 
    }

    public function forgotPasswordLink(ForgotPasswordLinkRequest $request)
    {
    
        // Attempt to send the password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );
        
        $logoPath = url('/') . "/assets/admin/images/dash-logo.png";
        // Check if the link was successfully sent or not
        if ($status === Password::RESET_LINK_SENT) {
            return redirect()->route('forgot-password')->with('success', 'A link will be sent via email to generate a new password');
        } else {
            return redirect()->route('forgot-password')->withErrors(['email' => __($status)]);
        }
    }
    

    public function showResetForm(Request $request)
    {
        return view('admin.auth.forgotPassword.new_password')->with(
            ['token' => $request->route('token'), 'email' => $request->email]
        );
    }
    

    public function updatePassword(UpdatePasswordRequest $request)
    {
       
        // Retrieve the reset record from the password_reset_tokens table by email
        $resetData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // Check if the record exists and the provided token matches the stored hashed token
        if (!$resetData || !Hash::check($request->token, $resetData->token)) {
            return back()->withInput()->with('error', 'Invalid token!');
        }

        // If the token is valid, update the user's password
        User::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        // Delete the token from the password_resets table after updating the password
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Redirect back to the same page with a success message
        return redirect()->route('login')->with('success', 'Your password has been successfully changed!');
    }


    public function showDeleteAccountPage($id = null)
    {
        $user = null;

        if ($id) {
            $user = \App\Models\User::find($id);
        }

        // If user not found or ID not provided
        if (!$user) {
            return view('admin.auth.deleteAccount', ['user' => (object)['id' => null]]);
        }

        return view('admin.auth.deleteAccount', compact('user'));
    }



    public function confirmDeleteAccount(Request $request)
    {
        $user = \App\Models\User::find($request->user_id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        // Mark user as deleted (soft delete simulation)
        $user->is_deleted = true;

        // Nullify tokens
        $user->api_token      = null;
        $user->google_token   = null;
        $user->facebook_token = null;
        $user->apple_token    = null;
        $user->device_token   = null;
        $user->device_type    = null;
        $user->device_id      = null;
        $user->is_social      = 0;

        $user->save();

        return redirect()->back()->with('success', 'Your account has been deleted successfully.');
    }

    
    
   
    
}
