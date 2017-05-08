@extends('organizer.main.master')

@section('title', 'Home')

@section('style')
    <link href="{{ asset('vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <style type="text/css">
        .text-left {
            text-align: left !important;
        }
    </style>
@endsection

@section('header')
    @include('organizer.navbar.navbar')
@endsection

@section('content')
    <div id="wrapper">
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row" style="border-bottom: 1px solid black;">
                    <div class="col-xs-12">
                        <h2 style="margin-top: 0px;">Create New Tournament</h2>
                    </div>
                </div>
                <div class="row" style="margin-top: 5px;">
                    <div class="col-xs-12 text-center">
                        <p>Fields marked * are required</p>
                    </div>
                </div>
                <div style="margin-top: 25px;margin-bottom: 25px;">
                    <form class="form-horizontal">
                        <div class="form-group">
                            <label for="name" class="col-xs-offset-2 col-xs-3 control-label text-left">Tournament Name*</label>
                            <div class="col-xs-5">
                                <input type="text" class="form-control" id="name" name="name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description" class="col-xs-offset-2 col-xs-3 control-label text-left">Tournament Description*</label>
                            <div class="col-xs-5">
                                <textarea class="form-control" rows="4" id="description" name="description" placeholder="This is what participants will see on Overview of the tournament page"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="logo" class="col-xs-offset-2 col-xs-3 control-label text-left">Logo*</label>
                            <div class="col-xs-5">
                                <input type="file" class="form-control" id="logo" name="logo">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="league-id" class="col-xs-offset-2 col-xs-3 control-label text-left">League Id&nbsp;<i class="fa fa-question-circle" aria-hidden="true"></i></label>
                            <div class="col-xs-3">
                                <input type="text" class="form-control" id="league-id" name="league_id">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="max-participant" class="col-xs-offset-2 col-xs-3 control-label text-left">Max Participant*</label>
                            <div class="col-xs-1">
                                <input type="number" class="form-control" id="max-participant" name="max_participant" min="3" step="1" value="3">
                            </div>
                            <div class="col-xs-1">
                                <label class="control-label text-left" style="font-weight: normal;">Teams</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="team-size" class="col-xs-offset-2 col-xs-3 control-label text-left">Team Size</label>
                            <div class="col-xs-3">
                                <select class="form-control" id="team-size" name="team_size">
                                    <option value="5" selected="selected">5 Vs 5</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="rules" class="col-xs-offset-2 col-xs-3 control-label text-left">Tournament Rules</label>
                            <div class="col-xs-5">
                                <textarea class="form-control" rows="6" id="rules" name="rules"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="prize" class="col-xs-offset-2 col-xs-3 control-label text-left">Prize</label>
                            <div class="col-xs-5">
                                <textarea class="form-control" rows="6" id="prize" name="prize"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="entry-fee" class="col-xs-offset-2 col-xs-3 control-label text-left">Entry Fee*</label>
                            <div class="col-xs-2">
                                <input type="text" class="form-control" id="entry-fee" name="entry_fee">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0px;">
                            <label for="registration-closed" class="col-xs-offset-2 col-xs-3 control-label text-left">Registration Closed*</label>
                            <div class="col-xs-2">
                                <div class="input-group date" id="registration-closed-datetimepicker">
                                    <input type="text" class="form-control" id="registration-closed" name="registration_closed">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-offset-5 col-xs-5">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="upload_identification_card" value="1"> Identification Card must be uploaded on registration
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="start-date" class="col-xs-offset-2 col-xs-3 control-label text-left">Start Date*</label>
                            <div class="col-xs-2">
                                <div class="input-group date" id="start-date-datetimepicker">
                                    <input type="text" class="form-control" id="start-date" name="start_date">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end-date" class="col-xs-offset-2 col-xs-3 control-label text-left">End Date*</label>
                            <div class="col-xs-2">
                                <div class="input-group date" id="end-date-datetimepicker">
                                    <input type="text" class="form-control" id="end-date" name="end_date">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <button class="btn btn-default">
                            Create New Tournament
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection

@section('script')
    <script src="{{ asset('vendor/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script type="text/javascript">
        $(function () {
            $("#registration-closed-datetimepicker").datetimepicker();
            $("#start-date-datetimepicker").datetimepicker();
            $("#end-date-datetimepicker").datetimepicker();
        });
    </script>
@endsection
