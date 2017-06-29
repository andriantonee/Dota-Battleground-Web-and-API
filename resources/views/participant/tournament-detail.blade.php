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
            margin-top: 20px;
            max-height: 384px;
            overflow-x: hidden;
            overflow-y: auto;
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
                                <a href="{{ url('tournament/'.$tournament->id.'/register') }}" id="tournament-header-registration-action" class="btn btn-default btn-custom">REGISTER</a>
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
                        <li><a href="#schedule-tab" data-toggle="tab">Schedule</a></li>
                        <li><a href="#bracket-tab" data-toggle="tab">Bracket</a></li>
                        <li><a href="#live-match-tab" data-toggle="tab">Live Match</a></li>
                    </ul>
                    <div class="panel-body">
                        <div class="tab-content">
                            <div class="tab-pane fade in active" id="details-tab">
                                <h4 class="tab-pane-title">Details</h4>
                                <table class="table table-striped table-hover table-custom table-tournament-detail">
                                    <tbody>
                                        <tr>
                                            <td>Event Type</td>
                                            <td>{{ $tournament->type }}</td>
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
                                    <h4 class="tab-pane-title">Other Prizes</h4>
                                    <div class="prizes-other">
                                        <p>{!! $tournament->prize_other !!}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="schedule-tab">
                                <h4 class="tab-pane-title">Schedule</h4>
                            </div>
                            <div class="tab-pane fade" id="bracket-tab">
                                <h4 class="tab-pane-title">Bracket</h4>
                            </div>
                            <div class="tab-pane fade" id="live-match-tab">
                                <h4 class="tab-pane-title">Live Matches</h4>
                            </div>
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
