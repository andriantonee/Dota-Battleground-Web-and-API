@extends('participant.main.master')

@section('title', 'Tournament')

@section('style')
    <link href="{{ asset('css/participant/footer.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/search-input.css') }}" rel="stylesheet">
    <style type="text/css">
        button.list-group-item:focus {
            outline: none;
        }

        #tournament-list-container {
            min-height: 536px;
        }

        .tournament-list-group-item {
            color:#e3e3e3;
            border: 1px solid #5f6471;
            background: linear-gradient(to right, #2f313a, #2f3341);
            box-shadow: 5px 5px 12px 5px rgba(0,0,0,0.5);
            border-radius: 3px;
            display: block;
            height: 130px;
            padding: 15px;
        }
        .tournament-list-group-item:hover {
            background: linear-gradient(to right, #323645,#3d3f4b);
            color: #fff;
            text-decoration: none;
        }
        .tournament-list-group-item:focus {
            color: #fff;
            text-decoration: none;
        }
        .tournament-list-group-item:first-child {
            margin-bottom: 5px;
        }
        .tournament-list-group-item+.tournament-list-group-item {
            margin-bottom: 5px;
        }
        .tournament-list-group-item:last-child {
            margin-bottom: 0px;
        }

        .tournament-list-group-item-logo {
            display: inline-block;
            vertical-align: middle;
            width: 100px;
            margin-right: 10px;
        }
        .tournament-list-group-item-logo > img {
            width: 100px;
            height: 100px;
            border: 1px solid black;
        }

        .tournament-list-group-item-detail-1 {
            display: inline-block;
            vertical-align: top;
            width: 260px;
        }
        .tournament-list-group-item-detail-1-name {
            margin-bottom: 0px;
        }
        .tournament-list-group-item-detail-1-organizer-name {
            margin-top: 5px;
            margin-bottom: 0px;
            font-size: 11px;
        }
        .tournament-list-group-item-detail-1-event-date {
            margin-top: 5px;
            font-size: 12px;
        }

        .tournament-list-group-item-detail-2 {
            display: inline-block;
            vertical-align: top;
            width: 242px;
            text-align: right;
        }
        .tournament-list-group-item-detail-2-price {
            margin-bottom: 15px;
        }
        .tournament-list-group-item-detail-2-registration-closed {
            margin-bottom: 20px;
            font-size: 12px;
        }
        .tournament-list-group-item-detail-2-status {
            font-size: 10px;
        }
        .list-group > button {
            background: linear-gradient(to right, #232520, #272A30);
            border: 1px solid #5f6471;
            color:#D8D8D8;
        }
        .list-group > button:hover {
            color:#fff;
            background: linear-gradient(to right, #34372f, #3f444e);
        }
        .list-group > button:focus {
            color:#D8D8D8;
        }
        .list-group-item-detail {
            color:#D8D8D8;
            border: 1px solid #5f6471;
            background: linear-gradient(to right, #2f313a, #2f3341);
            border-top: 0;
            padding: 5px 15px;
        }
    </style>
@endsection

@section('content')
    <div id="tournament-list-container" class="container">
        <div class="row" style="border-bottom: 1px solid #cecece;padding-bottom: 10px;height: 84px;">
            <div class="col-xs-6" style="height: 100%;">
                <h1 style="color:#fff;line-height: 84px;margin-top: 0px;margin-bottom: 0px;">Tournament</h1>
            </div>
            <div class="col-xs-6">
                <div class="input-group stylish-input-group">
                    <span class="input-group-addon">
                        <i class="glyphicon glyphicon-search"></i>
                    </span>
                    <input type="text" id="txtbox-search-team" class="form-control" placeholder="Search name..." >
                </div>
                <div style="margin-top: 5px;">
                    <label class="radio-inline">
                        <input type="radio" name="tournament_filter_status" id="tournament-filter-status-all" value="1"> All
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="tournament_filter_status" id="tournament-filter-status-upcoming" value="2"> Upcoming
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="tournament_filter_status" id="tournament-filter-status-in-progress" value="3"> In Progress
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="tournament_filter_status" id="tournament-filter-status-completed" value="4"> Completed
                    </label>
                    <select class="form-control" id="team-size" name="team_size" style="display: inline-block;width: 125px;margin-left: 10px;">
                        <option value="1" selected="selected">A through Z</option>
                        <option value="2">Start Date</option>
                        <option value="3">Registration End</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 15px;margin-bottom: 15px;">
            <div class="col-xs-4">
                <div class="list-group" style="margin-bottom: 10px;box-shadow: 5px 5px 12px 5px rgba(0,0,0,0.5);">
                    <button class="list-group-item" data-toggle="collapse" data-target="#filter-prices" aria-expanded="false" aria-controls="filter-prices" style="border-top-left-radius: 0;border-top-right-radius: 0;">Prices</button>
                    <div class="collapse in list-group-item-detail" id="filter-prices">
                        <div class="radio" style="margin-top: 0;">
                            <label>
                                <input type="radio" name="tournament_filter_prices" id="tournament-filter-prices-1" value="1">&nbsp;Dibawah Rp. 50.000
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="tournament_filter_prices" id="tournament-filter-prices-2" value="2">&nbsp;Rp. 50.000 - Rp. 100.000
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="tournament_filter_prices" id="tournament-filter-prices-3" value="3">&nbsp;Rp. 100.000 - Rp. 150.000
                            </label>
                        </div>
                        <div class="radio" style="margin-bottom: 0;">
                            <label>
                                <input type="radio" name="tournament_filter_prices" id="tournament-filter-prices-4" value="4">&nbsp;Diatas Rp. 150.000
                            </label>
                        </div>
                    </div>
                    <button class="list-group-item" data-toggle="collapse" data-target="#filter-date-and-location" aria-expanded="false" aria-controls="filter-date-and-location">Date &amp; Location</button>
                    <div class="collapse in list-group-item-detail" id="filter-date-and-location">
                        <div class="form-group" style="margin-bottom: 5px;">
                            <label for="start-date" class="control-label" style="font-weight: normal;">Start Date</label>
                            <div class="input-group date" id="start-date-datetimepicker">
                                <input type="text" class="form-control" id="start-date" name="start_date">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 5px;">
                            <label for="start-date" class="control-label" style="font-weight: normal;">Location</label>
                            <select class="form-control" id="team-size" name="team_size">
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button class="form-control btn btn-default btn-custom">Filter</button>
                </div>
            </div>
            <div class="col-xs-8">
                @foreach ($tournaments as $tournament)
                    <a href="{{ url('/tournament/'.$tournament->id) }}" class="tournament-list-group-item">
                        <div class="tournament-list-group-item-logo">
                            <img src="{{ asset('storage/tournament/'.$tournament->logo_file_name) }}">
                        </div>
                        <div class="tournament-list-group-item-detail-1">
                            <h4 class="tournament-list-group-item-detail-1-name" style="color: #f45138;">{{ $tournament->name }}</h4>
                            <p class="tournament-list-group-item-detail-1-organizer-name" style="color:#afaeae">{{ $tournament->owner->name }}</p>
                            <p class="tournament-list-group-item-detail-1-event-date">{{ date('d F Y', strtotime($tournament->start_date)) }} - {{ date('d F Y', strtotime($tournament->end_date)) }}</p>
                        </div>
                        <div class="tournament-list-group-item-detail-2">
                            <h4 class="tournament-list-group-item-detail-2-price" style="color: #fc7b67;">Rp. {{ number_format($tournament->entry_fee, 0, ',', '.') }} / Team</h4>
                            <p class="tournament-list-group-item-detail-2-registration-closed">Registration Before {{ date('d F Y H:i', strtotime($tournament->registration_closed)) }}</p>
                            <p class="tournament-list-group-item-detail-2-status" style="color:#5f6472">Upcoming</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('participant.footer.footer')
@endsection

@section('script')
@endsection
