<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use App\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function authenticated(Request $request, $user)
    {
        if ($user->status !== User::STATUS_ACTIVE) {
            $this->guard()->logout();
            return back()->with('error', __('text.text_22'));
        }
        return redirect()->intended($this->redirectPath());
    }
    
    
    protected function redirectPath()
    {
        
        $profiles = array(2,3,4,5);
        
        if(auth()->user()->role_id==10) 
            return '/admin';
        
        if(auth()->user()->role_id==2) 
            return '/profile/client';

        if(auth()->user()->role_id==3) 
            return '/profile/client';

        if(auth()->user()->role_id==5) 
            return '/profile/lawyer';

    }
    
}
