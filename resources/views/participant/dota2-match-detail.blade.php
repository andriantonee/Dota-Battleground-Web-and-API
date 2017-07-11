@extends('participant.main.master')

@section('title', 'Tournament')

@section('style')
    <link href="{{ asset('css/jquery.scrollbar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/footer.css') }}" rel="stylesheet">
    <style type="text/css">
        .radiant-color {
            color: #92A525;
        }
        .dire-color {
            color: #C23C2A;
        }
        .networth-color {
            color: #FBB829;
        }
        .panel-custom {
            background: transparent;
            border: none;
            margin: 30px;
        }
        .panel-custom > .nav-tabs > li.active > a {
            background: linear-gradient(to right, #2f313a, #2f3341);
            border: none; 
            border-top: 3px solid #5f6472;
            border-color: rgb(95, 100, 114);
            color: #fff;
            font-weight: bold;
            margin: 0;
        }
        .panel-custom > .nav-tabs > li > a {
            background-color: #272a34;
            border-bottom: none;
            color: #afaeae;
        }
        .panel-custom > .nav-tabs > li > a:hover {
            color: #fff;
        }
        .fieldset-custom {
            border: 1px solid #5f6471;
            box-shadow: 5px 5px 12px 5px rgba(0, 0, 0, 0.3);
            padding: 0 1.4em 1.4em 1.4em;
        }
        .fieldset-custom > legend {
            border-bottom: none;
            color: #afaeae;
            font-size: 1.2em !important;
            font-weight: bold !important;
            margin-bottom: 0;
            padding: 0 10px;
            text-align: left !important; 
            width: auto;
        }
        .label-custom {
            background: #111;
            border-radius: 0;
            color: #d9d9d9;
            display: block;
            line-height: 12px;
            padding: 2px;
        }
        .draft-pick {
            display: inline-block;
            text-align: center;
        }
        .draft-pick > img {
            display: inline-block;
            height: 42px;
        }
        .draft-ban {
            display: inline-block;
            opacity: 0.5;
            text-align: center;
        }
        .draft-ban > img {
            height: 38px;
            filter: grayscale(100%);
        }
        .minimap-wrapper {
            background-color: rgba(0, 0, 0, 0.2);
            box-shadow: 5px 5px 12px 5px rgba(0, 0, 0, 0.3);
            height: 302px;
            width: 302px;
            margin: 30px 0;
            padding: 7px;
        }
        .minimap {
            background: url({{ asset('img/dota-2/minimap.png') }}) no-repeat;
            background-size: cover;
            height: 100%;
            width: 100%;
            position: relative;
        }
        .radiant .tower {
            position: absolute;
            border: 2px solid #92A525;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            background: rgba(146, 165, 37, 0.7);
        }
        .radiant .barrack {
            position: absolute;
            border: 2px solid #92A525;
            width: 15px;
            height: 15px;
            background: rgba(146, 165, 37, 0.7);
        }
        .radiant .player {
            position: absolute;
            border: 2px solid #92A525;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            background-color: rgba(146, 165, 37, 0.7);
            background-position: center;
            background-repeat: no-repeat;
            background-size: 90%;
            z-index: 1000;
        }
        .dire .tower {
            position: absolute;
            border: 2px solid #C23C2A;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            background: rgba(194, 60, 42, 0.7);
        }
        .dire .barrack {
            position: absolute;
            border: 2px solid #C23C2A;
            width: 15px;
            height: 15px;
            background: rgba(194, 60, 42, 0.7);
        }
        .dire .player {
            position: absolute;
            border: 2px solid #C23C2A;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            background-color: rgba(194, 60, 42, 0.7);
            background-position: center;
            background-repeat: no-repeat;
            background-size: 90%;
            z-index: 1000;
        }
        .tower.destroy {
            border: 2px solid #fff;
            background: rgb(0, 0, 0);
        }
        .barrack.destroy {
            border: 2px solid #fff;
            background: rgb(0, 0, 0);
        }
        .radiant .tower.top-1 {
            top: 35%;
            left: 9%;
        }
        .radiant .tower.middle-1 {
            top: 56%;
            left: 35%;
        }
        .radiant .tower.bottom-1 {
            top: 86%;
            left: 77%;
        }
        .radiant .tower.top-2 {
            top: 52%;
            left: 9%;
        }
        .radiant .tower.middle-2 {
            top: 63%;
            left: 25%;
        }
        .radiant .tower.bottom-2 {
            top: 84%;
            left: 43%;
        }
        .radiant .tower.top-3 {
            top: 67%;
            left: 7%;
        }
        .radiant .tower.middle-3 {
            top: 72%;
            left: 18%;
        }
        .radiant .tower.bottom-3 {
            top: 83%;
            left: 22%;
        }
        .radiant .tower.top-4 {
            top: 79%;
            left: 9%;
        }
        .radiant .tower.bottom-4 {
            top: 82%;
            left: 11%;
        }
        .radiant .barrack.top-ranged {
            top: 71%;
            left: 5%;
        }
        .radiant .barrack.top-melee {
            top: 71%;
            left: 10%;
        }
        .radiant .barrack.middle-ranged {
            top: 73%;
            left: 15%;
        }
        .radiant .barrack.middle-melee {
            top: 76%;
            left: 18%;
        }
        .radiant .barrack.bottom-ranged {
            top: 82%;
            left: 20%;
        }
        .radiant .barrack.bottom-melee {
            top: 86%;
            left: 20%;
        }
        .dire .tower.top-1 {
            top: 8%;
            left: 17%;
        }
        .dire .tower.middle-1 {
            top: 45%;
            left: 54%;
        }
        .dire .tower.bottom-1 {
            top: 57%;
            left: 84%;
        }
        .dire .tower.top-2 {
            top: 10%;
            left: 52%;
        }
        .dire .tower.middle-2 {
            top: 32%;
            left: 62%;
        }
        .dire .tower.bottom-2 {
            top: 42%;
            left: 88%;
        }
        .dire .tower.top-3 {
            top: 12%;
            left: 69%;
        }
        .dire .tower.middle-3 {
            top: 23%;
            left: 73%;
        }
        .dire .tower.bottom-3 {
            top: 28%;
            left: 86%;
        }
        .dire .tower.top-4 {
            top: 13%;
            left: 81%;
        }
        .dire .tower.bottom-4 {
            top: 16%;
            left: 84%;
        }
        .dire .barrack.top-ranged {
            top: 10%;
            left: 73%;
        }
        .dire .barrack.top-melee {
            top: 14%;
            left: 73%;
        }
        .dire .barrack.middle-ranged {
            top: 20%;
            left: 75%;
        }
        .dire .barrack.middle-melee {
            top: 23%;
            left: 78%;
        }
        .dire .barrack.bottom-ranged {
            top: 26%;
            left: 85%;
        }
        .dire .barrack.bottom-melee {
            top: 26%;
            left: 89%;
        }
        .table-match {
            background-color: #242f39;
            box-shadow: 5px 5px 12px 5px rgba(0, 0, 0, 0.3);
        }
        .table-radiant.table-striped> tbody > tr:nth-child(2n+1) > td, .table-radiant.table-striped > tbody > tr:nth-child(2n+1) > th {
           background-color: #313d36;
        }
        .table-dire.table-striped> tbody > tr:nth-child(2n+1) > td, .table-dire.table-striped > tbody > tr:nth-child(2n+1) > th {
           background-color: #373037;
        }
        .table-match > thead > tr > th {
            border-bottom: none;
        }
        .table-match > thead {
            background-color: rgba(0, 0, 0, 0.4);
        }
        .table-match > tbody > tr > td, .table-match > tbody > tr > th, .table-match> tfoot > tr > td, .table-match > tfoot > tr > th, .table-match > thead > tr > td, .table > thead > tr > th {
            border-top: none;
        }
        .table-match > tbody > tr > td {
            vertical-align:middle;
        }
        .table-match > tbody > tr >td.item {
            padding: 2px;
            text-align: center;
        }
        .table-match > tbody >tr > td.item > .item-detail-1 >div, .table-match > tbody >tr > td.item > .item-detail-2 >div {
            display: inline-block;
        }
        .table-hover.table-striped tbody tr:hover td, .table-hover.table-striped tbody tr:hover th {
            background-color: #39424b;
        }
        .table-ability > tbody > tr >td {
            padding: 2px;
            text-align: center;
        }
        .table-ability {
            table-layout: fixed;
        }
        .table-ability > thead > tr > th {
            text-align: center;
        }
        .img-xs {
            height: 20px;
            width: 27.5px;
        }
        .img-sm {
            height: 24px;
        }
        .handle-overflow {
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
            width: 100px;
        }
        .comment-open {
            overflow: hidden;
        }
        .comment-backdrop.in {
            opacity: .5;
        }
        .comment-backdrop {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 1040;
            background-color: #000;
        }
        .comment-container {
            position: fixed;
            top: 0;
            right: -425px;
            bottom: 0;
            z-index: 1052;
            background-color: #292E3A;
            width: 425px;
            padding: 15px 0;
            transition: all 1s ease;
            -webkit-transition: all 1s ease;
            -moz-transition: all 1s ease;
            -o-transition: all 1s ease;
            -ms-transition: all 1s ease;
        }
        .comment-open-action {
            position: absolute;
            top: 72px;
            left: -41px;
            border: 1px solid #5f6471;
            border-right: 0;
            border-radius: 50% 0 0 50%;
            padding: 10px 0px 10px 15px;
            cursor: pointer;
            background-color: #292E3A;
        }
        .comment-open-action:hover {
            background: linear-gradient(to right, #323645,#3d3f4b);
        }
        .comment-close-action {
            position: absolute;
            top: 72px;
            left: -41px;
            border: 1px solid #5f6471;
            border-right: 0;
            border-radius: 50% 0 0 50%;
            padding: 10px 0px 10px 15px;
            cursor: pointer;
            background-color: #292E3A;
        }
        .comment-close-action:hover {
            background: linear-gradient(to right, #323645,#3d3f4b);
        }
        .comment-open-action > img,
        .comment-close-action > img {
            width: 25px;
            height: 150px;
        }
        .comment-container-scroll {
            max-height: 100%;
            overflow-x: hidden;
            overflow-y: auto;
        }
        form#post-comment {
            margin: 0 15px;
        }
        form#post-comment > * {
            margin: 0;
            margin-bottom: 5px;
        }
        form#post-comment > *:last-child {
            margin: 0;
        }
        form#post-comment textarea {
            resize: none;
        }
        .comment-content {
            margin: 0 15px;
            padding: 15px 10px;
            border-bottom: 1px solid #5f6471;
        }
        .comment-profile {
            display: inline-block;
            vertical-align: top;
        }
        .comment-profile-img {
            height: 55px;
            width: 55px;
        }
        .comment-detail {
            display: inline-block;
            vertical-align: top;
            margin-left: 10px;
            max-width: 289px;
        }
        .comment-detail p {
            margin: 5px 0;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row" style="background-color: #292E3A;box-shadow: 5px 5px 12px 5px rgba(0,0,0,0.3);padding-bottom: 15px;padding-top: 15px;">
            <div class="col-xs-3">
                @if ($dota2_live_match->match)
                    <img src="{{ asset('storage/tournament/'.$dota2_live_match->match->tournament->logo_file_name) }}" style="height: 90px;width: 230px;">
                @else
                    <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="height: 90px;width: 230px;">
                @endif
            </div>
            <div class="col-xs-3">
                @if ($dota2_live_match->match)
                    <h5 style="margin-top: 0;">League : {{ $dota2_live_match->match->tournament->name }}</h5>
                @else
                    <h5 style="margin-top: 0;">League ID : {{ $dota2_live_match->leagues_id }}</h5>
                @endif
                <h5>Match ID : {{ $dota2_live_match->id }}</h5>
                <h5>Series Information : Game {{ $radiant->series_wins + $dire->series_wins + 1 }}</h5>
                <h5 style="margin-bottom: 0;">Spectators : <span id="spectators">{{ $dota2_live_match->spectators }}</span></h5>
            </div>
            <div class="col-xs-6">
                @if ($dota2_live_match->series_type == 0)
                    <h4 style="margin-bottom: 15px;margin-top: 0;">Best of 1</h4>
                @elseif ($dota2_live_match->series_type == 1)
                    <h4 style="margin-bottom: 15px;margin-top: 0;">Best of 3</h4>
                @elseif ($dota2_live_match->series_type == 2)
                    <h4 style="margin-bottom: 15px;margin-top: 0;">Best of 5</h4>
                @endif
                <div style="text-align: center;">
                    <div style="display: inline-block;text-align: right;vertical-align: middle;width: 200px;">
                        @if ($radiant->tournament_registration)
                            <h4 title="{{ $radiant->tournament_registration->team->name }}" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 90px;max-width: 90px;vertical-align: middle;">{{ $radiant->tournament_registration->team->name }}</h4>
                            @if ($radiant->tournament_registration->team->picture_file_name)
                                <img src="{{ asset('storage/team/'.$radiant->tournament_registration->team->picture_file_name) }}" style="display: inline-block;height: 50px;width: 100px;margin-left: 5px;vertical-align: middle;">
                            @else
                                <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 50px;width: 100px;margin-left: 5px;vertical-align: middle;">
                            @endif
                        @else
                            @if ($radiant->dota2_teams_name)
                                <h4 title="{{ $radiant->dota2_teams_name }}" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 90px;max-width: 90px;vertical-align: middle;">{{ $radiant->dota2_teams_name }}</h4>
                            @else
                                <h4 title="Radiant" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 90px;max-width: 90px;vertical-align: middle;">Radiant</h4>
                            @endif
                            <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 50px;width: 100px;margin-left: 5px;vertical-align: middle;">
                        @endif
                    </div>
                    <div style="display: inline-block;margin-left: 5px;margin-right: 5px;vertical-align: middle;">
                        <h3 style="margin: 0;">{{ $radiant->series_wins }} - {{ $dire->series_wins }}</h3>
                    </div>
                    <div style="display: inline-block;text-align: left;vertical-align: middle;width: 200px;">
                        @if ($dire->tournament_registration)
                            @if ($dire->tournament_registration->team->picture_file_name)
                                <img src="{{ asset('storage/team/'.$dire->tournament_registration->team->picture_file_name) }}" style="display: inline-block;height: 50px;width: 100px;margin-right: 5px;vertical-align: middle;">
                            @else
                                <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 50px;width: 100px;margin-right: 5px;vertical-align: middle;">
                            @endif
                            <h4 title="{{ $dire->tournament_registration->team->name }}" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 90px;max-width: 90px;vertical-align: middle;">{{ $dire->tournament_registration->team->name }}</h4>
                        @else
                            <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 50px;width: 100px;margin-right: 5px;vertical-align: middle;">
                            @if ($dire->dota2_teams_name)
                                <h4 title="{{ $dire->dota2_teams_name }}" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 90px;max-width: 90px;vertical-align: middle;">{{ $dire->dota2_teams_name }}</h4>
                            @else
                                <h4 title="Dire" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 90px;max-width: 90px;vertical-align: middle;">Dire</h4>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="background-color: #292E3A;box-shadow: 5px 5px 12px 5px rgba(0,0,0,0.3);margin-bottom: 15px;margin-top: 15px;padding-bottom: 15px;padding-top: 15px;">
            @if ($radiant->matches_result == 3)
                @if ($radiant->tournament_registration)
                    <h2 id="radiant-victory" class="radiant-color" style="text-align: center;margin: 0;margin-bottom: 10px;font-weight: 800 !important;text-shadow: 1px 1px 5px black;">{{ $radiant->tournament_registration->team->name }} VICTORY</h2>
                @else
                    @if ($radiant->dota2_teams_name)
                        <h2 id="radiant-victory" class="radiant-color" style="text-align: center;margin: 0;margin-bottom: 10px;font-weight: 800 !important;text-shadow: 1px 1px 5px black;">{{ $radiant->dota2_teams_name }} VICTORY</h2>
                    @else
                        <h2 id="radiant-victory" class="radiant-color" style="text-align: center;margin: 0;margin-bottom: 10px;font-weight: 800 !important;text-shadow: 1px 1px 5px black;">RADIANT VICTORY</h2>
                    @endif
                @endif
            @else
                @if ($radiant->tournament_registration)
                    <h2 id="radiant-victory" class="radiant-color" style="text-align: center;margin: 0;margin-bottom: 10px;font-weight: 800 !important;text-shadow: 1px 1px 5px black;display: none;">{{ $radiant->tournament_registration->team->name }} VICTORY</h2>
                @else
                    @if ($radiant->dota2_teams_name)
                        <h2 id="radiant-victory" class="radiant-color" style="text-align: center;margin: 0;margin-bottom: 10px;font-weight: 800 !important;text-shadow: 1px 1px 5px black;display: none;">{{ $radiant->dota2_teams_name }} VICTORY</h2>
                    @else
                        <h2 id="radiant-victory" class="radiant-color" style="text-align: center;margin: 0;margin-bottom: 10px;font-weight: 800 !important;text-shadow: 1px 1px 5px black;display: none;">RADIANT VICTORY</h2>
                    @endif
                @endif
            @endif
            @if ($dire->matches_result == 3)
                @if ($dire->tournament_registration)
                    <h2 id="dire-victory" class="dire-color" style="text-align: center;margin: 0;margin-bottom: 10px;font-weight: 800 !important;text-shadow: 1px 1px 5px black;">{{ $dire->tournament_registration->team->name }} VICTORY</h2>
                @else
                    @if ($dire->dota2_teams_name)
                        <h2 id="dire-victory" class="dire-color" style="text-align: center;margin: 0;margin-bottom: 10px;font-weight: 800 !important;text-shadow: 1px 1px 5px black;">{{ $dire->dota2_teams_name }} VICTORY</h2>
                    @else
                        <h2 id="dire-victory" class="dire-color" style="text-align: center;margin: 0;margin-bottom: 10px;font-weight: 800 !important;text-shadow: 1px 1px 5px black;">RADIANT VICTORY</h2>
                    @endif
                @endif
            @else
                @if ($dire->tournament_registration)
                    <h2 id="dire-victory" class="dire-color" style="text-align: center;margin: 0;margin-bottom: 10px;font-weight: 800 !important;text-shadow: 1px 1px 5px black;display: none;">{{ $dire->tournament_registration->team->name }} VICTORY</h2>
                @else
                    @if ($dire->dota2_teams_name)
                        <h2 id="dire-victory" class="dire-color" style="text-align: center;margin: 0;margin-bottom: 10px;font-weight: 800 !important;text-shadow: 1px 1px 5px black;display: none;">{{ $dire->dota2_teams_name }} VICTORY</h2>
                    @else
                        <h2 id="dire-victory" class="dire-color" style="text-align: center;margin: 0;margin-bottom: 10px;font-weight: 800 !important;text-shadow: 1px 1px 5px black;display: none;">DIRE VICTORY</h2>
                    @endif
                @endif
            @endif
            <div class="col-xs-12" style="text-align: center;">
                <div style="display: inline-block;text-align: right;vertical-align: middle;">
                    @if ($radiant->tournament_registration)
                        <h2 title="{{ $radiant->tournament_registration->team->name }}" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 250px;max-width: 250px;margin: 0;vertical-align: middle;">{{ $radiant->tournament_registration->team->name }}</h2>
                        @if ($radiant->tournament_registration->team->picture_file_name)
                            <img src="{{ asset('storage/team/'.$radiant->tournament_registration->team->picture_file_name) }}" style="display: inline-block;height: 80px;width: 130px;margin-left: 5px;vertical-align: middle;">
                        @else
                            <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 80px;width: 130px;margin-left: 5px;vertical-align: middle;">
                        @endif
                    @else
                        @if ($radiant->dota2_teams_name)
                            <h2 title="{{ $radiant->dota2_teams_name }}" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 250px;max-width: 250px;margin: 0;vertical-align: middle;">{{ $radiant->dota2_teams_name }}</h2>
                        @else
                            <h2 title="Radiant" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 250px;max-width: 250px;margin: 0;vertical-align: middle;">Radiant</h2>
                        @endif
                        <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 80px;width: 130px;margin-left: 5px;vertical-align: middle;">
                    @endif
                </div>
                <div style="display: inline-block;margin-left: 5px;margin-right: 5px;vertical-align: top;">
                    <h5 id="duration" style="margin-top: 0px; margin-bottom: 10px;color: #5f6472;">{{ (floor($dota2_live_match->duration / 60) < 10 ? '0' : '').floor($dota2_live_match->duration / 60) }}:{{ ($dota2_live_match->duration % 60 < 10 ? '0' : '').$dota2_live_match->duration % 60 }}</h5>
                    <h2 id="score" style="margin: 0;">{{ $radiant->score }} - {{ $dire->score }}</h2>
                    {{-- <h2 id="score" style="margin: 0;">0 - 0</h2> --}}
                </div>
                <div style="display: inline-block;text-align: left;vertical-align: middle;">
                    @if ($dire->tournament_registration)
                        @if ($dire->tournament_registration->team->picture_file_name)
                            <img src="{{ asset('storage/team/'.$dire->tournament_registration->team->picture_file_name) }}" style="display: inline-block;height: 80px;width: 130px;margin-right: 5px;vertical-align: middle;">
                        @else
                            <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 80px;width: 130px;margin-right: 5px;vertical-align: middle;">
                        @endif
                        <h2 title="{{ $dire->tournament_registration->team->name }}" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 250px;max-width: 250px;margin: 0;vertical-align: middle;">{{ $dire->tournament_registration->team->name }}</h2>
                    @else
                        <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 80px;width: 130px;margin-right: 5px;vertical-align: middle;">
                        @if ($dire->dota2_teams_name)
                            <h2 title="{{ $dire->dota2_teams_name }}" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 250px;max-width: 250px;margin: 0;vertical-align: middle;">{{ $dire->dota2_teams_name }}</h2>
                        @else
                            <h2 title="Dire" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 250px;max-width: 250px;margin: 0;vertical-align: middle;">Dire</h2>
                        @endif
                    @endif
                </div>
            </div>
            <div class="col-xs-12">
                <fieldset class="fieldset-custom">
                    <legend>Pick and Ban</legend>
                    <div class="col-xs-6">
                        <h4 class="radiant-color" style="text-align: center;">Radiant</h4>
                        <div>
                            <div style="margin: 5px;">
                                @for ($idx = 0;$idx < 5;$idx++)
                                    <div class="draft-pick">
                                        @if (isset($radiant->heroes_pick[$idx]))
                                            @if ($radiant->heroes_pick[$idx]->picture_file_name)
                                                <img title="{{ $radiant->heroes_pick[$idx]->name }}" src="{{ asset('img/dota-2/heroes/'.$radiant->heroes_pick[$idx]->picture_file_name) }}">
                                            @else
                                                <img title="{{ $radiant->heroes_pick[$idx]->name }}" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                            {{-- <img id="radiant-pick-{{ $idx }}" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                        @else
                                            <img id="radiant-pick-{{ $idx }}" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                        @endif
                                        <div class="label label-custom">
                                            <small><span class="glyphicon glyphicon-ok" aria-hidden="true" style="color:green"></span></small>
                                            <small>PICK</small>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            <div style="margin: 5px;">
                                @for ($idx = 0;$idx < 5;$idx++)
                                    <div class="draft-ban">
                                        @if (isset($radiant->heroes_ban[$idx]))
                                            @if ($radiant->heroes_ban[$idx]->picture_file_name)
                                                <img title="{{ $radiant->heroes_ban[$idx]->name }}" src="{{ asset('img/dota-2/heroes/'.$radiant->heroes_ban[$idx]->picture_file_name) }}">
                                            @else
                                                <img title="{{ $radiant->heroes_ban[$idx]->name }}" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                            {{-- <img id="radiant-ban-{{ $idx }}" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                        @else
                                            <img id="radiant-ban-{{ $idx }}" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                        @endif
                                        <div class="label label-custom">
                                            <small><span class="glyphicon glyphicon-remove" aria-hidden="true" style="color:red"></span></small>
                                            <small>BAN</small>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <h4 class="dire-color" style="text-align: center;">Dire</h4>
                        <div>
                            <div style="margin: 5px;">
                                @for ($idx = 0;$idx < 5;$idx++)
                                    <div class="draft-pick">
                                        @if (isset($dire->heroes_pick[$idx]))
                                            @if ($dire->heroes_pick[$idx]->picture_file_name)
                                                <img title="{{ $dire->heroes_pick[$idx]->name }}" src="{{ asset('img/dota-2/heroes/'.$dire->heroes_pick[$idx]->picture_file_name) }}">
                                            @else
                                                <img title="{{ $dire->heroes_pick[$idx]->name }}" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                            {{-- <img id="dire-pick-{{ $idx }}" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                        @else
                                            <img id="dire-pick-{{ $idx }}" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                        @endif
                                        <div class="label label-custom">
                                            <small><span class="glyphicon glyphicon-ok" aria-hidden="true" style="color:green"></span></small>
                                            <small>PICK</small>
                                        </div>
                                    </div>
                                @endfor                        
                            </div>
                            <div style="margin: 5px;">
                                @for ($idx = 0;$idx < 5;$idx++)
                                    <div class="draft-ban">
                                        @if (isset($dire->heroes_ban[$idx]))
                                            @if ($dire->heroes_ban[$idx]->picture_file_name)
                                                <img title="{{ $dire->heroes_ban[$idx]->name }}" src="{{ asset('img/dota-2/heroes/'.$dire->heroes_ban[$idx]->picture_file_name) }}">
                                            @else
                                                <img title="{{ $dire->heroes_ban[$idx]->name }}" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                            {{-- <img id="dire-ban-{{ $idx }}" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                        @else
                                            <img id="dire-ban-{{ $idx }}" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                        @endif
                                        <div class="label label-custom">
                                            <small><span class="glyphicon glyphicon-remove" aria-hidden="true" style="color:red"></span></small>
                                            <small>BAN</small>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-xs-4">
                <div class="minimap-wrapper">
                    <div class="minimap">
                        <div class="radiant">
                            <?php
                                $tower_status = substr("00000000000", 0, 11 - strlen(decbin($radiant->tower_state))) . decbin($radiant->tower_state);
                                $barrack_status = substr("000000", 0, 6 - strlen(decbin($radiant->barracks_state))) . decbin($radiant->barracks_state);
                            ?>
                            <div class="tower {{ $tower_status[10] ? '' : 'destroy '}}top-1"></div>
                            <div class="tower {{ $tower_status[7] ? '' : 'destroy '}}middle-1"></div>
                            <div class="tower {{ $tower_status[4] ? '' : 'destroy '}}bottom-1"></div>

                            <div class="tower {{ $tower_status[9] ? '' : 'destroy '}}top-2"></div>
                            <div class="tower {{ $tower_status[6] ? '' : 'destroy '}}middle-2"></div>
                            <div class="tower {{ $tower_status[3] ? '' : 'destroy '}}bottom-2"></div>

                            <div class="tower {{ $tower_status[8] ? '' : 'destroy '}}top-3"></div>
                            <div class="tower {{ $tower_status[5] ? '' : 'destroy '}}middle-3"></div>
                            <div class="tower {{ $tower_status[2] ? '' : 'destroy '}}bottom-3"></div>

                            <div class="barrack {{ $barrack_status[4] ? '' : 'destroy '}}top-ranged"></div>
                            <div class="barrack {{ $barrack_status[5] ? '' : 'destroy '}}top-melee"></div>

                            <div class="barrack {{ $barrack_status[2] ? '' : 'destroy '}}middle-ranged"></div>
                            <div class="barrack {{ $barrack_status[3] ? '' : 'destroy '}}middle-melee"></div>

                            <div class="barrack {{ $barrack_status[0] ? '' : 'destroy '}}bottom-ranged"></div>
                            <div class="barrack {{ $barrack_status[1] ? '' : 'destroy '}}bottom-melee"></div>

                            <div class="tower {{ $tower_status[1] ? '' : 'destroy '}}top-4"></div>
                            <div class="tower {{ $tower_status[0] ? '' : 'destroy '}}bottom-4"></div>

                            @if ($dota2_live_match->duration != 0)
                                @foreach ($radiant->dota2_live_match_players as $dota2_live_match_player)
                                    @if ($dota2_live_match_player->hero)
                                        <?php
                                            $max_position_y = 7456;
                                            $min_position_y = 7072;
                                            $position_from_bottom = $min_position_y + $dota2_live_match_player->position_y;
                                            $bottom = ($position_from_bottom / ($max_position_y + $min_position_y) * 248);

                                            $max_position_x = 7540;
                                            $min_position_x = 7392;
                                            $position_from_left = $min_position_x + $dota2_live_match_player->position_x;
                                            $left = ($position_from_left / ($max_position_x + $min_position_x) * 256);
                                        ?>
                                        @if ($dota2_live_match_player->hero->picture_file_name)
                                            <div title="{{ $dota2_live_match_player->hero->name }}" id="player-{{ $dota2_live_match_player->id }}-hero-icon" class="player" style="background-image: url({{ asset('img/dota-2/heroes/mini/'.$dota2_live_match_player->hero->picture_file_name) }});bottom: {{ $bottom }}px;left: {{ $left }}px;"></div>
                                        @else
                                            <div title="{{ $dota2_live_match_player->hero->name }}" id="player-{{ $dota2_live_match_player->id }}-hero-icon" class="player" style="background-image: url({{ asset('img/dota-2/heroes/default.png') }});bottom: {{ $bottom }}px;left: {{ $left }};"></div>
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <div class="dire">
                            <?php
                                $tower_status = substr("00000000000", 0, 11 - strlen(decbin($dire->tower_state))) . decbin($dire->tower_state);
                                $barrack_status = substr("000000", 0, 6 - strlen(decbin($dire->barracks_state))) . decbin($dire->barracks_state);
                            ?>
                            <div class="tower {{ $tower_status[10] ? '' : 'destroy '}}top-1"></div>
                            <div class="tower {{ $tower_status[7] ? '' : 'destroy '}}middle-1"></div>
                            <div class="tower {{ $tower_status[4] ? '' : 'destroy '}}bottom-1"></div>

                            <div class="tower {{ $tower_status[9] ? '' : 'destroy '}}top-2"></div>
                            <div class="tower {{ $tower_status[6] ? '' : 'destroy '}}middle-2"></div>
                            <div class="tower {{ $tower_status[3] ? '' : 'destroy '}}bottom-2"></div>

                            <div class="tower {{ $tower_status[8] ? '' : 'destroy '}}top-3"></div>
                            <div class="tower {{ $tower_status[5] ? '' : 'destroy '}}middle-3"></div>
                            <div class="tower {{ $tower_status[2] ? '' : 'destroy '}}bottom-3"></div>

                            <div class="barrack {{ $barrack_status[4] ? '' : 'destroy '}}top-ranged"></div>
                            <div class="barrack {{ $barrack_status[5] ? '' : 'destroy '}}top-melee"></div>

                            <div class="barrack {{ $barrack_status[2] ? '' : 'destroy '}}middle-ranged"></div>
                            <div class="barrack {{ $barrack_status[3] ? '' : 'destroy '}}middle-melee"></div>

                            <div class="barrack {{ $barrack_status[0] ? '' : 'destroy '}}bottom-ranged"></div>
                            <div class="barrack {{ $barrack_status[1] ? '' : 'destroy '}}bottom-melee"></div>

                            <div class="tower {{ $tower_status[1] ? '' : 'destroy '}}top-4"></div>
                            <div class="tower {{ $tower_status[0] ? '' : 'destroy '}}bottom-4"></div>

                            @if ($dota2_live_match->duration != 0)
                                @foreach ($dire->dota2_live_match_players as $dota2_live_match_player)
                                    @if ($dota2_live_match_player->hero)
                                        <?php
                                            $max_position_y = 7456;
                                            $min_position_y = 7072;
                                            $position_from_bottom = $min_position_y + $dota2_live_match_player->position_y;
                                            $bottom = ($position_from_bottom / ($max_position_y + $min_position_y) * 248);

                                            $max_position_x = 7540;
                                            $min_position_x = 7392;
                                            $position_from_left = $min_position_x + $dota2_live_match_player->position_x;
                                            $left = ($position_from_left / ($max_position_x + $min_position_x) * 256);
                                        ?>
                                        @if ($dota2_live_match_player->hero->picture_file_name)
                                            <div title="{{ $dota2_live_match_player->hero->name }}" id="player-{{ $dota2_live_match_player->id }}-hero-icon" class="player" style="background-image: url({{ asset('img/dota-2/heroes/mini/'.$dota2_live_match_player->hero->picture_file_name) }});bottom: {{ $bottom }}px;left: {{ $left }}px;"></div>
                                        @else
                                            <div title="{{ $dota2_live_match_player->hero->name }}" id="player-{{ $dota2_live_match_player->id }}-hero-icon" class="player" style="background-image: url({{ asset('img/dota-2/heroes/default.png') }});bottom: {{ $bottom }}px;left: {{ $left }};"></div>
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-8">
                <div class="panel with-nav-tabs panel-default panel-custom" style="margin-left: 0;margin-right: 0;">
                    <ul class="nav nav-tabs" style="border:none;">
                        <li class="active">
                            <a href="#net-worth-tab" data-toggle="tab">Net Worth</a>
                        </li>
                        <li style="margin-left:5px;">
                            <a href="#experience-tab" data-toggle="tab">Experience</a>
                        </li>
                    </ul>
                    <div class="tab-content" style="box-shadow: 5px 5px 12px 5px rgba(0,0,0,0.3);background: linear-gradient(to right, #2f313a, #2f3341)">
                        <div class="tab-pane fade in active" id="net-worth-tab">
                            <canvas id="net-worth-canvas" width="100%" height="260"></canvas>
                        </div>
                        <div class="tab-pane fade" id="experience-tab">
                            <canvas id="experience-canvas" width="100%" height="260"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
                <div style="margin: 10px;margin-left: 0;">
                    @if ($radiant->tournament_registration)
                        @if ($radiant->tournament_registration->team->picture_file_name)
                            <img src="{{ asset('storage/team/'.$radiant->tournament_registration->team->picture_file_name) }}" style="display: inline-block;height: 50px;width: 100px;margin-right: 5px;vertical-align: middle;">
                        @else
                            <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 50px;width: 100px;margin-right: 5px;vertical-align: middle;">
                        @endif
                        <h4 title="{{ $radiant->tournament_registration->team->name }}" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 250px;max-width: 250px;vertical-align: middle;">{{ $radiant->tournament_registration->team->name }}</h4>
                    @else
                        <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 50px;width: 100px;margin-right: 5px;vertical-align: middle;">
                        @if ($radiant->dota2_teams_name)
                            <h4 title="{{ $radiant->dota2_teams_name }}" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 250px;max-width: 250px;vertical-align: middle;">{{ $radiant->dota2_teams_name }}</h4>
                        @else
                            <h4 title="Radiant" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 250px;max-width: 250px;vertical-align: middle;">Radiant</h4>
                        @endif
                    @endif
                </div>
                <table class="table table-hover table-striped table-radiant table-match">
                    <thead>
                        <tr>
                            <th class="col-xs-1">Hero</th>
                            <th class="col-xs-3 radiant-color">Player</th>
                            <th class="col-xs-1">K/D/A</th>
                            <th class="col-xs-1">LH/DN</th>
                            <th class="col-xs-1 networth-color">NET</th>
                            <th class="col-xs-3">Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($radiant->dota2_live_match_players as $dota2_live_match_player)
                            <tr id="player-{{ $dota2_live_match_player->id }}-stats">
                                <td>
                                    @if ($dota2_live_match_player->hero)
                                        <img title="{{ $dota2_live_match_player->hero->name }}" class="img-sm" src="{{ asset('img/dota-2/heroes/'.$dota2_live_match_player->hero->picture_file_name) }}">
                                        {{-- <img class="img-sm hero" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                    @else
                                        <img class="img-sm hero" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                    @endif
                                </td>
                                <td class="radiant-color">
                                    @if ($dota2_live_match_player->member)
                                        <div title="{{ $dota2_live_match_player->member->name }}" class="handle-overflow">{{ $dota2_live_match_player->member->name }}</div>
                                    @else
                                        <div title="{{ $dota2_live_match_player->name }}" class="handle-overflow">{{ $dota2_live_match_player->name }}</div>
                                    @endif
                                </td>
                                <td><span class="kills">{{ $dota2_live_match_player->kills }}</span>/<span class="death">{{ $dota2_live_match_player->death }}</span>/<span class="assists">{{ $dota2_live_match_player->assists }}</span></td>
                                {{-- <td><span class="kills">0</span>/<span class="death">0</span>/<span class="assists">0</span></td> --}}
                                <td><span class="last_hits">{{ $dota2_live_match_player->last_hits }}</span>/<span class="denies">{{ $dota2_live_match_player->denies }}</span></td>
                                {{-- <td><span class="last_hits">0</span>/<span class="denies">0</span></td> --}}
                                <td class="networth-color net_worth">
                                    @if ($dota2_live_match_player->net_worth >= 1000)
                                        {{ round($dota2_live_match_player->net_worth / 1000, 1) }}k
                                    @else
                                        {{ $dota2_live_match_player->net_worth }}
                                    @endif
                                </td>
                                {{-- <td class="networth-color net_worth">
                                    625
                                </td> --}}
                                <td class="item">
                                    <div class="item-detail-1">
                                        <div class="item-1">
                                            @if (isset($dota2_live_match_player->items[1]))
                                                @if ($dota2_live_match_player->items[1]->picture_file_name)
                                                    <img title="{{ $dota2_live_match_player->items[1]->name }}" class="img-xs" src="{{ asset('img/dota-2/items/'.$dota2_live_match_player->items[1]->picture_file_name) }}">
                                                @else
                                                    <img title="{{ $dota2_live_match_player->items[1]->name }}" class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                                @endif
                                                {{-- <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                            @else
                                                <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                        </div>
                                        <div class="item-2">
                                            @if (isset($dota2_live_match_player->items[2]))
                                                @if ($dota2_live_match_player->items[2]->picture_file_name)
                                                    <img title="{{ $dota2_live_match_player->items[2]->name }}" class="img-xs" src="{{ asset('img/dota-2/items/'.$dota2_live_match_player->items[2]->picture_file_name) }}">
                                                @else
                                                    <img title="{{ $dota2_live_match_player->items[2]->name }}" class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                                @endif
                                                {{-- <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                            @else
                                                <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                        </div>
                                        <div class="item-3">
                                            @if (isset($dota2_live_match_player->items[3]))
                                                @if ($dota2_live_match_player->items[3]->picture_file_name)
                                                    <img title="{{ $dota2_live_match_player->items[3]->name }}" class="img-xs" src="{{ asset('img/dota-2/items/'.$dota2_live_match_player->items[3]->picture_file_name) }}">
                                                @else
                                                    <img title="{{ $dota2_live_match_player->items[3]->name }}" class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                                @endif
                                                {{-- <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                            @else
                                                <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="item-detail-2">
                                        <div class="item-4">
                                            @if (isset($dota2_live_match_player->items[4]))
                                                @if ($dota2_live_match_player->items[4]->picture_file_name)
                                                    <img title="{{ $dota2_live_match_player->items[4]->name }}" class="img-xs" src="{{ asset('img/dota-2/items/'.$dota2_live_match_player->items[4]->picture_file_name) }}">
                                                @else
                                                    <img title="{{ $dota2_live_match_player->items[4]->name }}" class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                                @endif
                                                {{-- <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                            @else
                                                <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                        </div>
                                        <div class="item-5">
                                            @if (isset($dota2_live_match_player->items[5]))
                                                @if ($dota2_live_match_player->items[5]->picture_file_name)
                                                    <img title="{{ $dota2_live_match_player->items[5]->name }}" class="img-xs" src="{{ asset('img/dota-2/items/'.$dota2_live_match_player->items[5]->picture_file_name) }}">
                                                @else
                                                    <img title="{{ $dota2_live_match_player->items[5]->name }}" class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                                @endif
                                                {{-- <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                            @else
                                                <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                        </div>
                                        <div class="item-6">
                                            @if (isset($dota2_live_match_player->items[6]))
                                                @if ($dota2_live_match_player->items[6]->picture_file_name)
                                                    <img title="{{ $dota2_live_match_player->items[6]->name }}" class="img-xs" src="{{ asset('img/dota-2/items/'.$dota2_live_match_player->items[6]->picture_file_name) }}">
                                                @else
                                                    <img title="{{ $dota2_live_match_player->items[6]->name }}" class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                                @endif
                                                {{-- <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                            @else
                                                <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-xs-6">
                <div style="margin: 10px;margin-left: 0;">
                    @if ($dire->tournament_registration)
                        @if ($dire->tournament_registration->team->picture_file_name)
                            <img src="{{ asset('storage/team/'.$dire->tournament_registration->team->picture_file_name) }}" style="display: inline-block;height: 50px;width: 100px;margin-right: 5px;vertical-align: middle;">
                        @else
                            <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 50px;width: 100px;margin-right: 5px;vertical-align: middle;">
                        @endif
                        <h4 title="{{ $dire->tournament_registration->team->name }}" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 250px;max-width: 250px;vertical-align: middle;">{{ $dire->tournament_registration->team->name }}</h4>
                    @else
                        <img src="{{ asset('img/dota-2/heroes/default.png') }}" style="display: inline-block;height: 50px;width: 100px;margin-right: 5px;vertical-align: middle;">
                        @if ($dire->dota2_teams_name)
                            <h4 title="{{ $dire->dota2_teams_name }}" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 250px;max-width: 250px;vertical-align: middle;">{{ $dire->dota2_teams_name }}</h4>
                        @else
                            <h4 title="Dire" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 250px;max-width: 250px;vertical-align: middle;">Dire</h4>
                        @endif
                    @endif
                </div>
                <table class="table table-hover table-striped table-dire table-match">
                    <thead>
                        <tr>
                            <th class="col-xs-1">Hero</th>
                            <th class="col-xs-3 dire-color">Player</th>
                            <th class="col-xs-1">K/D/A</th>
                            <th class="col-xs-1">LH/DN</th>
                            <th class="col-xs-1 networth-color">NET</th>
                            <th class="col-xs-3">Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dire->dota2_live_match_players as $dota2_live_match_player)
                            <tr id="player-{{ $dota2_live_match_player->id }}-stats">
                                <td>
                                    @if ($dota2_live_match_player->hero)
                                        <img title="{{ $dota2_live_match_player->hero->name }}" class="img-sm" src="{{ asset('img/dota-2/heroes/'.$dota2_live_match_player->hero->picture_file_name) }}">
                                        {{-- <img class="img-sm hero" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                    @else
                                        <img class="img-sm hero" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                    @endif
                                </td>
                                <td class="dire-color">
                                    @if ($dota2_live_match_player->member)
                                        <div title="{{ $dota2_live_match_player->member->name }}" class="handle-overflow">{{ $dota2_live_match_player->member->name }}</div>
                                    @else
                                        <div title="{{ $dota2_live_match_player->name }}" class="handle-overflow">{{ $dota2_live_match_player->name }}</div>
                                    @endif
                                </td>
                                <td><span class="kills">{{ $dota2_live_match_player->kills }}</span>/<span class="death">{{ $dota2_live_match_player->death }}</span>/<span class="assists">{{ $dota2_live_match_player->assists }}</span></td>
                                {{-- <td><span class="kills">0</span>/<span class="death">0</span>/<span class="assists">0</span></td> --}}
                                <td><span class="last_hits">{{ $dota2_live_match_player->last_hits }}</span>/<span class="denies">{{ $dota2_live_match_player->denies }}</span></td>
                                {{-- <td><span class="last_hits">0</span>/<span class="denies">0</span></td> --}}
                                <td class="networth-color net_worth">
                                    @if ($dota2_live_match_player->net_worth >= 1000)
                                        {{ round($dota2_live_match_player->net_worth / 1000, 1) }}k
                                    @else
                                        {{ $dota2_live_match_player->net_worth }}
                                    @endif
                                </td>
                                {{-- <td class="networth-color net_worth">
                                    625
                                </td> --}}
                                <td class="item">
                                    <div class="item-detail-1">
                                        <div class="item-1">
                                            @if (isset($dota2_live_match_player->items[1]))
                                                @if ($dota2_live_match_player->items[1]->picture_file_name)
                                                    <img title="{{ $dota2_live_match_player->items[1]->name }}" class="img-xs" src="{{ asset('img/dota-2/items/'.$dota2_live_match_player->items[1]->picture_file_name) }}">
                                                @else
                                                    <img title="{{ $dota2_live_match_player->items[1]->name }}" class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                                @endif
                                                {{-- <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                            @else
                                                <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                        </div>
                                        <div class="item-2">
                                            @if (isset($dota2_live_match_player->items[2]))
                                                @if ($dota2_live_match_player->items[2]->picture_file_name)
                                                    <img title="{{ $dota2_live_match_player->items[2]->name }}" class="img-xs" src="{{ asset('img/dota-2/items/'.$dota2_live_match_player->items[2]->picture_file_name) }}">
                                                @else
                                                    <img title="{{ $dota2_live_match_player->items[2]->name }}" class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                                @endif
                                                {{-- <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                            @else
                                                <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                        </div>
                                        <div class="item-3">
                                            @if (isset($dota2_live_match_player->items[3]))
                                                @if ($dota2_live_match_player->items[3]->picture_file_name)
                                                    <img title="{{ $dota2_live_match_player->items[3]->name }}" class="img-xs" src="{{ asset('img/dota-2/items/'.$dota2_live_match_player->items[3]->picture_file_name) }}">
                                                @else
                                                    <img title="{{ $dota2_live_match_player->items[3]->name }}" class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                                @endif
                                                {{-- <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                            @else
                                                <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="item-detail-2">
                                        <div class="item-4">
                                            @if (isset($dota2_live_match_player->items[4]))
                                                @if ($dota2_live_match_player->items[4]->picture_file_name)
                                                    <img title="{{ $dota2_live_match_player->items[4]->name }}" class="img-xs" src="{{ asset('img/dota-2/items/'.$dota2_live_match_player->items[4]->picture_file_name) }}">
                                                @else
                                                    <img title="{{ $dota2_live_match_player->items[4]->name }}" class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                                @endif
                                                {{-- <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                            @else
                                                <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                        </div>
                                        <div class="item-5">
                                            @if (isset($dota2_live_match_player->items[5]))
                                                @if ($dota2_live_match_player->items[5]->picture_file_name)
                                                    <img title="{{ $dota2_live_match_player->items[5]->name }}" class="img-xs" src="{{ asset('img/dota-2/items/'.$dota2_live_match_player->items[5]->picture_file_name) }}">
                                                @else
                                                    <img title="{{ $dota2_live_match_player->items[5]->name }}" class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                                @endif
                                                {{-- <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                            @else
                                                <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                        </div>
                                        <div class="item-6">
                                            @if (isset($dota2_live_match_player->items[6]))
                                                @if ($dota2_live_match_player->items[6]->picture_file_name)
                                                    <img title="{{ $dota2_live_match_player->items[6]->name }}" class="img-xs" src="{{ asset('img/dota-2/items/'.$dota2_live_match_player->items[6]->picture_file_name) }}">
                                                @else
                                                    <img title="{{ $dota2_live_match_player->items[6]->name }}" class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                                @endif
                                                {{-- <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                            @else
                                                <img class="img-xs" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-xs-12">
                <div style="margin: 10px;margin-left: 0;">
                    @if ($radiant->tournament_registration)
                        <h4 title="{{ $radiant->tournament_registration->team->name }} Skill Builds" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 500px;max-width: 500px;vertical-align: middle;">{{ $radiant->tournament_registration->team->name }} Skill Builds</h4>                        
                    @else
                        @if ($radiant->dota2_teams_name)
                            <h4 title="{{ $radiant->dota2_teams_name }} Skill Builds" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 500px;max-width: 500px;vertical-align: middle;">{{ $radiant->dota2_teams_name }} Skill Builds</h4>
                        @else
                            <h4 title="Radiant Skill Builds" class="radiant-color handle-overflow" style="display: inline-block;width: auto;min-width: 500px;max-width: 500px;vertical-align: middle;">Radiant Skill Builds</h4>                        
                        @endif
                    @endif
                </div>
                <table class="table table-hover table-striped table-radiant table-match table-ability">
                    <thead>
                        <tr>
                            <th class="col-xs-2">Hero</th>
                            <th class="col-xs-1">1</th>
                            <th class="col-xs-1">2</th>
                            <th class="col-xs-1">3</th>
                            <th class="col-xs-1">4</th>
                            <th class="col-xs-1">5</th>
                            <th class="col-xs-1">6</th>
                            <th class="col-xs-1">7</th>
                            <th class="col-xs-1">8</th>
                            <th class="col-xs-1">9</th>
                            <th class="col-xs-1">10</th>
                            <th class="col-xs-1">11</th>
                            <th class="col-xs-1">12</th>
                            <th class="col-xs-1">13</th>
                            <th class="col-xs-1">14</th>
                            <th class="col-xs-1">15</th>
                            <th class="col-xs-1">16</th>
                            <th class="col-xs-1">17</th>
                            <th class="col-xs-1">18</th>
                            <th class="col-xs-1">19</th>
                            <th class="col-xs-1">20</th>
                            <th class="col-xs-1">21</th>
                            <th class="col-xs-1">22</th>
                            <th class="col-xs-1">23</th>
                            <th class="col-xs-1">24</th>
                            <th class="col-xs-1">25</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($radiant->dota2_live_match_players as $dota2_live_match_player)
                            <tr id="player-{{ $dota2_live_match_player->id }}-abilities">
                                <td>
                                    @if ($dota2_live_match_player->hero)
                                        <img title="{{ $dota2_live_match_player->hero->name }}" class="img-sm" src="{{ asset('img/dota-2/heroes/'.$dota2_live_match_player->hero->picture_file_name) }}">
                                        {{-- <img class="img-sm hero" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                    @else
                                        <img class="img-sm hero" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                    @endif
                                </td>
                                @for ($idx = 1;$idx <= 25;$idx++)
                                    <?php $skill_idx = null; ?>
                                    @if ($idx <= 16)
                                        <?php $skill_idx = $idx; ?>
                                    @elseif ($idx == 18)
                                        <?php $skill_idx = 17; ?>
                                    @elseif ($idx == 20)
                                        <?php $skill_idx = 18; ?>
                                    @elseif ($idx == 25)
                                        <?php $skill_idx = 19; ?>
                                    @endif

                                    @if ($dota2_live_match_player->hero)
                                        @if ($dota2_live_match_player->hero->id == 74)
                                            <?php $skill_idx = $idx; ?>
                                        @endif

                                        @if ($skill_idx !== null)
                                            @if (isset($dota2_live_match_player->abilities[$skill_idx]))
                                                <td>
                                                    @if ($dota2_live_match_player->abilities[$skill_idx]->picture_file_name)
                                                        <img title="{{ $dota2_live_match_player->abilities[$skill_idx]->name }}" class="img-sm" src="{{ asset('img/dota-2/abilities/'.$dota2_live_match_player->abilities[$skill_idx]->picture_file_name) }}">
                                                    @else
                                                        <img title="{{ $dota2_live_match_player->abilities[$skill_idx]->name }}" class="img-sm" src="{{ asset('img/dota-2/heroes/default.png') }}" style="width: 24px;">
                                                    @endif
                                                </td>
                                            @else
                                                <td class="ability-{{ $skill_idx }}"></td>
                                            @endif
                                        @else
                                            <td></td>
                                        @endif
                                    @else
                                        @if ($skill_idx)
                                            <td class="ability-{{ $skill_idx }}"></td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endif
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-xs-12">
                <div style="margin: 10px;margin-left: 0;">
                    @if ($dire->tournament_registration)
                        <h4 title="{{ $dire->tournament_registration->team->name }} Skill Builds" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 500px;max-width: 500px;vertical-align: middle;">{{ $dire->tournament_registration->team->name }} Skill Builds</h4>                        
                    @else
                        @if ($dire->dota2_teams_name)
                            <h4 title="{{ $dire->dota2_teams_name }} Skill Builds" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 500px;max-width: 500px;vertical-align: middle;">{{ $dire->dota2_teams_name }} Skill Builds</h4>
                        @else
                            <h4 title="Dire Skill Builds" class="dire-color handle-overflow" style="display: inline-block;width: auto;min-width: 500px;max-width: 500px;vertical-align: middle;">Dire Skill Builds</h4>                        
                        @endif
                    @endif
                </div>
                <table class="table table-hover table-striped table-dire table-match table-ability" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th class="col-xs-2">Hero</th>
                            <th class="col-xs-1">1</th>
                            <th class="col-xs-1">2</th>
                            <th class="col-xs-1">3</th>
                            <th class="col-xs-1">4</th>
                            <th class="col-xs-1">5</th>
                            <th class="col-xs-1">6</th>
                            <th class="col-xs-1">7</th>
                            <th class="col-xs-1">8</th>
                            <th class="col-xs-1">9</th>
                            <th class="col-xs-1">10</th>
                            <th class="col-xs-1">11</th>
                            <th class="col-xs-1">12</th>
                            <th class="col-xs-1">13</th>
                            <th class="col-xs-1">14</th>
                            <th class="col-xs-1">15</th>
                            <th class="col-xs-1">16</th>
                            <th class="col-xs-1">17</th>
                            <th class="col-xs-1">18</th>
                            <th class="col-xs-1">19</th>
                            <th class="col-xs-1">20</th>
                            <th class="col-xs-1">21</th>
                            <th class="col-xs-1">22</th>
                            <th class="col-xs-1">23</th>
                            <th class="col-xs-1">24</th>
                            <th class="col-xs-1">25</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dire->dota2_live_match_players as $dota2_live_match_player)
                            <tr id="player-{{ $dota2_live_match_player->id }}-abilities">
                                <td>
                                    @if ($dota2_live_match_player->hero)
                                        <img title="{{ $dota2_live_match_player->hero->name }}" class="img-sm" src="{{ asset('img/dota-2/heroes/'.$dota2_live_match_player->hero->picture_file_name) }}">
                                        {{-- <img class="img-sm hero" src="{{ asset('img/dota-2/heroes/default.png') }}"> --}}
                                    @else
                                        <img class="img-sm hero" src="{{ asset('img/dota-2/heroes/default.png') }}">
                                    @endif
                                </td>
                                @for ($idx = 1;$idx <= 25;$idx++)
                                    <?php $skill_idx = null; ?>
                                    @if ($idx <= 16)
                                        <?php $skill_idx = $idx; ?>
                                    @elseif ($idx == 18)
                                        <?php $skill_idx = 17; ?>
                                    @elseif ($idx == 20)
                                        <?php $skill_idx = 18; ?>
                                    @elseif ($idx == 25)
                                        <?php $skill_idx = 19; ?>
                                    @endif

                                    @if ($dota2_live_match_player->hero)
                                        @if ($dota2_live_match_player->hero->id == 74)
                                            <?php $skill_idx = $idx; ?>
                                        @endif

                                        @if ($skill_idx !== null)
                                            @if (isset($dota2_live_match_player->abilities[$skill_idx]))
                                                <td>
                                                    @if ($dota2_live_match_player->abilities[$skill_idx]->picture_file_name)
                                                        <img title="{{ $dota2_live_match_player->abilities[$skill_idx]->name }}" class="img-sm" src="{{ asset('img/dota-2/abilities/'.$dota2_live_match_player->abilities[$skill_idx]->picture_file_name) }}">
                                                    @else
                                                        <img title="{{ $dota2_live_match_player->abilities[$skill_idx]->name }}" class="img-sm" src="{{ asset('img/dota-2/heroes/default.png') }}" style="width: 24px;">
                                                    @endif
                                                </td>
                                            @else
                                                <td class="ability-{{ $skill_idx }}"></td>
                                            @endif
                                        @else
                                            <td></td>
                                        @endif
                                    @else
                                        @if ($skill_idx)
                                            <td class="ability-{{ $skill_idx }}"></td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endif
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="comment-container">
        <div class="comment-open-action">
            <img src="{{ asset('img/open-comment.png') }}">
        </div>
        <div class="comment-close-action" style="display: none;">
            <img src="{{ asset('img/close-comment.png') }}">
        </div>
        <div class="comment-container-scroll scrollbar-macosx">
            @if ($participant)
                <form id="post-comment">
                    <h4>Leave a comment</h4>
                    <textarea class="form-control" id="comment" name="comment" rows="4" placeholder="Leave a comment here..." required="required"></textarea>
                    <div class="text-right">
                        <button type="submit" id="btn-post-comment" class="btn btn-custom ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9">
                            <span class="ladda-label"><i class="fa fa-paper-plane"></i> Post Comment</span>
                        </button>
                    </div>
                </form>
            @endif
            <div id="comment-main" class="comment-main">
                @foreach ($dota2_live_match_comments as $dota2_live_match_comment)
                    <div class="comment-content">
                        <div class="comment-profile">
                            @if ($dota2_live_match_comment->member->picture_file_name)
                                <img class="comment-profile-img" src="{{ asset('storage/member/'.$dota2_live_match_comment->member->picture_file_name) }}">
                            @else
                                <img class="comment-profile-img" src="{{ asset('img/default-profile.jpg') }}">
                            @endif
                        </div>
                        <div class="comment-detail">
                            <p style="color: #9d9d9d;font-weight:bold;">{{ $dota2_live_match_comment->member->name }}</p>
                            <p style="color: #fff">{!! str_replace(PHP_EOL, '<br />', $dota2_live_match_comment->detail) !!}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('participant.footer.footer')
@endsection

@section('script')
    <script type="text/javascript">
        var PUSHER_APP_KEY = "{{ env('PUSHER_KEY', '') }}";

        var durationIntervalID = false;
        @if ($radiant->matches_result === null && $dire->matches_result === null)
            var duration = {{ $dota2_live_match->duration }};
            var duration_element = document.getElementById("duration");

            @if ($dota2_live_match->duration != 0)
                var durationIntervalID = setInterval(durationTick, 1000);
            @endif

            function durationTick() {
                duration++;

                var timestamp_min = Math.floor(duration / 60);
                var timestamp_sec = duration % 60;
                var timestamp = (timestamp_min < 10 ? "0" : "") + timestamp_min + ":" + (timestamp_sec < 10 ? "0" : "") + timestamp_sec;
                duration_element.innerHTML = timestamp;
            }
        @endif

        var duration_labels = {!! json_encode($duration) !!};
        var radiant_statistics = {!! json_encode($radiant_statistics) !!};
        var dire_statistics = {!! json_encode($dire_statistics) !!};
    </script>
    <script src="https://js.pusher.com/4.0/pusher.min.js"></script>
    <script src="{{ asset('js/jquery.scrollbar.min.js') }}"></script>
    <script src="{{ asset('vendor/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('vendor/chart.js/dist/Chart.min.js') }}"></script>
    <script src="{{ asset('js/participant/dota2-match-detail.js') }}"></script>
@endsection
