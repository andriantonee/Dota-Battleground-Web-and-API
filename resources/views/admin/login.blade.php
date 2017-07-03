@extends('admin.main.master')

@section('title', 'Admin Login')

@section('style')
    <style type="text/css">
        html, body {
            height: 100%;
        }
        body {
            min-width: 1024px;
        }

        .form-inline:first-child {
            margin-bottom: 10px;
        }
        .form-inline+.form-inline {
            margin-bottom: 10px;
        }

        .container-login-table {
            display: table;
            height: 100%;
            margin-left: auto;
            margin-right: auto;
        }
        .container-login-row {
            display: table-row;
        }
        .container-login-cell {
            display: table-cell;
            vertical-align: middle;
        }
        .container-login {
            border: 1px solid #737373;
            padding: 15px;
        }
        .container-login-header {
            border-bottom: 1px solid #737373;
            padding: 0px 15px;
        }
        .container-login-header-img {
            display: inline-block;
            margin-top: 10px;
            padding: 5px;
            vertical-align: top;
        }
        .container-login-header-text {
            display: inline-block;
            margin-left: 15px;
        }
        .container-login-body {
            margin: 25px 0;
            padding: 0 15px;
        }
        .container-login-footer {
            padding: 0 15px;
        }
        .container-login-footer > button {
            width: 100%;
        }

        .login-label {
            margin-right: 15px;
        }
        .login-label > i.fa-user {
            margin: 0 2px;
        }

        .login-text {
            width: 314px !important;
        }
    </style>
@endsection

@section('header')
@endsection

@section('content')
    <div class="container-login-table">
        <div class="container-login-row">
            <div class="container-login-cell">
                <div class="alert alert-success" role="alert" style="display: none;">
                    <ul id="login-alert-container">
                        <!-- All message that want to deliver to the user -->
                    </ul>
                </div>
                <div class="container-login well-transparent">
                    <div class="container-login-header">
                        <div class="container-login-header-img">
                            <img src="{{ asset('/img/logo.png') }}">
                        </div>
                        <div class="container-login-header-text">
                            <h4>Dota Battleground</h4>
                            <h5>Sign In as Administrator</h5>
                        </div>
                    </div>
                    <form id="form-admin-login">
                        <div class="container-login-body">
                            <div class="form-inline">
                                <div class="form-group">
                                    <label for="sign-in-email" class="login-label"><i class="fa fa-user" aria-hidden="true"></i></label>
                                    <input type="email" class="form-control login-text" id="sign-in-email" name="email" placeholder="Email" required="required">
                                </div>
                            </div>
                            <div class="form-inline">
                                <div class="form-group">
                                    <label for="sign-in-password" class="login-label"><i class="fa fa-key" aria-hidden="true"></i></label>
                                    <input type="password" class="form-control login-text" id="sign-in-password" name="password" placeholder="Password" required="required">
                                </div>
                            </div>
                        </div>
                        <div class="container-login-footer">
                            <button type="submit" id="btn-admin-login" class="btn btn-default btn-custom ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9">
                                <span class="ladda-label">Sign In</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection

@section('script')
    <script src="{{ asset('js/admin/authentication.js') }}"></script>
@endsection
