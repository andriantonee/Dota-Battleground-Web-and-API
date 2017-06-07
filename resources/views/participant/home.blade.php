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
        .footer-ul > li > a:hover {
            color: blue;
            text-decoration: none;
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
        <header class="jumbotron hero-spacer" style=" background-image: url('{{ asset('img/carousel_image.jpg') }}');opacity:0.5;height:400px;margin-bottom:0">
            <h1>Dota Battleground</h1>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa, ipsam, eligendi, in quo sunt possimus non incidunt odit vero aliquid similique quaerat nam nobis illo aspernatur vitae fugiat numquam repellat.</p>
        </header>

        <!-- Page Features -->
        <div class="row text-center" style="background:#0c0c0c;padding-top:20px;padding-bottom:20px;">
            <div class="col-xs-offset-1 col-xs-4">
                <div class="thumbnail" style="background:transparent; border:none;margin:0">
                    <img src="http://placehold.it/150x150" alt="">
                    <div class="caption">
                        <h4 style="color:#fff">DO YOU ORGANIZE TOURNAMENTS?</h4>
                        <p style="color:#9d9d9d">Create and manage your own tournaments for free</p>
                        <p>
                            <a href="#" class="btn btn-default btn-custom">Start Now!</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-xs-offset-2 col-xs-4">
                <div class="thumbnail" style="background:transparent; border:none;margin:0">
                    <img src="http://placehold.it/150x150" alt="">
                    <div class="caption">
                        <h4 style="color:#fff">LOOKING FOR TOURNAMENTS?</h4>
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
            <div class="row text-center" style="background-color: #272A33;border-top: 1px solid #5f6471;border-bottom: 1px solid #5f6471;padding-bottom: 10px;">
                <h2>WHAT YOU CAN DO ON DOTA BATTLEGROUND</h2>
                <div class="col-xs-offset-4 col-xs-4" style="padding-left: 20px;">                
                    <ul style="text-align: left;">
                        <li>Bla Bla Bla Bla</li>
                        <li>Bla Bla Bla Bla</li>
                        <li>Bla Bla Bla Bla</li>
                        <li>Bla Bla Bla Bla</li>
                        <li>Bla Bla Bla Bla</li>
                        <li>Bla Bla Bla Bla</li>
                    </ul>
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
                    <img src="img/google-play-store.png" style="width: 200px;">
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
