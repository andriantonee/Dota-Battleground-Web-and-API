@extends('organizer.main.master')

@section('title', 'Home')

@section('style')
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
@endsection

@section('header')
    @include('organizer.navbar.navbar')
@endsection

@section('content')
    <div id="wrapper">
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row" style="border-bottom: 1px solid black;">
                    <div class="col-xs-4">
                        <h2 style="margin-top: 0px;">Change Password</h2>
                    </div>
                </div>
                <div style="margin-top: 25px;">
                    <div class="row">
                        <form id="form-organizer-password" class="col-xs-12">
                            <div style="width: 313px;margin-left: auto;margin-right: auto;">
                                <div class="alert alert-success" role="alert" style="margin-left: 5px;margin-right: 5px;display: none;">
                                    <ul id="password-alert-container">
                                        <!-- All message that want to deliver to the user -->
                                    </ul>
                                </div>
                                <div class="form-inline" style="margin-bottom: 10px;">
                                    <div class="form-group">
                                        <label for="old-password" style="margin-right: 15px;"><i class="fa fa-key" aria-hidden="true"></i></label>
                                        <input type="password" class="form-control" id="old-password" name="old_password" placeholder="Old Password" style="width: 280px;">
                                    </div>
                                </div>
                                <div class="form-inline" style="margin-bottom: 10px;">
                                    <div class="form-group">
                                        <label for="new-password" style="margin-right: 15px;"><i class="fa fa-key" aria-hidden="true"></i></label>
                                        <input type="password" class="form-control" id="new-password" name="new_password" placeholder="New Password" style="width: 280px;">
                                    </div>
                                </div>
                                <div class="form-inline" style="margin-bottom: 10px;">
                                    <div class="form-group">
                                        <label for="new-password-confirmation" style="margin-right: 15px;"><i class="fa fa-key" aria-hidden="true"></i></label>
                                        <input type="password" class="form-control" id="new-password-confirmation" name="new_password_confirmation" placeholder="New Password Confirmation" style="width: 280px;">
                                    </div>
                                </div>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-default ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9" id="btn-organizer-password">
                                        <span class="ladda-label">Change</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection

@section('script')
    <script src="{{ asset('js/organizer/password.js') }}"></script>
@endsection
