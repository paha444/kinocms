<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;
//use App\Mail\Auth;

use Illuminate\Support\Facades\Auth;
//use App\VerifyMail;
use App\Mail\Auth\VerifyMail;
//use App\Mail;
//use Illuminate\Auth\Events\VerifyMail;
//use Illuminate\Contracts\Auth\VerifyMail;
//use App\Offers;

use Mail;
use Session;

//use DB;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            //'password' => 'required|string|min:8|confirmed',

            'password' => [
                'required',
                'string',
                'min:10',             // must be at least 10 characters in length
                'confirmed',
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                //'regex:/[@$!%*#?&]/', // must contain a special character
            ],

        ]
        
/*        [
             //'required' => 'Поле :attribute должно быть заполнено.',

           'name' => [
                'required'=>'11111',
                'string'=>'22',
                'max'=>'333',
           ],
                
           'email' => [
                'required'=>'44',
                'string'=>'555',
                'email'=>'666666',
                'max:255'=>'77777',
                'unique:users'=>'8888888',
           ],

             
           'password' => [
                'required' => 'Это поле должно быть заполнено.',
                'string' => 'только цифры и текстовые символы',
                'min' => 'должно минимум 10 символов',             // must be at least 10 characters in length
                'confirmed' => 'пароли должны совпадать',
                'regex' => 'должна быть одна маленькая буква',      // must contain at least one lowercase letter
                //'regex:/[A-Z]/' => 'должна быть одна большая буква',      // must contain at least one uppercase letter
                //'regex:/[0-9]/' => 'должна быть одна цифра',      // must contain at least one digit
           ], 
        ]
        */
        );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        
        //print_r($data);
        //die;
        
        $role_id = 2;
        
        //if($role_id!=10){

            $user = User::create([
                'role_id' => $role_id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'verify_token' => Str::random(),
                'status' => 0,
                //'city' => $data['city'],
            ]);
            
            
            $id= $user->id;
            
            //DB::insert('insert into role_user (role_id, user_id) values (?, ?)', [$role_id, $id]);


/*
            Mail::send('emails.auth.verify', $user, function ($message)
            {
              $message->to($to, $user['first_name'])->subject('Активация аккаунта на femida24.kz');
              $message->from('noreply@femida24.kz','femida24.kz');
            });

*/            
            Mail::to($user->email)->send(new VerifyMail($user));
//////////////
/*
            $send = new VerifyMail($user);
            
            //$environment = App::environment();
            
            $hostname = env("APP_URL", "");
            
            $message = $hostname.'/verify/'.$send->user->getOriginal('verify_token');
            //echo $message;
            
            //print_r($send); 
            //die;

            $to      = $user->email;
            $subject = 'VerifyMail';
            $message = $message;
            $headers = 'From: webmaster@example.com' . "\r\n" .
                'Reply-To: webmaster@example.com' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            
            mail($to, $subject, $message, $headers);
*/
////////////////
            
            return $user;

        //}else{
        //    return redirect()->route('index')
        //        ->with('message', __('text.text_19'));
       // }
        
    }
    

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        event(new Registered($user = $this->create($request->all())));
    
        return redirect()->route('index')
            ->with('message', 'Проверьте свою электронную почту и перейдите по ссылке для подтверждения.');
    }
    


    public function verify($token)
    {   
        
        if (!$user = User::where('verify_token', $token)->first()) {
            return redirect()->route('index')
                ->with('message', 'Извините, ваша ссылка не может быть идентифицирована.');
        }
    
        $user->status = User::STATUS_ACTIVE;
        $user->verify_token = null;
        $user->save();

////////    

          $data = Session::all();
          
          if(isset($data['offers'])){  
              
              DB::table('offers')->whereIn('id', $data['offers'])
              ->update(['user_id' => $user->id]);
          
          
              Session::forget('offers');  
          }  
          
          

////////
    
        return redirect()->route('index')
            ->with('message', 'Ваш e-mail проверен. Теперь вы можете войти в систему.');
    }
    
}
