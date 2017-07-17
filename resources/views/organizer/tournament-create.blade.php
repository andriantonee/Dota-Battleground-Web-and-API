@extends('organizer.main.master')

@section('title', 'Home')

@section('style')
    <link href="{{ asset('vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/organizer/league-id-tooltip.css') }}" rel="stylesheet">
    <style type="text/css">
        .text-left {
            text-align: left !important;
        }

        textarea.resize-vertical-only {
            resize: vertical;
        }
    </style>
@endsection

@section('header')
    @include('organizer.navbar.navbar')
@endsection

@section('content')
    <div id="wrapper">
        <div id="page-wrapper">
            <div class="container-fluid well well-transparent">
                <div class="row" style="border-bottom: 1px solid #e3e3e3;">
                    <div class="col-xs-12">
                        <h2 style="margin-top: 0px;color:#fff">Create New Tournament</h2>
                    </div>
                </div>
                <div class="row" style="margin-top: 5px;">
                    <div class="col-xs-12 text-center">
                        <p>Fields marked * are required</p>
                    </div>
                </div>
                <form id="form-tournament-create" style="margin-top: 25px;margin-bottom: 25px;">
                    <div class="row">
                        <div class="col-xs-offset-2 col-xs-8 alert alert-success" role="alert" style="display: none;">
                            <ul id="tournament-create-alert-container">
                                <!-- All message that want to deliver to the Participant -->
                            </ul>
                        </div>
                    </div>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="tournament-name" class="col-xs-offset-2 col-xs-3 control-label text-left">Tournament Name*</label>
                            <div class="col-xs-5">
                                <input type="text" class="form-control" id="tournament-name" name="name" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tournament-description" class="col-xs-offset-2 col-xs-3 control-label text-left">Tournament Description*</label>
                            <div class="col-xs-5">
                                <textarea class="form-control resize-vertical-only" rows="4" id="tournament-description" name="description" placeholder="This is what participants will see on Overview of the tournament page" required="required"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tournament-logo" class="col-xs-offset-2 col-xs-3 control-label text-left">Logo*</label>
                            <div class="col-xs-5">
                                <input type="file" id="tournament-logo" name="logo" accept="image/jpeg, image/png" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tournament-type" class="col-xs-offset-2 col-xs-3 control-label text-left">Tournament Type*</label>
                            <div class="col-xs-5">
                                <select class="form-control" id="tournament-type" name="type" required="required">
                                    <option value disabled="disabled" selected="selected">Select a tournament type</option>
                                    <option value="1">Single Elimination</option>
                                    <option value="2">Double Elimination</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tournament-league-id" class="col-xs-offset-2 col-xs-3 control-label text-left">League ID*&nbsp;<a role="button" class="league-id-tooltip"><i class="fa fa-question-circle" aria-hidden="true"></i></a></label>
                            <div class="col-xs-2">
                                <input type="text" class="form-control" id="tournament-league-id" name="league_id" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tournament-city" class="col-xs-offset-2 col-xs-3 control-label text-left">City</label>
                            <div class="col-xs-3">
                                <select class="form-control" id="tournament-city" name="city">
                                    <option value selected="selected">Select a City</option>
                                    @foreach ($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tournament-address" class="col-xs-offset-2 col-xs-3 control-label text-left">Address</label>
                            <div class="col-xs-5">
                                <input type="text" class="form-control" id="tournament-address" name="address">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="max-participant" class="col-xs-offset-2 col-xs-3 control-label text-left">Max Participant*</label>
                            <div class="col-xs-2">
                                <input type="number" class="form-control" id="max-participant" name="max_participant" min="2" max="256" step="1" value="2" required="required">
                            </div>
                            <div class="col-xs-1">
                                <label class="control-label text-left" style="font-weight: normal;">Teams</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="team-size" class="col-xs-offset-2 col-xs-3 control-label text-left">Team Size</label>
                            <div class="col-xs-2">
                                <select class="form-control" id="team-size" name="team_size">
                                    <option value="5" selected="selected">5 VS 5</option>
                                    <option value="1">1 VS 1</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="rules" class="col-xs-offset-2 col-xs-3 control-label text-left">Tournament Rules*</label>
                            <div class="col-xs-5">
                                <textarea class="form-control resize-vertical-only" rows="6" id="rules" name="rules" required="required"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-offset-2 col-xs-3 control-label text-left">Prize</label>
                            <div class="col-xs-5">
                                <input type="text" class="form-control" id="tournament-prize-1st" name="prize_1st" placeholder="1st">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-offset-5 col-xs-5">
                                <input type="text" class="form-control" id="tournament-prize-2nd" name="prize_2nd" placeholder="2nd">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-offset-5 col-xs-5">
                                <input type="text" class="form-control" id="tournament-prize-3rd" name="prize_3rd" placeholder="3rd">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-offset-5 col-xs-5">
                                <textarea class="form-control resize-vertical-only" rows="4" id="tournament-prize-other" name="prize_other" placeholder="Other Prize"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tournament-entry-fee" class="col-xs-offset-2 col-xs-3 control-label text-left">Entry Fee*</label>
                            <div class="col-xs-2">
                                <input type="number" class="form-control" id="tournament-entry-fee" name="entry_fee" min="1" max="999999" step="1" required="required">
                            </div>
                        </div>
                        <div class="form-group"{{--  style="margin-bottom: 0px;" --}}>
                            <label for="tournament-registration-closed" class="col-xs-offset-2 col-xs-3 control-label text-left">Registration Closed*</label>
                            <div class="col-xs-3">
                                <div class="input-group date" id="registration-closed-datetimepicker">
                                    <input type="text" class="form-control" id="tournament-registration-closed" name="registration_closed" required="required">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="form-group">
                            <div class="col-xs-offset-5 col-xs-5">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="ckbox-tournament-upload-identification-card" name="upload_identification_card" value="1"> Identification Card must be uploaded on registration
                                    </label>
                                </div>
                            </div>
                        </div> --}}
                        <div class="form-group">
                            <label for="tournament-start-date" class="col-xs-offset-2 col-xs-3 control-label text-left">Start Date*</label>
                            <div class="col-xs-2">
                                <div class="input-group date" id="start-date-datetimepicker">
                                    <input type="text" class="form-control" id="tournament-start-date" name="start_date" required="required">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tournament-end-date" class="col-xs-offset-2 col-xs-3 control-label text-left">End Date*</label>
                            <div class="col-xs-2">
                                <div class="input-group date" id="end-date-datetimepicker">
                                    <input type="text" class="form-control" id="tournament-end-date" name="end_date" required="required">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            <button type="submit" id="btn-tournament-create" class="btn btn-default btn-custom ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9">
                                <span class="ladda-label">Create</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection

@section('script')
    <script src="{{ asset('vendor/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('js/organizer/tournament-create.js') }}"></script>
    <script src="{{ asset('js/organizer/league-id-tooltip.js') }}"></script>
@endsection
