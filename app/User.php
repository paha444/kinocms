<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Auth;

use DB;


use Illuminate\Notifications\Messages\MailMessage;

use Illuminate\Support\Facades\Mail;
//use App\User;
use Illuminate\Support\MessageBag;

use Session;

class User extends Authenticatable
{
    use Notifiable;

    const STATUS_DELETED = 2;
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'verify_token', 'status',
    ];


    protected $hidden = [
        'password', 'remember_token',
    ];

    public function roles()
    {
        //return $this->belongsToMany(Role::class);
    }

    public function isAdmin()
    {
        if(Auth::user()->role_id==1) return true;
        //return (boolean)$this->roles->where('name', 'admin')->count();
    }
    public function isClient()
    {
        if(Auth::user()->role_id==2) return true;
        //return (boolean)$this->roles->where('name', 'client')->count();
    }
    public function isBusiness()
    {
        if(Auth::user()->role_id==3) return true;    
        //return (boolean)$this->roles->where('name', 'employee')->count();
    }
    public function isManager()
    {
        //return (boolean)$this->roles->where('name', 'manager')->count();
    }

    public function isLawyers()
    {
        if(Auth::user()->role_id==5) return true;
        //return (boolean)$this->roles->where('name', 'lawyers')->count();
    }


    public function isRole($role)
    {
/*        
        //print_r($role); die;
        $roles = explode('|',$role);
        
        $roles_arr = array(
            2=>'client',
            3=>'business',
            5=>'lawyers',
            10=>'admin'
        );
        
        $result = false;
        
        foreach($roles as $role){
            
            $role_id = array_search($role, $roles_arr);
            if(Auth::user()->role_id == $role_id){
                $result = true;
                break;
            }
        }*/
        
        $result = false;
        
        if(Auth::check()){
            
            $roles = explode('|',$role);

            $roles_arr = array(
                1=>'admin',
                2=>'client',
            );
        
            foreach($roles as $role){
                
                $role_id = array_search($role, $roles_arr);
                if(Auth::user()->role_id == $role_id){
                    $result = true;
                    break;
                }
            }
    
        
        //$user = Auth::user();
/*        
            $roles = explode('|',$role);
            
            $row = DB::table('roles')
            ->where('id', Auth::user()->role_id)
            ->whereIn('name', $roles)
            ->first();
            
            if($row){
            Auth::user()->role_name = $row->name;
            Auth::user()->role_name_ru = $row->name_ru;
            }
            $result = (boolean) $row;
            

*/
            //$result = true;
            
            
        }else{
            $result = false;
        }

        return $result;
    }   
    
    
    public function sendPasswordResetNotification($token)
    {   

    $this->notify(new Notifications\MailResetPasswordNotification($token));
    
    Session::put('message_send', __('passwords.sent'));
    
    //$messageBag = new MessageBag;

    // add your error messages:
    //$messageBag->add('send', 'Your custom error message!');    
    
    //$this->errors = $messageBag;
    
    //return redirect('/')->withErrors($messageBag);
    
        //$request = request();
        
        //Mail::to(request()->email)->send(new newpassword($token));

/*        
 		Mail::send('emails.auth.reset_password', ['token' => $token], function () use($token) {
            $m->from('hello@app.com', 'Your Application');
            $m->to(request()->email, '')->subject('Your Password Reset!');
        });
*/    
    
    }
    
    
}
