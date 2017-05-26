@extends('organizer.main.master')

@section('title', 'Home')

@section('style')
    <link href="{{ asset('vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/tab-pages.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/modify-modal.css') }}" rel="stylesheet">
    <style type="text/css">
        .text-left {
            text-align: left !important;
        }

        textarea.resize-vertical-only {
            resize: vertical;
        }

        .participants-group-list-item:first-child {
            margin-bottom: 10px;
        }
        .participants-group-list-item+.participants-group-list-item {
            margin-bottom: 10px;
        }
        .participants-group-list-item:last-child {
            margin-bottom: 0px;
        }

        .table-content-centered th, .table-content-centered td {
            text-align: center;
            vertical-align: middle !important;
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
                                    @if ($tournament->start == 0)
                                        @if (count($tournament->registrations) >= 2)
                                            <h4 style="margin: 0;">
                                                Status : Registration Closed
                                                <button id="btn-tournament-start" class="btn btn-default" style="margin-left: 15px;">
                                                    <i class="fa fa-play"></i>&nbsp;&nbsp;Start
                                                </button>
                                            </h4>
                                        @else
                                            <h4 style="margin: 0;">
                                                Status : Registration Closed
                                                <button id="btn-tournament-end" class="btn btn-default" style="margin-left: 15px;">
                                                    <i class="fa fa-stop"></i>&nbsp;&nbsp;End
                                                </button>
                                            </h4>
                                        @endif
                                    @else
                                        @if ($tournament->complete == 0)
                                            <h4>Status : In Progress</h4>
                                        @else
                                            <h4>Status : Complete</h4>
                                        @endif
                                    @endif
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
                                <li class="active"><a href="#settings-tab" data-toggle="tab">Settings</a></li>
                                <li><a href="#participants-tab" data-toggle="tab">Participants</a></li>
                                <li><a href="#brackets-tab" data-toggle="tab">Brackets</a></li>
                                @if ($tournament->start && count($tournament->registrations) >= 2)
                                    <li><a href="#schedule-tab" data-toggle="tab">Schedule</a></li>
                                    <li><a href="#report-match-tab" data-toggle="tab">Report Match</a></li>
                                    <li><a href="#live-match-tab" data-toggle="tab">Live Match</a></li>
                                @endif
                            </ul>
                        </div>
                        <div class="panel-body" style="border: 1px solid #000000;border-top: none;">
                            <div class="tab-content">
                                <div class="tab-pane fade in active" id="settings-tab">
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
                                <div class="tab-pane fade" id="participants-tab">
                                    <h2 style="border-bottom: 1px solid black;margin: 0;padding-bottom: 10px;">Registered <span style="float: right;"><i class="fa fa-users" aria-hidden="true"></i>&nbsp;{{ count($tournament->registrations) }}/{{ $tournament->max_participant }}</span></h2>
                                    @if (count($tournament->registrations) > 0)
                                        <div class="participants-group-list" style="margin-top: 15px;">
                                            @foreach ($tournament->registrations as $registration)
                                                <div class="participants-group-list-item" style="background-color: #e6e6e6;border: 1px solid black;padding: 10px;">
                                                    <div style="margin-bottom: 10px;">
                                                        <div style="display: inline-block;vertical-align: middle;">
                                                            @if ($registration->team->picture_file_name)
                                                                <img src="{{ asset('storage/team/'.$registration->team->picture_file_name) }}" style="width: 60px;height: 60px;">
                                                            @else
                                                                <img src="{{ asset('img/default-group.png') }}" style="width: 60px;height: 60px;">
                                                            @endif
                                                        </div>
                                                        <div style="display: inline-block;margin-left: 15px;vertical-align: middle;">
                                                            <h3 style="margin: 0;">{{ $registration->team->name }}</h3>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div style="padding-left: 79px;width: 600px;">
                                                            <table class="table table-striped" style="background-color: #c7c7c7;margin-bottom: 0;">
                                                                <tbody>
                                                                    @foreach ($registration->members as $member)
                                                                        <tr>
                                                                            <td style="width: 30px;vertical-align: middle;">
                                                                                @if ($member->picture_file_name)
                                                                                    <img src="{{ asset('storage/member/'.$member->picture_file_name) }}" style="height: 30px;width: 30px;">
                                                                                @else
                                                                                    <img src="{{ asset('img/default-profile.jpg') }}" style="height: 30px;width: 30px;">
                                                                                @endif
                                                                            </td>
                                                                            <td style="vertical-align: middle;">
                                                                                <p style="font-size: 16px;font-weight: bold;margin: 0;">{{ $member->name }}</p>
                                                                            </td>
                                                                            <td style="width: 38px;vertical-align: middle;">
                                                                                <button class="btn btn-default">
                                                                                    <i class="fa fa-file-text-o"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="margin-top: 15px;opacity: 0.2;">
                                            <p style="font-size: 256px;margin-bottom: 0;text-align: center;"><i class="fa fa-user-times" aria-hidden="true"></i></p>
                                            <p style="font-size: 64px;margin-top: 0;text-align: center;">Don't Have Any Participant</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="tab-pane fade" id="brackets-tab">
                                    <div class="row">
                                        <div class="col-xs-offset-2 col-xs-8 alert alert-success" role="alert" style="display: none;">
                                            <ul id="tournament-brackets-alert-container">
                                                <!-- All message that want to deliver to the Participant -->
                                            </ul>
                                        </div>
                                    </div>
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
                                                @if (count($tournament->registrations) >= 2)
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox" id="ckbox-tournament-randomize-participant-seed" name="randomize" value="1"> Randomize Participant Seed
                                                        </label>
                                                    </div>
                                                @else
                                                    <div class="checkbox disabled">
                                                        <label>
                                                            <input type="checkbox" id="ckbox-tournament-randomize-participant-seed" name="randomize" value="1" disabled="disabled"> Randomize Participant Seed
                                                        </label>
                                                    </div>
                                                @endif
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
                                    @if (count($tournament->registrations) >= 2)
                                        <div style="width: 100%;text-align: center;">
                                            <iframe id="tournament-brackets-iframe" src="http://challonge.com/{{ $tournament->challonges_url }}/module" width="90%" height="500" frameborder="0" scrolling="auto" allowtransparency="true"></iframe>
                                        </div>
                                    @else
                                        <div style="width: 100%;text-align: center;opacity: 0.2;">
                                            <p style="font-size: 256px;margin-bottom: 0;text-align: center;"><i class="fa fa-eye-slash" aria-hidden="true"></i></p>
                                            <p style="font-size: 64px;margin-top: 0;text-align: center;">No Preview Available</p>
                                            <p style="font-size: 64px;margin-top: 0;text-align: center;">Min. Participant : 2</p>
                                        </div>
                                    @endif
                                </div>
                                @if ($tournament->start && count($tournament->registrations) >= 2)
                                    <div class="tab-pane fade" id="schedule-tab">
                                        @if ($tournament->type == 1)
                                            <table class="table table-bordered table-content-centered" style="margin-bottom: 0;">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 201px;">Round</th>
                                                        <th style="width: 75px;">Match #</th>
                                                        <th style="width: 185px;"></th>
                                                        <th style="width: 25px;"></th>
                                                        <th style="width: 185px;"></th>
                                                        <th style="width: 260px;border-right: 0;">Schedule</th>
                                                        <th style="width: 30px;border-left: 0;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @for ($round_id = 1; $round_id <= $tournament->max_round; $round_id++)
                                                        @foreach ($tournament->matches[$round_id] as $key_match => $match)
                                                            <tr>
                                                                @if ($key_match == 0)
                                                                    <td id="round-{{ $round_id }}-title" rowspan="{{ count($tournament->matches[$round_id]) }}">
                                                                        @if ($round_id < $tournament->max_round - 1)
                                                                            Round {{ $round_id }}
                                                                        @elseif ($round_id == $tournament->max_round - 1)
                                                                            Semifinals
                                                                        @else
                                                                            Finals
                                                                        @endif
                                                                    </td>
                                                                @endif
                                                                <td>{{ $key_match + 1 }}</td>
                                                                <td>
                                                                    @if (isset($match->participants[0]))
                                                                        {{ $match->participants[0]->team->name }}
                                                                    @else
                                                                        TBD
                                                                    @endif
                                                                </td>
                                                                <td>VS</td>
                                                                <td>
                                                                    @if (isset($match->participants[1]))
                                                                        {{ $match->participants[1]->team->name }}
                                                                    @else
                                                                        TBD
                                                                    @endif
                                                                </td>
                                                                <td style="border-right: 0;">
                                                                    @if ($match->scheduled_time)
                                                                        {{ date('l, d F Y H:i:s', strtotime($match->scheduled_time)) }}
                                                                    @else
                                                                        Not Scheduled
                                                                    @endif
                                                                </td>
                                                                <td style="border-left: 0;">
                                                                    <button class="btn btn-default btn-xs btn-edit-schedule" data-match-id="{{ $match->id }}" data-round-id={{ $round_id }} data-toggle="modal" data-target="#schedule-modal">
                                                                        <i class="fa fa-pencil-square-o"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endfor
                                                    @if (isset($tournament->matches[0]))
                                                        @if (isset($tournament->matches[0][0]))
                                                            <tr>
                                                                <td id="round-0-title" rowspan="1">Bronze Match</td>
                                                                <td>1</td>
                                                                <td>
                                                                    @if (isset($tournament->matches[0][0]->participants[0]))
                                                                        {{ $tournament->matches[0][0]->participants[0]->team->name }}
                                                                    @else
                                                                        TBD
                                                                    @endif
                                                                </td>
                                                                <td>VS</td>
                                                                <td>
                                                                    @if (isset($tournament->matches[0][0]->participants[1]))
                                                                        {{ $tournament->matches[0][0]->participants[1]->team->name }}
                                                                    @else
                                                                        TBD
                                                                    @endif
                                                                </td>
                                                                <td style="border-right: 0;">
                                                                    @if ($tournament->matches[0][0]->scheduled_time)
                                                                        {{ date('l, d F Y H:i:s', strtotime($tournament->matches[0][0]->scheduled_time)) }}
                                                                    @else
                                                                        Not Scheduled
                                                                    @endif
                                                                </td>
                                                                <td style="border-left: 0;">
                                                                    <button class="btn btn-default btn-xs btn-edit-schedule" data-match-id="{{ $tournament->matches[0][0]->id }}" data-round-id="0" data-toggle="modal" data-target="#schedule-modal">
                                                                        <i class="fa fa-pencil-square-o"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endif
                                                </tbody>
                                            </table>
                                        @elseif ($tournament->type == 2)
                                            {{-- <h2 style="border-bottom: 1px solid black;margin-bottom: 15px;margin-top: 0;padding-bottom: 10px;">Upper Bracket</h2>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Round</th>
                                                        <th>Match #</th>
                                                        <th>Team A</th>
                                                        <th></th>
                                                        <th>Team B</th>
                                                        <th>Schedule</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td rowspan="3" style="text-align: center;vertical-align: middle;">Round 1</td>
                                                        <td>1</td>
                                                        <td>Team A</td>
                                                        <td>vs</td>
                                                        <td>Team B</td>
                                                        <td>Not Schedule</td>
                                                    </tr>
                                                    <tr>
                                                        <td>2</td>
                                                        <td>Team A</td>
                                                        <td>vs</td>
                                                        <td>Team B</td>
                                                        <td>Not Schedule</td>
                                                    </tr>
                                                    <tr>
                                                        <td>3</td>
                                                        <td>Team A</td>
                                                        <td>vs</td>
                                                        <td>Team B</td>
                                                        <td>Not Schedule</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <h2 style="border-bottom: 1px solid black;margin-bottom: 15px;margin-top: 0;padding-bottom: 10px;">Lower Bracket</h2>
                                            <table class="table table-bordered" style="margin-bottom: 0;">
                                                <thead>
                                                    <tr>
                                                        <th>Round</th>
                                                        <th>Match #</th>
                                                        <th>Team A</th>
                                                        <th></th>
                                                        <th>Team B</th>
                                                        <th>Schedule</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td rowspan="3" style="text-align: center;vertical-align: middle;">Round 1</td>
                                                        <td>1</td>
                                                        <td>Team A</td>
                                                        <td>vs</td>
                                                        <td>Team B</td>
                                                        <td>Not Schedule</td>
                                                    </tr>
                                                    <tr>
                                                        <td>2</td>
                                                        <td>Team A</td>
                                                        <td>vs</td>
                                                        <td>Team B</td>
                                                        <td>Not Schedule</td>
                                                    </tr>
                                                    <tr>
                                                        <td>3</td>
                                                        <td>Team A</td>
                                                        <td>vs</td>
                                                        <td>Team B</td>
                                                        <td>Not Schedule</td>
                                                    </tr>
                                                </tbody>
                                            </table> --}}
                                        @endif
                                    </div>
                                    <div class="tab-pane fade" id="report-match-tab" style="min-height: 600px;">
                                        Default 5
                                    </div>
                                    <div class="tab-pane fade" id="live-match-tab" style="min-height: 600px;">
                                        Default 6
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div class="modal modal-remove-padding-right" id="schedule-modal" tabindex="-1" role="dialog" aria-labelledby="schedule-modal-label">
        <div class="modal-dialog modal-dialog-fixed-width-500" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-border-bottom-custom">
                    <h3 id="schedule-round-match-title" class="text-center" style="margin: 0;">Round 1 - Match 2</h3>
                    <h3 id="schedule-versus-title" class="text-center" style="margin: 0;margin-top: 5px;">Team C VS Team D</h3>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success" role="alert" style="margin-left: 5px;margin-right: 5px;display: none;">
                        <ul id="schedule-alert-container">
                            <!-- All message that want to deliver to the user -->
                        </ul>
                    </div>
                    <form id="form-schedule">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label for="schedule-date-and-time" class="col-xs-offset-1 col-xs-3 control-label">
                                    Date &amp; Time
                                </label>
                                <div class="col-xs-6">
                                    <div class="input-group date" id="schedule-date-and-time-datetimepicker">
                                        <input type="text" class="form-control" id="schedule-date-and-time" name="schedule_date_and_time" required="required">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-default ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9" id="btn-schedule">
                                <span class="ladda-label">Save</span>
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
    <script src="{{ asset('vendor/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('js/organizer/tournament-detail.js') }}"></script>
@endsection
