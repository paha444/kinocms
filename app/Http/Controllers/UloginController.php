<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use Auth;
use Hash;
use Redirect;

use Illuminate\Support\Str;
use App\Mail\Auth\VerifyMail;

//use Illuminate\Support\Facades\Validator;
//use Illuminate\Foundation\Auth\RegistersUsers;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Foundation\Auth\AuthenticatesUsers;

use DB;
use File;

use Illuminate\Support\Facades\Mail;

class UloginController extends Controller
{
    
    
    
   public function translit($s) {
      $s = (string) $s; // преобразуем в строковое значение
    
     $table = array(
                   'А' => 'A',
                   'Б' => 'B',
                   'В' => 'V',
                   'Г' => 'G',
                   'Д' => 'D',
                   'Е' => 'E',
                   'Ё' => 'YO',
                   'Ж' => 'ZH',
                   'З' => 'Z',
                   'И' => 'I',
                   'Й' => 'J',
                   'К' => 'K',
                   'Л' => 'L',
                   'М' => 'M',
                   'Н' => 'N',
                   'О' => 'O',
                   'П' => 'P',
                   'Р' => 'R',
                   'С' => 'S',
                   'Т' => 'T',
                   'У' => 'U',
                   'Ф' => 'F',
                   'Х' => 'H',
                   'Ц' => 'C',
                   'Ч' => 'CH',
                   'Ш' => 'SH',
                   'Щ' => 'CSH',
                   'Ь' => '',
                   'Ы' => 'Y',
                   'Ъ' => '',
                   'Э' => 'E',
                   'Ю' => 'YU',
                   'Я' => 'YA',
    
                   'а' => 'a',
                   'б' => 'b',
                   'в' => 'v',
                   'г' => 'g',
                   'д' => 'd',
                   'е' => 'e',
                   'ё' => 'yo',
                   'ж' => 'zh',
                   'з' => 'z',
                   'и' => 'i',
                   'й' => 'j',
                   'к' => 'k',
                   'л' => 'l',
                   'м' => 'm',
                   'н' => 'n',
                   'о' => 'o',
                   'п' => 'p',
                   'р' => 'r',
                   'с' => 's',
                   'т' => 't',
                   'у' => 'u',
                   'ф' => 'f',
                   'х' => 'h',
                   'ц' => 'c',
                   'ч' => 'ch',
                   'ш' => 'sh',
                   'щ' => 'csh',
                   'ь' => '',
                   'ы' => 'y',
                   'ъ' => '',
                   'э' => 'e',
                   'ю' => 'yu',
                   'я' => 'ya',
                   ' ' => '-',
                   '  ' => '-',
                   '.' => '-',
                   ',' => '-',
                   '>' => '-',
                   '<' => '-',
                   '=' => '-',
                   '+' => '-',
                   '!' => '-',
                   '?' => '-',
                   '@' => '-',
                   ':' => '-',
                   '"' => '-',
                   '&nbsp;' => ''
    
    );
    
    $s = str_replace(array_keys($table),array_values($table),$s);
    
    
        $s = strip_tags($s); // убираем HTML-теги
        $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
        $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
        $s = trim($s); // убираем пробелы в начале и конце строки
    
    
        $s = strtolower($s);
        $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
    
        $s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
        $s = preg_replace("/\s+/", ' ', $s);
    
        $s = str_replace("--", "-", $s); // заменяем пробелы знаком минус
    
        $s = str_replace("--", "-", $s);
    
    //  echo $s.'<br><br>';
    
      return $s; // возвращаем результат
    }

    
    
    
    
       // Login user through social network.
    public function login(Request $request)
    {
        // Get information about user.
        $data = file_get_contents('http://ulogin.ru/token.php?token=' . $request->get('token') .
            '&host=' . $_SERVER['HTTP_HOST']);
        $user = json_decode($data, true);

        // Check exist email.
        if (isset($user['email']) && !empty($user['email'])) {
            // Find user in DB.
            $userData = User::where('email', $user['email'])->first();

            // Check exist user.
            if ($userData) {
                // Check user status.
                //if ($userData->status) {
                    // Make login user.
                    Auth::loginUsingId($userData->id, true);

                //} else {
                    // Wrong status.
                 //   \Session::flash('flash_message_error', trans('interface.AccountNotActive'));
                //}

                return Redirect::back();
            } else {
                // Make registration new user.
                
                $role_id = 2;
                
                $verify_token = Str::random();

                $city = '';
                if(isset($user['city'])){
                    $city = $user['city'];
                }
                
                $photo = '';
                if(isset($user['photo'])){
                    $photo = $user['photo'];
                }

                
                //$file = $user['photo'];
                
                //$fileName = '';
                
                //if($file){

                    //$fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                    //$file->move(public_path('images/avatars'),$fileName); 
                
                //}
    


                $name = $this->translit($user['last_name']).'_'.$this->translit($user['first_name']);
                
                // Create new user in DB.
                $newUser = User::create([
                    'role_id' => $role_id,
                    'name' => $name,
                    //'country' => $user['country'],
                    'email' => $user['email'],
                    'password' => Hash::make(str_random(8)),
                    //'role' => 'user',
                    'status' => 0,
                    'verify_token' => $verify_token,
                    'city' => $city,
                    'avatar' => $photo,
                    //'ip' => $request->ip()
                    'fullname' => $user['first_name'], 
                    'family' => $user['last_name'],
                ]);
                
                
                DB::insert('insert into role_user (role_id, user_id) values (?, ?)', [$role_id, $newUser->id]);


                Mail::to($newUser->email)->send(new VerifyMail($newUser));
              
/*                
                $send = new VerifyMail($newUser);
                
                Mail::send('emails.auth.verify', $send, function ($message)
                {
                  $message->to($newUser->email,'')->subject('Активация аккаунта на femida24.kz');
                  $message->from('noreply@femida24.kz','femida24.kz');
                });
              
*/              
              
              
              
                
                    //$send = new VerifyMail($user);
                    
                    //$environment = App::environment();
/*                    
                    $hostname = env("APP_URL", "");
                    
                    $message = $hostname.'/verify/'.$verify_token;
                    //echo $message;
                    
                    //print_r($send); 
                    //die;
                    $to      = $user['email'];
                    
                    
                    $subject = 'VerifyMail';
                    $message = $message;
                    $headers = 'From: webmaster@example.com' . "\r\n" .
                        'Reply-To: webmaster@example.com' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();
                    
                    mail($to, $subject, $message, $headers);


                Mail::send('emails.auth.verify', [], function ($message)
                {
                  $message->to($to, $user['first_name'])->subject('Активация аккаунта на femida24.kz');
                  $message->from('noreply@femida24.kz','femida24.kz');
                });
*/
                   
                   
                    
                    return redirect()->route('index')
                ->with('message', __('text.text_18'));




                // Make login user.
                //Auth::loginUsingId($newUser->id, true);

                //\Session::flash('flash_message', trans('interface.ActivatedSuccess'));

                //return Redirect::back();
            }
        }

        \Session::flash('flash_message_error', trans('interface.NotEmail'));

        return Redirect::back();
    }

}
