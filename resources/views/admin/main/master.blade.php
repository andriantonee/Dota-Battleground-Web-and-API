<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- The above 2 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <!-- Note there is no responsive meta tag here -->
        <title>Dota Battleground - @yield('title')</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}">

        <!-- Bootstrap -->
        <link href="{{ asset('vendor/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('vendor/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
        <link href="{{ asset('vendor/sweetalert/dist/sweetalert.css') }}" rel="stylesheet">
        <link href="{{ asset('vendor/ladda-bootstrap/dist/ladda-themeless.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/non-responsive.css') }}" rel="stylesheet">
        <link href="{{ asset('css/participant/modify-button.css') }}" rel="stylesheet">
        <link href="{{ asset('css/participant/modify-well.css') }}" rel="stylesheet">
        <link href="{{ asset('css/participant/modify-icon.css') }}" rel="stylesheet">
        <link href="{{ asset('css/participant/header.css') }}" rel="stylesheet">
        <link href="{{ asset('css/participant/modify-table.css') }}" rel="stylesheet">
        <style type="text/css">
            .divider-vertical {
                height: 30px;
                margin: 10px 2px;
                border-right: 1px solid #FFF;
            }
            body{
                color: #D8D8D8;
                background-color: #272A33;
            }
        </style>
        @yield('style')

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        @yield('header')

        @yield('content')

        @yield('footer')

        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="{{ asset('vendor/jquery/dist/jquery.min.js') }}"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="{{ asset('vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('vendor/sweetalert/dist/sweetalert.min.js') }}"></script>
        <script src="{{ asset('vendor/ladda-bootstrap/dist/spin.min.js') }}"></script>
        <script src="{{ asset('vendor/ladda-bootstrap/dist/ladda.min.js') }}"></script>
        <script src="{{ asset('js/admin/config.js') }}"></script>
        @yield('script')
    </body>
</html>