<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use Illuminate\Support\Facades\Mail;

class MailResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $token;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */

    public function toMail( $notifiable ) {
       //$link = url( "/password/reset/?token=" . $this->token );
/*    
       return ( new MailMessage )
          ->view('auth.passwords.reset')
          ->from('info@example.com')
          ->subject( 'Reset your password' )
          ->line( "Hey, We've successfully changed the text " )
          ->action( 'Reset Password', $link )
          ->attach('reset.attachment')
          ->line( 'Thank you!' );*/
        //$token = $this->token;
        
        //Mail::to(request()->email)->send(new newpassword($token));
          
          //print_r($notifiable); 
          
/*          $to_email = $notifiable->getOriginal('email');//->email;
          
          $to_name = $notifiable->getOriginal('fullname');
          
          //die;
          
 		Mail::send('emails.auth.reset_password', ['token' => $this->token], function ($message) use ($to_email, $to_name){
            //$message->from('hello@app.com', 'Your Application');
            //$message->to(request()->email, '')->subject('Your Password Reset!');
            $message->to($to_email, $to_name)
            ->subject('Laravel Test Mail');
            $message->from(MAIL_FROM_ADDRESS,'Test Mail');

        });*/
        
        
        
        return (new MailMessage)
            //->subject('Your Reset Password Subject Here')
            ->greeting(__('text.restore_password'))
            //->from($this->user->email, $this->user->name)
            //->subject('My Dummy Subject')
            //->greeting('To: '.$notifiable->email)
            
            ->subject(__('text.text_27'))
            //->greeting('')
            ->line(__('text.text_28'))
            ->action(__('text.text_29'), url(config('app.url').route('password.reset', $this->token, false)))
            ->line(__('text.text_30'));
            
            //->copyright(' ');

          
    }
    
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
