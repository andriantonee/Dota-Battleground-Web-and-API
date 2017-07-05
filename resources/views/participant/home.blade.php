@extends('participant.main.master')

@section('title', 'Home')

@section('style')
    <link href="{{ asset('vendor/bootstrap-social/bootstrap-social.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/search-input.css') }}" rel="stylesheet">
    <style type="text/css">
        footer {
            min-width: 1024px !important;
        }

        .footer-ul {
            list-style-type: none;
            margin-bottom: 0px;
        }
        .footer-ul > li {
            margin: 3px 0px;
        }
        .footer-ul > li > a {
            color: #9d9d9d;

        }

        .copyright {
            min-height: 40px;
            background-color: #000000;
        }
        .copyright p {
            text-align: center;
            color: #FFF;
            padding: 10px 0;
            margin-bottom: 0px;
        }
        .navbar{
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid" style="padding:0;">
<!--         <header class="jumbotron hero-spacer" style=" background-image: url('{{ asset('img/carousel_image2.jpg') }}');opacity:0.5;height:400px;margin-bottom:0">
            <div>
                <h1>Dota Battleground</h1>
                <h2>The Easiest Way to Manage Your Tournament</h2>
            </div>
            
        </header> -->

        <div class="carousel">
            <div style="position:absolute;text-shadow: 0 1px 2px rgba(0,0,0,.6);z-index: 10;left:5%;top:20%;">
                <h1 style="color:rgb(255, 90, 25);font-size: 63px;">Dota Battleground</h1>
                <!-- <h2>The Easiest Way to Manage Your DOTA 2 Tournament</h2> -->
                <h2 style="font-size: 33px;">Find, Compete, and Win the DOTA 2 Tournament</h2>
            </div>
            <div class="carousel-inner">
                <img src="{{ asset('img/carousel_image2.jpg') }}" width="100%" style="opacity:0.3;">
            </div>
        </div>
        <!-- Page Features -->
        <div class="row text-center" style="background-color: #272A33;padding-top:20px;padding-bottom:20px;border-top: 1px solid #5f6471;margin-left: 0;margin-right: 0;">
            <div class="col-xs-offset-1 col-xs-4">
                <div class="thumbnail" style="background:transparent; border:none;margin:0">
                    <img src="{{ asset('img/organize_icon.png') }}" alt="">
                    <div class="caption">
                        <h4 style="color:rgb(255, 90, 25)">DO YOU ORGANIZE TOURNAMENTS?</h4>
                        <p style="color:#9d9d9d">Create and manage your own tournaments for free</p>
                        <p>
                            <a href="#" class="btn btn-default btn-custom">Start Now!</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-xs-offset-2 col-xs-4">
                <div class="thumbnail" style="background:transparent; border:none;margin:0">
                    <img src="{{ asset('img/searching_tournament_icon.png') }}" alt="">
                    <div class="caption">
                        <h4 style="color:rgb(255, 90, 25)">LOOKING FOR TOURNAMENTS?</h4>
                        <p style="color:#9d9d9d">Look for your tournament to start compete and win the prize</p>
                        <div class="input-group stylish-input-group">
                            <span class="input-group-addon">
                                <i class="glyphicon glyphicon-search"></i>
                            </span>
                            <input type="text" id="txtbox-search-team" class="form-control" placeholder="Search name..." >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <footer>
        <div class="container-fluid">
            <div class="row" style="background-color:  #0c0c0c;border-top: 1px solid #5f6471;border-bottom: 1px solid #5f6471;padding-bottom: 10px;">
                 <div class="col-xs-4  col-xs-offset-1">
                     <img src="{{ asset('img/feature-live.png') }}" alt="" style="margin-top:10px;height:380px">
                 </div>
                 <div class="col-xs-6 col-xs-offset-1" style="margin-top:60px;">
                     <h2 style="color:rgb(255, 90, 25)">View Statistic on a Live Match</h2>
                     <h4 style="color:#9d9d9d;padding-top:10px;padding-bottom:20px;">Follow your favorite team by viewing their statistic match LIVE</h4>
                 </div>
            </div>

            <div class="row" style="background-color: #272A33;border-bottom: 1px solid #5f6471;padding-bottom: 10px;">
                 <div class="col-xs-6 col-xs-offset-1" style="margin-top:60px;">
                     <h2 style="color:rgb(255, 90, 25)">Dota Battleground Come Handy in Mobile</h2>
                     <h4 style="color:#9d9d9d;padding-top:10px;padding-bottom:20px;">You can get your schedule notification on your mobile phone</h4>
                     <a href="" class=""><img src="img/google-play-store.png" style="width: 200px;"></a>
                 </div>
                 <div class="col-xs-4  col-xs-offset-1">
                     <img src="{{ asset('img/feature-smartphone.png') }}" alt="" style="margin-top:10px;height:400px;">
                 </div>
            </div>
            <div class="row" style="padding: 20px 0px;background:#0c0c0c;">
                <div class="col-xs-offset-1 col-xs-3">
                    <ul class="footer-ul">
                        <li><a href="#"> About Us</a></li>
                        <li><a href="#"> Privacy Policy</a></li>
                        <li><a href="#"> Terms and Conditions</a></li>
                        <li><a href="#"> Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-xs-4">
                    <p>Download Aplikasi Dota 2 Battleground</p>
                    <a href="" class=""><img src="img/google-play-store.png" style="width: 200px;"></a>
                </div>
                <div class="col-xs-3">
                    <p>Follow Us</p>
                    <a class="btn btn-social-icon btn-lg btn-facebook" style="border-radius: 0px;">
                        <span class="fa fa-facebook"></span>
                    </a>
                    <a class="btn btn-social-icon btn-lg btn-instagram" style="border-radius: 0px;">
                        <span class="fa fa-instagram"></span>
                    </a>
                    <a class="btn btn-social-icon btn-lg btn-twitter" style="border-radius: 0px;">
                        <span class="fa fa-twitter"></span>
                    </a>
                </div>
            </div>
        </div>
    </footer>
    <div class="container-fluid" style="min-height: 30px;background:#0c0c0c;">
        <div class="col-xs-12">
            <p style="text-align: center;padding: 5px 0px;margin-bottom: 0px;">© Dota Battleground - Portal Turnamen Dota 2</p>
        </div>
    </div>
@endsection

@section('script')
@endsection
