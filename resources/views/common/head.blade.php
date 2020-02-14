<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Most of search - Sigma</title>
<meta name="description" content="">
<meta name="keywords" content="elasticseach,cloud,infastructure,sigma,php">
<meta name="author" content="nicoorfi@yahoo.com">

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- CSRF Token -->
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/apple-touch-icon.png')}}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/favicon-32x32.png')}}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/favicon-16x16.png"')}}">
<link rel=" manifest" href="{{ asset('/manifest.json')}}">

<title>{{ config('app.name', 'Laravel') }}</title>

<style>
    .gradient {
        background: linear-gradient(90deg, #d53369 0%, #daae51 100%);
    }
</style>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

<!-- Styles -->
<link href="{{ asset('css/app.css') }}" rel="stylesheet">

@yield('additional-js')

@yield('additional-css')
