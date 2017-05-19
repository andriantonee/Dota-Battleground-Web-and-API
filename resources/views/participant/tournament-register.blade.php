@extends('participant.main.master')

@section('title', 'Tournament Register')

@section('style')
    <link href="{{ asset('css/participant/footer.css') }}" rel="stylesheet">
    <style type="text/css">
        #tournament-register-container {
            min-height: 536px;
        }

        #tournament-register-header {
            padding: 0 15px;
        }
        #tournament-register-header > div.col-xs-12 {
            border-bottom: 1px solid black;
        }
        #tournament-register-header h3 {
            margin-top: 0;
        }

        #tournament-register-body {
            margin-bottom: 15px;
            margin-top: 15px;
            padding: 0 15px;
        }
        #tournament-register-body > div:last-child {
            border-left: 1px solid black;
            min-height: 469px;
        }
        p.tournament-register-body-title {
            margin-bottom: 5px;
        }
        h3.tournament-register-body-title {
            border-bottom: 1px solid black;
            margin-bottom: 0;
            margin-top: 0;
            padding-bottom: 5px;
        }
        .list-group-team {
            border: 1px solid black;
            max-height: 104px;
            margin-bottom: 10px;
            min-height: 104px;
            padding: 15px;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .list-group-team-item {
            border: 1px solid black;
            padding: 5px;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            -o-user-select: none;
        }
        .list-group-team-item:hover {
            background-color: #ddd !important;
            cursor: pointer;
        }
        .list-group-team-item:first-child {
            margin-bottom: 15px;
        }
        .list-group-team-item:last-child {
            margin-bottom: 0px !important;
        }
        .list-group-team-item+.list-group-team-item {
            margin-bottom: 15px;
        }
        .list-group-team-item-img {
            display: inline-block;
            vertical-align: middle;
        }
        .list-group-team-item-img > img {
            height: 60px;
            width: 60px;
        }
        .list-group-team-item-detail {
            display: inline-block;
            margin: 0 10px;
            min-width: 239px;
            max-width: 239px;
            vertical-align: middle;
        }
        .list-group-team-item-detail > h4, .list-group-team-item-detail > p {
            margin: 0;
            overflow: hidden;
            text-overflow: clip;
            white-space: nowrap;
        }
        .list-group-team-item-mark {
            display: none;
            font-size: 40px;
            vertical-align: middle;
        }
        .list-group-team-item.selected {
            background-color: #eee;
        }
        .list-group-team-item.selected .list-group-team-item-detail {
            max-width: 195px;
            min-width: 195px;
        }
        .list-group-team-item.selected .list-group-team-item-mark {
            display: inline-block;
        }
        .no-team-available {
            color: #eee;
            font-size: 20px;
            margin: 0;
        }
        .no-team-available > i.fa.fa-times {
            font-size: 43px;
        }

        .list-group-member {
            border: 1px solid black;
            max-height: 212px;
            min-height: 212px;
            padding: 10px 30px;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .list-group-member-item {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            -o-user-select: none;
        }
        .list-group-member-item:first-child {
            margin-bottom: 10px;
        }
        .list-group-member-item+.list-group-member-item {
            margin-bottom: 10px;
        }
        .list-group-member-item:last-child {
            margin-bottom: 0px !important;
        }
        .list-group-member-item-img {
            display: inline-block;
            vertical-align: middle;
        }
        .list-group-member-item-img > img {
            height: 30px;
            width: 30px;
        }
        .list-group-member-item-detail {
            display: inline-block;
            margin: 0 10px;
            min-width: 234px;
            max-width: 234px;
            vertical-align: middle;
        }
        .list-group-member-item-detail > p {
            margin: 0;
            overflow: hidden;
            text-overflow: clip;
            white-space: nowrap;
        }
        .list-group-member-item-detail > p > label {
            cursor: pointer;
            font-weight: normal;
            margin: 0;
        }
        .list-group-member-item-checkbox {
            display: inline-block;
            line-height: 13px;
            vertical-align: middle;
        }
        .list-group-member-item-checkbox input[type="checkbox"] {
            cursor: pointer;
            margin: 0;
        }
        .selected-member-container {
            border: 1px solid black;
            border-top: 0;
            margin-bottom: 10px;
        }
        .selected-member-container p:first-child {
            border-right: 1px solid black;
            padding-left: 30px;
            width: 304px;
        }
        .selected-member-container p {
            display: inline-block;
            font-size: large;
            font-weight: bold;
            margin: 0;
            padding: 5px;
        }
        .no-team-selected {
            color: #eee;
            font-size: 32px;
        }
        .no-team-selected > i.fa.fa-times {
            font-size: 124px;
        }
        .button-container * {
            margin: 0 15px;
            width: 120px;
        }

        .payment-tutorial-container {
            margin-top: 20px;
        }
        .payment-tutorial-container li {
            padding: 0 15px;
        }
        .list-group-bank-container {
            margin-bottom: 15px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 5px;
        }
        .list-group-bank {
            display: inline-block;
        }
        .list-group-bank:first-child {
            margin-right: 32px;
        }
        .list-group-bank-item {
            border: 1px solid black;
            width: 180px;
        }
        .list-group-bank-item img {
            border-bottom: 1px solid black;
            height: 50px;
            padding: 5px;
            width: 100%;
        }
        .list-group-bank-item-detail {
            text-align: center;
            width: 100%;
        }
        .list-group-bank-item-detail h3 {
            font-weight: bold;
            margin-bottom: 5px;
            margin-top: 10px;
        }
        .list-group-bank-item-detail p {
            margin-bottom: 10px;
        }
        p.note-book {
            font-size: x-small;
        }
    </style>
@endsection

@section('content')
    <div id="tournament-register-container" class="container">
        <div id="tournament-register-header" class="row">
            <div class="col-xs-12">
                <h3>Register {{ $tournament->name }}</h3>
            </div>
        </div>
        <div id="tournament-register-body" class="row">
            <div class="col-xs-5">
                <form id="form-tournament-register" data-tournament-name="{{ $tournament->name }}">
                    <p class="tournament-register-body-title">Select Your Team</p>
                    <input type="hidden" name="team" value="" id="team">
                    <div class="list-group-team">
                        @if (count($teams) > 0)
                            @foreach ($teams as $team)
                                <div class="list-group-team-item" data-team-id="{{ $team->id }}">
                                    <div class="list-group-team-item-img">
                                        @if ($team->picture_file_name)
                                            <img src="{{ asset('storage/team/'.$team->picture_file_name) }}">
                                        @else
                                            <img src="{{ asset('img/default-group.png') }}">
                                        @endif
                                    </div>
                                    <div class="list-group-team-item-detail">
                                        <h4>{{ $team->name }}</h4>
                                        <p>{{ $team->details_count }} Member</p>
                                    </div>
                                    <div class="list-group-team-item-mark">
                                        <span class="text-success fa fa-check"></span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center">
                                <p class="no-team-available"><i class="fa fa-times"></i></p>
                                <p class="no-team-available">No Team Available</p>
                            </div>
                        @endif
                    </div>
                    <p class="tournament-register-body-title">Choose Your Player</p>
                    <div id="list-member-container" class="list-group-member">
                        <div class="text-center">
                            <p class="no-team-selected"><i class="fa fa-times"></i></p>
                            <p class="no-team-selected">No Team Selected</p>
                        </div>
                    </div>
                    <div class="selected-member-container">
                        <p>Player Left</p>
                        <p id="player-left">5</p>
                    </div>
                    <div class="button-container text-center">
                        <button type="submit" id="btn-tournament-register" class="btn btn-success" disabled="disabled">Save</button>
                        <a href="{{ url('tournament/'.$tournament->id) }}" class="btn btn-danger">Cancel</a>
                    </div>
                </form>
            </div>
            <div class="col-xs-offset-1 col-xs-6">
                <h3 class="tournament-register-body-title">How to Make a Payment</h3>
                <div class="payment-tutorial-container">
                    <ol>
                        <li>
                            Transfer the amount of registration fee to one of the following banks
                            <div class="list-group-bank-container">
                                <div class="list-group-bank">
                                    <div class="list-group-bank-item">
                                        <img src="{{ asset('img/bank/bca.png') }}">
                                        <div class="list-group-bank-item-detail">
                                            <h3>BCA</h3>
                                            <p>
                                                000 000 0000<br />
                                                a/n Dota Battleground
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-bank">
                                    <div class="list-group-bank-item">
                                        <img src="{{ asset('img/bank/bri.png') }}">
                                        <div class="list-group-bank-item-detail">
                                            <h3>BRI</h3>
                                            <p>
                                                000 000 0000<br />
                                                a/n Dota Battleground
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>Confirm your payment right away after the transaction is done.<br />Payment without confirmation is not accepted.</li>
                    </ol>
                    <p class="note-book">NB: Confirmation page can be accessed from registration list at your profile.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('participant.footer.footer')
@endsection

@section('script')
    <script src="{{ asset('js/participant/tournament-register.js') }}"></script>
@endsection
