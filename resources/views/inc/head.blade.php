@section('head')
<!DOCTYPE html>
<html lang="en" >
<head>
      <meta charset="UTF-8" />
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
      <meta property="og:locale" content="en_US"/>
      <meta property="og:type" content="website"/>
      <meta property="og:url" content=""/>
      <meta property="og:site_name" content=""/>
      <meta property="og:description" content=""/>
      <meta property="og:title" content="" />
      <meta name="description" content="" />
      <meta name="keywords" content="">
      <meta name="robots" content="index, follow, noydir">
      <meta name="format-detection" content="telephone=no">
      <link rel="canonical" href="">
      <meta name="csrf-token" content="{{ csrf_token() }}">


    <meta charset="UTF-8">
    <title>@if(isset($Film)){{$Film->name}}@endif</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">


</head>


  <body>

