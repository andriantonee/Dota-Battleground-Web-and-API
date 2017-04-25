@extends('participant.main.master')

@section('title', 'Home')

@section('style')
    <link href="{{ asset('vendor/bootstrap-social/bootstrap-social.css') }}" rel="stylesheet">
    <style type="text/css">
        footer {
            background-color: #565656;
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
            color: white;
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
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <header class="jumbotron hero-spacer">
            <h1>A Warm Welcome!</h1>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa, ipsam, eligendi, in quo sunt possimus non incidunt odit vero aliquid similique quaerat nam nobis illo aspernatur vitae fugiat numquam repellat.</p>
            <p><a class="btn btn-primary btn-large">Call to action!</a>
            </p>
        </header>

        <!-- Page Features -->
        <div class="row text-center">
            <div class="col-xs-offset-1 col-xs-4">
                <div class="thumbnail">
                    <img src="http://placehold.it/800x500" alt="">
                    <div class="caption">
                        <h3>Feature Label</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit.</p>
                        <p>
                            <a href="#" class="btn btn-primary">Buy Now!</a> <a href="#" class="btn btn-default">More Info</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-xs-offset-2 col-xs-4">
                <div class="thumbnail">
                    <img src="http://placehold.it/800x500" alt="">
                    <div class="caption">
                        <h3>Feature Label</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit.</p>
                        <p>
                            <a href="#" class="btn btn-primary">Buy Now!</a> <a href="#" class="btn btn-default">More Info</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <footer>
        <div class="container-fluid">
            <div class="row text-center" style="background-color: #eeeeee;border-top: 3px solid black;border-bottom: 3px solid black;padding-bottom: 10px;">
                <h1>What you can do on Blablaala</h1>
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
            <div class="row" style="padding: 20px 0px;">
                <div class="col-xs-offset-1 col-xs-3">
                    <ul class="footer-ul">
                        <li><a href="#"> About Us</a></li>
                        <li><a href="#"> Privacy Policy</a></li>
                        <li><a href="#"> Terms and Conditions</a></li>
                        <li><a href="#"> Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-xs-4">
                    <p style="color: white;">Download Aplikasi Dota 2 Battleground</p>
                    <img src="img/google-play-store.png" style="width: 200px;">
                </div>
                <div class="col-xs-3">
                    <p style="color: white;">Follow Us</p>
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
    <div class="container-fluid" style="min-height: 30px;background-color: black;">
        <div class="col-xs-12">
            <p style="text-align: center;color: white;padding: 5px 0px;margin-bottom: 0px;">© Dota Battleground - Portal Turnamen Dota 2</p>
        </div>
    </div>
@endsection

@section('script')
@endsection
