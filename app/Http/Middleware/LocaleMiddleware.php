<?php

namespace App\Http\Middleware;

use Closure;

use App;
use Request;

class LocaleMiddleware
{
    public static $mainLanguage = 'ru'; //�������� ����, ������� �� ������ ������������ � URl
    
    public static $languages = ['en', 'ru', 'kz']; // ���������, ����� ����� ����� ������������ � ����������. 

    
/*
 * ��������� ������� ���������� ����� ����� � ������� URL
 * ���������� ����� ��� ������� null, ���� ��� �����
 */
public static function getLocale()
{
    $uri = Request::path(); //�������� URI 


    $segmentsURI = explode('/',$uri); //����� �� ����� �� ����������� "/"


    //��������� ����� �����  - ���� �� ��� ����� ��������� ������
    if (!empty($segmentsURI[0]) && in_array($segmentsURI[0], self::$languages)) {

        if ($segmentsURI[0] != self::$mainLanguage) return $segmentsURI[0]; 

    }
    return null; 
}

    /*
    * ������������� ���� ���������� � ����������� �� ����� ����� �� URL
    */
    public function handle($request, Closure $next)
    {
        $locale = self::getLocale();

        if($locale) App::setLocale($locale); 
        //���� ����� ��� - ������������� �������� ���� $mainLanguage
        else App::setLocale(self::$mainLanguage); 

        return $next($request); //���������� ������ - �������� � ��������� ���������
    }
    
}