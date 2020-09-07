@component('mail::message')
# Подтверждение регистрации

Подтверждение регистрации на Femida24.kz
Вы получили это письмо, так как Ваш адрес {{ $user->email }} был указан при регистрации на сайте Femida24.kz
Для подтверждения регистрации нажмите кнопку ниже:

@component('mail::button', ['url' => route('register.verify', ['token' => $user->verify_token])])
    Подтверждаю регистрацию
@endcomponent

В случае, если вы получили это письмо по ошибке, просто проигнорируйте его.

@endcomponent