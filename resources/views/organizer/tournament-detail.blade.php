@extends('organizer.main.master')

@section('title', 'Home')

@section('style')
    <link href="{{ asset('vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/tab-pages.css') }}" rel="stylesheet">
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
            <div class="container-fluid">
                <div class="row" style="border-bottom: 1px solid black;">
                    <div class="col-xs-6">
                        <h2 style="margin-top: 0px;">{{ $tournament->name }}</h2>
                    </div>
                    <div class="col-xs-6 text-right">
                        @if ($tournament->approval)
                            @if ($tournament->approval->accepted == 1)
                                @if (date('Y-m-d H:i:s') <= $tournament->registration_closed)
                                    <h4>Status : Registration Open</h4>
                                @else
                                    <h4>Status : Registration Closed</h4>
                                @endif
                            @elseif ($tournament->approval->accepted == 0)
                                <h4>Status : Rejected</h4>
                            @endif
                        @else
                            <h4>Status : Pending</h4>
                        @endif
                    </div>
                </div>
                <div style="margin-top: 25px;margin-bottom: 25px;">
                    <div class="panel with-nav-tabs panel-default" style="border: none;">
                        <div class="panel-heading" style="background-color: transparent;border-color: #000000;">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#Settings-tab" data-toggle="tab">Settings</a></li>
                                <li><a href="#participants-tab" data-toggle="tab">Participants</a></li>
                                <li><a href="#brackets-tab" data-toggle="tab">Brackets</a></li>
                                <li><a href="#schedule-tab" data-toggle="tab">Schedule</a></li>
                                <li><a href="#report-match-tab" data-toggle="tab">Report Match</a></li>
                                <li><a href="#live-match-tab" data-toggle="tab">Live Match</a></li>
                            </ul>
                        </div>
                        <div class="panel-body" style="border: 1px solid #000000;border-top: none;">
                            <div class="tab-content">
                                <div class="tab-pane fade in active" id="Settings-tab">
                                    <div class="row">
                                        <div class="col-xs-offset-2 col-xs-8 alert alert-success" role="alert" style="display: none;">
                                            <ul id="tournament-settings-alert-container">
                                                <!-- All message that want to deliver to the Participant -->
                                            </ul>
                                        </div>
                                    </div>
                                    <form id="form-tournament-settings" class="form-horizontal">
                                        <div class="form-group">
                                            <label for="tournament-name" class="col-xs-offset-2 col-xs-3 control-label text-left">Tournament Name</label>
                                            <div class="col-xs-5">
                                                <input type="text" class="form-control" id="tournament-name" name="name" value="{{ $tournament->name }}" disabled="disabled">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="tournament-description" class="col-xs-offset-2 col-xs-3 control-label text-left">Tournament Description</label>
                                            <div class="col-xs-5">
                                                <textarea class="form-control resize-vertical-only" rows="4" id="tournament-description" name="description" placeholder="This is what participants will see on Overview of the tournament page" required="required">{{ $tournament->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="tournament-logo" class="col-xs-offset-2 col-xs-3 control-label text-left">Logo</label>
                                            <div class="col-xs-5">
                                                <img src="{{ asset('/storage/tournament/'.$tournament->logo_file_name) }}" style="width: 100px;height: 100px;border: 1px solid black;">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="tournament-league-id" class="col-xs-offset-2 col-xs-3 control-label text-left">League ID&nbsp;<i class="fa fa-question-circle" aria-hidden="true"></i></label>
                                            <div class="col-xs-2">
                                                <input type="text" class="form-control" id="tournament-league-id" name="league_id" value="{{ $tournament->leagues_id }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="tournament-city" class="col-xs-offset-2 col-xs-3 control-label text-left">City</label>
                                            <div class="col-xs-3">
                                                <select class="form-control" id="tournament-city" name="city">
                                                    @if ($tournament->cities_id)
                                                        <option value>Select a City</option>
                                                    @else
                                                        <option value selected="selected">Select a City</option>
                                                    @endif
                                                    @foreach ($cities as $city)
                                                        @if ($city->id == $tournament->cities_id)
                                                            <option value="{{ $city->id }}" selected="selected">{{ $city->name }}</option>
                                                        @else
                                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="tournament-address" class="col-xs-offset-2 col-xs-3 control-label text-left">Address</label>
                                            <div class="col-xs-5">
                                                <input type="text" class="form-control" id="tournament-address" name="address" value="{{ $tournament->address }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="max-participant" class="col-xs-offset-2 col-xs-3 control-label text-left">Max Participant</label>
                                            <div class="col-xs-2">
                                                <input type="number" class="form-control" id="max-participant" name="max_participant" min="3" max="256" step="1" value="{{ $tournament->max_participant }}" disabled="disabled">
                                            </div>
                                            <div class="col-xs-1">
                                                <label class="control-label text-left" style="font-weight: normal;">Teams</label>
                                            </div>
                                        </div>
                                        {{-- <div class="form-group">
                                            <label for="team-size" class="col-xs-offset-2 col-xs-3 control-label text-left">Team Size</label>
                                            <div class="col-xs-3">
                                                <select class="form-control" id="team-size" name="team_size">
                                                    <option value="5" selected="selected">5 Vs 5</option>
                                                </select>
                                            </div>
                                        </div> --}}
                                        <div class="form-group">
                                            <label for="rules" class="col-xs-offset-2 col-xs-3 control-label text-left">Tournament Rules</label>
                                            <div class="col-xs-5">
                                                <textarea class="form-control resize-vertical-only" rows="6" id="rules" name="rules" disabled="disabled">{{ $tournament->rules }}</textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-xs-offset-2 col-xs-3 control-label text-left">Prize</label>
                                            <div class="col-xs-5">
                                                <input type="text" class="form-control" id="tournament-prize-1st" name="prize_1st" placeholder="1st" value="{{ $tournament->prize_1st }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-xs-offset-5 col-xs-5">
                                                <input type="text" class="form-control" id="tournament-prize-2nd" name="prize_2nd" placeholder="2nd" value="{{ $tournament->prize_2nd }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-xs-offset-5 col-xs-5">
                                                <input type="text" class="form-control" id="tournament-prize-3rd" name="prize_3rd" placeholder="3rd" value="{{ $tournament->prize_3rd }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-xs-offset-5 col-xs-5">
                                                <textarea class="form-control resize-vertical-only" rows="4" id="tournament-prize-other" name="prize_other" placeholder="Other Prize">{{ $tournament->prize_other }}</textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="tournament-entry-fee" class="col-xs-offset-2 col-xs-3 control-label text-left">Entry Fee</label>
                                            <div class="col-xs-2">
                                                <input type="number" class="form-control" id="tournament-entry-fee" name="entry_fee" min="1" max="999999" step="1" value="{{ $tournament->entry_fee }}" disabled="disabled">
                                            </div>
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0px;">
                                            <label for="tournament-registration-closed" class="col-xs-offset-2 col-xs-3 control-label text-left">Registration Closed</label>
                                            <div class="col-xs-3">
                                                <div class="input-group date" id="registration-closed-datetimepicker">
                                                    <input type="text" class="form-control" id="tournament-registration-closed" name="registration_closed" value="{{ date('d/m/Y H:i', strtotime($tournament->registration_closed)) }}" disabled="disabled">
                                                    <span class="input-group-addon" style="cursor: not-allowed;">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-xs-offset-5 col-xs-5">
                                                <div class="checkbox disabled">
                                                    <label>
                                                        @if ($tournament->need_identifications == 1)
                                                            <input type="checkbox" id="ckbox-tournament-upload-identification-card" name="upload_identification_card" value="1" checked="checked" disabled="disabled"> Identification Card must be uploaded on registration
                                                        @else
                                                            <input type="checkbox" id="ckbox-tournament-upload-identification-card" name="upload_identification_card" value="1" disabled="disabled"> Identification Card must be uploaded on registration
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="tournament-start-date" class="col-xs-offset-2 col-xs-3 control-label text-left">Start Date</label>
                                            <div class="col-xs-2">
                                                <div class="input-group date" id="start-date-datetimepicker">
                                                    <input type="text" class="form-control" id="tournament-start-date" name="start_date" value="{{ date('d/m/Y', strtotime($tournament->start_date)) }}" disabled="disabled">
                                                    <span class="input-group-addon" style="cursor: not-allowed;">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="tournament-end-date" class="col-xs-offset-2 col-xs-3 control-label text-left">End Date</label>
                                            <div class="col-xs-2">
                                                <div class="input-group date" id="end-date-datetimepicker">
                                                    <input type="text" class="form-control" id="tournament-end-date" name="end_date" value="{{ date('d/m/Y', strtotime($tournament->end_date)) }}" disabled="disabled">
                                                    <span class="input-group-addon" style="cursor: not-allowed;">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <div class="col-xs-offset-5 col-xs-5 text-right">
                                                <button type="submit" id="btn-tournament-settings" class="btn btn-default ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9">
                                                    <span class="ladda-label">Update</span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="participants-tab" style="min-height: 600px;">
                                    Default 2
                                </div>
                                <div class="tab-pane fade" id="brackets-tab" style="min-height: 600px;">
                                    <form id="form-tournament-brackets" class="form-horizontal">
                                        <div class="form-group">
                                            <label for="tournament-type" class="col-xs-offset-2 col-xs-3 control-label text-left">Tournament Type</label>
                                            <div class="col-xs-5">
                                                <select class="form-control" id="tournament-type" name="type" required="required">
                                                    @if ($tournament->type == 1)
                                                        <option value="1" selected="selected">Single Elimination</option>
                                                        <option value="2">Double Elimination</option>
                                                    @elseif ($tournament->type == 2)
                                                        <option value="1">Single Elimination</option>
                                                        <option value="2" selected="selected">Double Elimination</option>
                                                    @else
                                                        <option value="1">Single Elimination</option>
                                                        <option value="2">Double Elimination</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <div class="col-xs-offset-5 col-xs-5 text-right">
                                                <button type="submit" id="btn-tournament-brackets" class="btn btn-default ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9">
                                                    <span class="ladda-label">Generate</span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <h2 style="border-bottom: 1px solid black;margin-bottom: 15px;padding-bottom: 10px;padding-left: 25px;">Preview</h2>
                                    <div style="width: 100%;text-align: center;">
                                        <iframe src="http://challonge.com/{{ $tournament->challonges_url }}/module" width="90%" height="500" frameborder="0" scrolling="auto" allowtransparency="true"></iframe>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="schedule-tab" style="min-height: 600px;">
                                    Default 4
                                </div>
                                <div class="tab-pane fade" id="report-match-tab" style="min-height: 600px;">
                                    Default 5
                                </div>
                                <div class="tab-pane fade" id="live-match-tab" style="min-height: 600px;">
                                    Default 6
                                </div>
                            </div>
                        </div>
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
    <script src="{{ asset('js/organizer/tournament-detail.js') }}"></script>
@endsection
