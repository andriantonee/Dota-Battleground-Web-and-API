@extends('organizer.main.master')

@section('title', 'Home')

@section('style')
    <link href="{{ asset('css/participant/footer.css') }}" rel="stylesheet">
    <style type="text/css">
        .text-left {
            text-align: left !important;
        }
        .navbar{
            margin-bottom: 0;
        }
        .feature-section{
            background:transparent;
            padding-top:20px;
            padding-bottom:20px;
        }
        .feature-section > div > div.thumbnail{
            background:transparent; border:none;margin:0
        }
        .caption > h3{
            color:#fff;
            font-size: 23px;
        }
        .caption > p {
            color:#9d9d9d;
        }
        #form-organizer-register{
            color:#e3e3e3;
            border: 1px solid #5f6471;
            background: linear-gradient(to right, #2f313a, #2f3341);
            box-shadow: 5px 5px 12px 5px rgba(0,0,0,0.1);
            padding: 15px 15px 0px 15px;
            border-radius: 10px;
        }
    </style>
@endsection

@section('header')
    @include('organizer.navbar.navbar-home')
@endsection

@section('content')
    <div class="container-fluid" style="padding:0px">
        <header class="jumbotron hero-spacer" style="background-image: url('{{ asset('img/organizer-background-1.jpg') }}');">
            <div class="row">
                <div class="col-xs-6" style="height: 270px;display: table;">
                    <div style="display: table-cell;vertical-align: middle;">
                        <h1 style="font-size: 63px;margin-top: 0;color:rgb(255, 90, 25)">Dota Battleground</h1>
                        <h2 style="margin-bottom: 0;">Managing your tournament with ease</h2>
                    </div>
                </div>
                <div class="col-xs-offset-1 col-xs-5">
                    <form id="form-organizer-register" class="form-horizontal">
                        <div class="alert alert-success" role="alert" style="margin-left: 5px;margin-right: 5px;display: none;">
                            <ul id="register-alert-container">
                                <!-- All message that want to deliver to the user -->
                            </ul>
                        </div>
                        <div class="form-group">
                            <label for="sign-up-name" class="col-xs-4 control-label text-left">Name</label>
                            <div class="col-xs-8">
                                <input type="text" class="form-control" id="sign-up-name" name="name" placeholder="Name" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sign-up-email" class="col-xs-4 control-label text-left">Email</label>
                            <div class="col-xs-8">
                                <input type="email" class="form-control" id="sign-up-email" name="email" placeholder="Email" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sign-up-password" class="col-xs-4 control-label text-left">Password</label>
                            <div class="col-xs-8">
                                <input type="password" class="form-control" id="sign-up-email" name="password" placeholder="Password" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sign-up-password-confirmation" class="col-xs-4 control-label text-left">Confirm Password</label>
                            <div class="col-xs-8">
                                <input type="password" class="form-control" id="sign-up-password-confirmation" name="password_confirmation" placeholder="Password" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-8 text-left" style="padding-top: 5px;">
                                <label>
                                    <input type="checkbox" name="agree" id="ckbox-organizer-agree"> I agree to the terms and conditions
                                </label>
                            </div>
                            <div class="col-xs-4 text-right">
                                <button type="submit" id="btn-organizer-register" class="btn btn-default btn-custom ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9" disabled="disabled">
                                    <span class="ladda-label">Sign Up</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </header>

        <!-- Page Features -->
        <div class="row text-center feature-section">
            <div class="col-xs-4">
                <div class="thumbnail">
                    <img src="{{ asset('img/feature-organizer-1.png') }}" alt="" style="height:150px">
                    <div class="caption">
                        <h3 style="color:rgb(255, 90, 25)">Free Tournament Management Software</h3>
                        <p>Free tournament management software for all organizer</p>
                    </div>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="thumbnail">
                    <img src="{{ asset('img/feature-organizer-2.png') }}" alt="" style="height:150px">
                    <div class="caption">
                        <h3 style="color:rgb(255, 90, 25)">Live Match Commenting</h3>
                        <p>Tournament's Commentator can comment and interact with the viewer on live match statistic page</p>
                    </div>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="thumbnail">
                    <img src="{{ asset('img/feature-organizer-3.png') }}" alt="" style="height:150px">
                    <div class="caption">
                        <h3 style="color:rgb(255, 90, 25)">Re-registration the participant</h3>
                        <p>Organizer can re-check or re-regist the participant at on-site tournament just by using scan feature on mobile app.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row text-center" style="background-image: url('{{ asset('img/organizer-background-2.jpg') }}');padding-top: 40px;padding-bottom:80px">
             <h2 style="color:#fff;margin-bottom:40px;">Download Dota Battleground Organizer App Now!</h2>
             <a href="" ><img src="img/google-play-store.png" style="width: 200px;"></a>
        </div>
    </div>
@endsection

@section('footer')
    @include('participant.footer.footer')
@endsection

@section('script')
    <script src="{{ asset('js/organizer/authentication.js') }}"></script>
@endsection
