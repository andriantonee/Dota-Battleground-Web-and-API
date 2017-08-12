@extends('participant.main.master')

@section('title', 'Tournament Detail')

@section('style')
    <link href="{{ asset('css/participant/footer.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/tab-pages.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/modify-table.css') }}" rel="stylesheet">
    <style type="text/css">
        #tournament-container {
            min-height: 536px;
        }

        #tournament-header {
            height: 135px;
            padding: 0px 15px 5px 45px;
        }
        #tournament-header-logo {
            display: inline-block;
            vertical-align: top;
        }
        #tournament-header-logo > img {
            border: 1px solid black;
            height: 130px;
            width: 130px;
        }
        #tournament-header-detail {
            border-bottom: 1px solid #ddd;
            display: inline-block;
            margin-left: 5px;
            margin-top: 20px;
            padding-bottom: 10px;
            width: 500px;
            vertical-align: top;
        }
        #tournament-header-detail-name {
            margin-bottom: 5px;
            margin-top: 0;
        }
        p.tournament-header-detail-other {
            font-size: 13px;
            margin-bottom: 0px;
            margin-top: 0px;
            padding-left: 20px;
        }
        #tournament-header-registration {
            border-color:#a5a5a5;
            display: inline-block;
            height: 100%;
            margin-left: 15px;
            width: 276px;
            vertical-align: top;
        }
        #tournament-header-registration-container {
            display: table;
            height: 100%;
            width: 100%;
        }
        #tournament-header-registration-content {
            display: table-cell;
            text-align: center;
            vertical-align: bottom;
        }
        #tournament-header-registration-status {
            margin-bottom: 5px;
            margin-top: 0;
        }
        #tournament-header-registration-closed {
            margin-bottom: 5px;
            margin-top: 0;
            font-size: 10px;
        }
        #tournament-header-registration-action {
            border-radius: 0;
            margin-bottom: 15px;
            width: 200px;
        }
        #tournament-header-alert {
            border: 1px solid #cccccc;
            display: inline-block;
            margin-bottom: 15px;
            padding: 6px 12px;
            width: 200px;
            color:#fc4028;
        }

        #tournament-body {
            margin-top:10px;
            padding: 0px 15px;
        }
        #tournament-body > div.well {
            min-height: 386px;
        }
        .well-title {
            border-bottom: 1px solid #ddd;
            margin-bottom: 15px;
            margin-top: 0;
            padding-bottom: 10px;
        }
        .tab-pane-title {
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            margin-top: 0;
            padding-bottom: 10px;
        }
        .tab-pane-title:first-child {
            margin-bottom: 0;
        }
        table.table-tournament-detail {
            margin-bottom: 0;
            margin-top: 20px;
        }

        table.table-tournament-detail > tbody > tr > td:first-child {
            font-weight: bold;
        }
        table.table-tournament-detail > tbody > tr > td {
            border: none;
        }
        .tournament-rule-content {
            margin-bottom: 0;
            margin-top: 20px;
            max-height: 384px;
            min-height: 384px;
            overflow-x: hidden;
            overflow-y: auto;
            color:#e3e3e3;
        }
        .prizes-container {
            margin-top: 20px;
        }
        .prizes-content {
            color:#e3e3e3;
            border: 1px solid #5f6471;
            background: linear-gradient(to right, #2f313a, #2f3341);
            display: flex;
            display: -webkit-flex;
            flex-direction: row;
            -webkit-flex-direction: row;
            -ms-flex-direction: row;
            height: 80px;
            width: 800px;
        }
        .prizes-content:first-child {
            margin-bottom: 5px;
        }
        .prizes-content+.prizes-content {
            margin-bottom: 5px;
        }
        .prizes-content:last-child {
            margin-bottom: 0;
        }
        .prizes-rank {
            border-right: 1px solid #000;
            height: 100%;
            width: 125px;
        }
        .prizes-rank > h1 {
            color: #232323;
            font-size: 78px;
            transform: translate(7px, -32px) rotateZ(-15deg);
            -webkit-transform: translate(7px, -32px) rotateZ(-15deg);
            -moz-transform: translate(7px, -32px) rotateZ(-15deg);
            -ms-transform: translate(7px, -32px) rotateZ(-15deg);
            -o-transform: translate(7px, -32px) rotateZ(-15deg);
        }
        .prizes-rank > h1 > span {
            font-size: 48px;
            margin-left: -3px;
        }
        .prizes-rank-gold {
            background-color: #ffd700;
        }
        .prizes-rank-silver {
            background-color: #c0c0c0;
        }
        .prizes-rank-copper {
            background-color: #b87333;
        }
        .prizes-detail-container {
            height: 100%;
            width: 675px;
        }
        .prizes-detail-content {
            display: table;
            height: 100%;
            padding-left: 15px;
            width: 100%;
        }
        .prizes-detail-content > h3 {
            display: table-cell;
            vertical-align: middle;
        }
        .prizes-other {
            margin-top: 10px;
            max-height: 384px;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .table-content-centered th, .table-content-centered td {
            text-align: center;
            vertical-align: middle !important;
        }

        .live-match-group-list {
            margin-top: 10px;
        }
        .live-match-group-list-item {
            cursor: pointer;
            padding: 15px;
            position: relative;
            text-align: center;
        }
        .live-match-group-list-item:first-child {
            margin-bottom: 10px;
        }
        .live-match-group-list-item+.live-match-group-list-item {
            margin-bottom: 10px;
        }
        .live-match-group-list-item:last-child {
            margin-bottom: 0px;
        }
        .radiant-color {
            color: #92A525;
        }
        .dire-color {
            color: #C23C2A;
        }
        .handle-overflow {
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
            width: 100px;
        }
    </style>
@endsection

@section('content')
    <div id="tournament-container" class="container">
        <div id="tournament-header">
            <div id="tournament-header-logo">
                <img src="{{ asset('storage/tournament/'.$tournament->logo_file_name) }}">
            </div>
            <div id="tournament-header-detail">
                <h1 id="tournament-header-detail-name">{{ $tournament->name }}</h1>
                <p class="tournament-header-detail-other" style="color: #afaeae;">Organized By : {{ $tournament->owner->name }}</p>
                <p class="tournament-header-detail-other" style="color: #afaeae;">Event Date : {{ date('d F Y', strtotime($tournament->start_date)) }} - {{ date('d F Y', strtotime($tournament->end_date)) }}</p>
            </div>
            <div id="tournament-header-registration" class="well-transparent">
                <div id="tournament-header-registration-container">
                    <div id="tournament-header-registration-content" >
                        @if ($tournament->registration_closed >= date('Y-m-d H:i:s'))
                            <h4 id="tournament-header-registration-status">REGISTRATION IS OPEN!</h4>
                        @else
                            <h4 id="tournament-header-registration-status">REGISTRATION IS CLOSED!</h4>
                        @endif
                        <p id="tournament-header-registration-closed" style="color: #afaeae;">Registration Ends : {{ date('d F Y H:i', strtotime($tournament->registration_closed)) }}</p>
                        @if ($tournament->registration_closed >= date('Y-m-d H:i:s'))
                            @if ($participant)
                                @if ($has_verified_identifications)
                                    <a href="{{ url('tournament/'.$tournament->id.'/register') }}" id="tournament-header-registration-action" class="btn btn-default btn-custom">REGISTER</a>
                                @else
                                    <span id="tournament-header-alert">IDENTITY CARD NOT VERIFIED</span>
                                @endif
                            @else
                                <span id="tournament-header-alert">SIGN IN TO REGISTER</span>
                            @endif
                        @else
                            <span id="tournament-header-alert">REGISTRATION CLOSED</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div id="tournament-body">
            <div class="well well-lg well-transparent">
                <h3 class="well-title">Overview</h3>
                <p style="color:#e3e3e3;">{!! $tournament->description !!}</p>
                <div class="panel with-nav-tabs panel-default" style="margin-top:20px;">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#details-tab" data-toggle="tab">Details</a></li>
                        <li><a href="#rules-tab" data-toggle="tab">Rules</a></li>
                        <li><a href="#prizes-tab" data-toggle="tab">Prizes</a></li>
                        @if ($tournament->start == 1 && $tournament->registrations_count >= 2)
                            <li><a href="#schedule-tab" data-toggle="tab">Schedule</a></li>
                            <li><a href="#bracket-tab" data-toggle="tab">Bracket</a></li>
                            <li><a href="#live-match-tab" data-toggle="tab">Live Match</a></li>
                        @endif
                    </ul>
                    <div class="panel-body">
                        <div class="tab-content">
                            <div class="tab-pane fade in active" id="details-tab">
                                <h4 class="tab-pane-title">Details</h4>
                                <table class="table table-striped table-hover table-custom table-tournament-detail">
                                    <tbody>
                                        <tr>
                                            <td>Event Type</td>
                                            <td>
                                                @if ($tournament->type == 1)
                                                    Single Elimination
                                                @elseif ($tournament->type == 2)
                                                    Double Elimination
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Location</td>
                                            <td>{{ $tournament->city ? $tournament->city->name : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Address</td>
                                            <td>{{ $tournament->address ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Entry Fee</td>
                                            <td>Rp. {{ number_format($tournament->entry_fee, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Min Participants</td>
                                            <td>2</td>
                                        </tr>
                                        <tr>
                                            <td>Max Participants</td>
                                            <td>{{ $tournament->max_participant }}</td>
                                        </tr>
                                        <tr>
                                            <td>Current Participants</td>
                                            <td>{{ $tournament->registrations_count }}</td>
                                        </tr>
                                        <tr>
                                            <td>Team Size</td>
                                            <td>
                                                @if ($tournament->team_size == 5)
                                                    5 VS 5
                                                @elseif ($tournament->team_size == 1)
                                                    1 VS 1
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="rules-tab">
                                <h4 class="tab-pane-title">Rules</h4>
                                <div class="tournament-rule-content">
                                    <p>{!! $tournament->rules !!}</p>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="prizes-tab">
                                <h4 class="tab-pane-title">Prizes</h4>
                                <div class="prizes-container">
                                    <div class="prizes-content">
                                        <div class="prizes-rank prizes-rank-gold">
                                            <h1>1<span>ST</span></h1>
                                        </div>
                                        <div class="prizes-detail-container">
                                            <div class="prizes-detail-content">
                                                <h3>{{ $tournament->prize_1st ?: '-' }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="prizes-content">
                                        <div class="prizes-rank prizes-rank-silver">
                                            <h1>2<span>ND</span></h1>
                                        </div>
                                        <div class="prizes-detail-container">
                                            <div class="prizes-detail-content">
                                                <h3>{{ $tournament->prize_2nd ?: '-' }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="prizes-content">
                                        <div class="prizes-rank prizes-rank-copper">
                                            <h1>3<span>RD</span></h1>
                                        </div>
                                        <div class="prizes-detail-container">
                                            <div class="prizes-detail-content">
                                                <h3>{{ $tournament->prize_3rd ?: '-' }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if ($tournament->prize_other)
                                    <h4 class="tab-pane-title" style="margin-top: 20px;margin-bottom: 10px;">Other Prizes</h4>
                                    <div class="prizes-other">
                                        <p style="margin: 0;">{!! $tournament->prize_other !!}</p>
                                    </div>
                                @endif
                            </div>
                            @if ($tournament->start == 1 && $tournament->registrations_count >= 2)
                                <div class="tab-pane fade" id="schedule-tab">
                                    @if ($tournament->type == 1)
                                        <h4 style="border-bottom: 1px solid #e3e3e3; margin-bottom: 15px;margin-top: 0;padding-bottom: 10px;">Schedule</h4>
                                        <table class="table table-bordered table-striped table-content-centered table-schedule" style="margin-top: 10px;margin-bottom: 0;">
                                            <thead>
                                                <tr>
                                                    <th style="width: 201px;">Round</th>
                                                    <th style="width: 75px;">Match #</th>
                                                    <th style="width: 185px;"></th>
                                                    <th style="width: 35px;"></th>
                                                    <th style="width: 185px;"></th>
                                                    <th style="width: 260px;">Schedule</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($tournament->max_round >= 0)
                                                    @for ($round_id = 1; $round_id <= $tournament->max_round; $round_id++)
                                                        @foreach ($tournament->matches[$round_id] as $key_match => $match)
                                                            <tr>
                                                                @if ($key_match == 0)
                                                                    <td class="round" rowspan="{{ count($tournament->matches[$round_id]) }}">
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
                                                                <td>
                                                                    @if ($match->scheduled_time)
                                                                        {{ date('l, d F Y H:i:s', strtotime($match->scheduled_time)) }}
                                                                    @else
                                                                        Not Scheduled
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endfor
                                                    @if (isset($tournament->matches[0]))
                                                        @if (isset($tournament->matches[0][0]))
                                                            <tr>
                                                                <td class="round" rowspan="1">Bronze Match</td>
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
                                                                <td>
                                                                    @if ($tournament->matches[0][0]->scheduled_time)
                                                                        {{ date('l, d F Y H:i:s', strtotime($tournament->matches[0][0]->scheduled_time)) }}
                                                                    @else
                                                                        Not Scheduled
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @else
                                                            @if ($tournament->max_round == 0)
                                                                <tr>
                                                                    <td colspan="6">No Match Available</td>
                                                                </tr>
                                                            @endif
                                                        @endif
                                                    @endif
                                                @else
                                                    <tr>
                                                        <td colspan="6">No Match Available</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    @elseif ($tournament->type == 2)
                                        <h4 style="border-bottom: 1px solid #e3e3e3; margin-bottom: 15px;margin-top: 0;padding-bottom: 10px;">Upper Bracket Schedule</h4>
                                        <table class="table table-bordered table-striped table-content-centered table-schedule" style="margin-bottom: 15px;">
                                            <thead>
                                                <tr>
                                                    <th style="width: 201px;">Round</th>
                                                    <th style="width: 75px;">Match #</th>
                                                    <th style="width: 185px;"></th>
                                                    <th style="width: 35px;"></th>
                                                    <th style="width: 185px;"></th>
                                                    <th style="width: 260px;">Schedule</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($tournament->max_round > 0)
                                                    @for ($round_id = 1; $round_id <= $tournament->max_round; $round_id++)
                                                        @foreach ($tournament->matches[$round_id] as $key_match => $match)
                                                            <tr>
                                                                @if ($key_match == 0)
                                                                    <td class="round" rowspan="{{ count($tournament->matches[$round_id]) }}">
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
                                                                <td>
                                                                    @if ($match->scheduled_time)
                                                                        {{ date('l, d F Y H:i:s', strtotime($match->scheduled_time)) }}
                                                                    @else
                                                                        Not Scheduled
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endfor
                                                @else
                                                    <tr>
                                                        <td class="round" colspan="6">No Match Available</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                        <h4 style="border-bottom: 1px solid #e3e3e3;margin-bottom: 15px;margin-top: 0;padding-bottom: 10px;">Lower Bracket Schedule</h4>
                                        <table class="table table-bordered table-striped table-content-centered table-schedule" style="margin-bottom: 0;">
                                            <thead>
                                                <tr>
                                                    <th style="width: 201px;">Round</th>
                                                    <th style="width: 75px;">Match #</th>
                                                    <th style="width: 185px;"></th>
                                                    <th style="width: 35px;"></th>
                                                    <th style="width: 185px;"></th>
                                                    <th style="width: 260px;">Schedule</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($tournament->min_round < 0)
                                                    @for ($round_id = -1; $round_id >= $tournament->min_round; $round_id--)
                                                        @foreach ($tournament->matches[$round_id] as $key_match => $match)
                                                            <tr>
                                                                @if ($key_match == 0)
                                                                    <td class="round" rowspan="{{ count($tournament->matches[$round_id]) }}">
                                                                        Round {{ abs($round_id) }}
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
                                                                <td>
                                                                    @if ($match->scheduled_time)
                                                                        {{ date('l, d F Y H:i:s', strtotime($match->scheduled_time)) }}
                                                                    @else
                                                                        Not Scheduled
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endfor
                                                @else
                                                    <tr>
                                                        <td colspan="6">No Match Available</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                                <div class="tab-pane fade" id="bracket-tab">
                                    <h4 class="tab-pane-title">Bracket</h4>
                                    <div style="margin-top: 10px;width: 100%;">
                                        <iframe id="tournament-brackets-iframe" src="http://challonge.com/{{ $tournament->challonges_url }}/module" width="100%" height="500" frameborder="0" scrolling="auto" allowtransparency="true"></iframe>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="live-match-tab">
                                    <h4 class="tab-pane-title">Live Matches</h4>
                                    @if (count($tournament->live_matches) > 0)
                                        <div class="live-match-group-list">
                                            @foreach ($tournament->live_matches as $match)
                                                @foreach ($match->dota2_live_matches as $live_match)
                                                    <a href="{{ url('dota-2/match/'.$live_match->id) }}" class="live-match-group-list-item well-custom">
                                                        <p style="position: absolute;top: 0;left: 3px;margin: 0;">Match ID : {{ $live_match->id }}</p>
                                                        <p style="position: absolute;top: 0;right: 3px;margin: 0;">
                                                            Game {{ $live_match->dota2_live_match_teams[0]->series_wins + $live_match->dota2_live_match_teams[1]->series_wins + 1 }} | Best of
                                                            @if ($live_match->series_type == 0)
                                                                1
                                                            @elseif ($live_match->series_type == 1)
                                                                3
                                                            @elseif ($live_match->series_type == 2)
                                                                5
                                                            @endif
                                                        </p>
                                                        <p style="position: absolute;bottom: 0;right: 3px;margin: 0;">Spectators : {{ $live_match->spectators }}</p>
                                                        <div style="display: inline-block;text-align: right;vertical-align: middle;">
                                                            @if ($live_match->dota2_live_match_teams[0]->tournament_registration)
                                                                <h2 title="{{ $live_match->dota2_live_match_teams[0]->tournament_registration->team->name }}" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 190px;max-width: 190px;margin: 0;vertical-align: middle;">{{ $live_match->dota2_live_match_teams[0]->tournament_registration->team->name }}</h2>
                                                                @if ($live_match->dota2_live_match_teams[0]->tournament_registration->team->picture_file_name)
                                                                    <img src="{{ asset('storage/team/'.$live_match->dota2_live_match_teams[0]->tournament_registration->team->picture_file_name) }}" style="display: inline-block;height: 80px;width: 130px;margin-left: 5px;vertical-align: middle;">
                                                                @else
                                                                    <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 80px;width: 130px;margin-left: 5px;vertical-align: middle;">
                                                                @endif
                                                            @else
                                                                @if ($live_match->dota2_live_match_teams[0]->dota2_teams_name)
                                                                    <h2 title="{{ $live_match->dota2_live_match_teams[0]->dota2_teams_name }}" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 190px;max-width: 190px;margin: 0;vertical-align: middle;">{{ $live_match->dota2_live_match_teams[0]->dota2_teams_name }}</h2>
                                                                @else
                                                                    <h2 title="Radiant" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 190px;max-width: 190px;margin: 0;vertical-align: middle;">Radiant</h2>
                                                                @endif
                                                                <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 80px;width: 130px;margin-left: 5px;vertical-align: middle;">
                                                            @endif
                                                        </div>
                                                        <div style="display: inline-block;margin-left: 5px;margin-right: 5px;vertical-align: top;min-width: 120px;max-width: 120px;">
                                                            <h5 id="duration" style="margin-top: 0px; margin-bottom: 10px;color: #5f6472;">{{ (floor($live_match->duration / 60) < 10 ? '0' : '').floor($live_match->duration / 60) }}:{{ ($live_match->duration % 60 < 10 ? '0' : '').$live_match->duration % 60 }}</h5>
                                                            <h2 id="score" style="margin: 0;">{{ $live_match->dota2_live_match_teams[0]->score }} - {{ $live_match->dota2_live_match_teams[1]->score }}</h2>
                                                        </div>
                                                        <div style="display: inline-block;text-align: left;vertical-align: middle;">
                                                            @if ($live_match->dota2_live_match_teams[1]->tournament_registration)
                                                                @if ($live_match->dota2_live_match_teams[1]->tournament_registration->team->picture_file_name)
                                                                    <img src="{{ asset('storage/team/'.$live_match->dota2_live_match_teams[1]->tournament_registration->team->picture_file_name) }}" style="display: inline-block;height: 80px;width: 130px;margin-right: 5px;vertical-align: middle;">
                                                                @else
                                                                    <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 80px;width: 130px;margin-right: 5px;vertical-align: middle;">
                                                                @endif
                                                                <h2 title="{{ $live_match->dota2_live_match_teams[1]->dota2_teams_name }}" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 190px;max-width: 190px;margin: 0;vertical-align: middle;">{{ $live_match->dota2_live_match_teams[1]->dota2_teams_name }}</h2>
                                                            @else
                                                                <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 80px;width: 130px;margin-right: 5px;vertical-align: middle;">
                                                                @if ($live_match->dota2_live_match_teams[1]->dota2_teams_name)
                                                                    <h2 title="{{ $live_match->dota2_live_match_teams[1]->dota2_teams_name }}" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 190px;max-width: 190px;margin: 0;vertical-align: middle;">{{ $live_match->dota2_live_match_teams[1]->dota2_teams_name }}</h2>
                                                                @else
                                                                    <h2 title="Dire" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 190px;max-width: 190px;margin: 0;vertical-align: middle;">Dire</h2>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </a>
                                                @endforeach
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="margin-top: 15px;opacity: 0.2;">
                                            <p style="font-size: 256px;margin-bottom: 0;text-align: center;"><i class="fa fa-calendar-times-o" aria-hidden="true"></i></p>
                                            <p style="font-size: 64px;margin-top: 0;text-align: center;">No Match Lives Now.</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('participant.footer.footer')
@endsection

@section('script')
@endsection
