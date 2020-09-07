<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
     
        if(Auth::check() && Auth::user()->status==0){
            
            Auth::logout();
            return redirect('login')->with('message', 'User not active');
        
        }else{    
            
            if (Auth::check() && Auth::user()->isRole($role)) {
                return $next($request);
            }
    
            return redirect('login');

            
        }
        
        
    }
}
