@extends('participant.main.master')

@section('title', 'Tournament')

@section('style')
    <link href="{{ asset('css/participant/footer.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/search-input.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/modify-pagination.css') }}" rel="stylesheet">
    <style type="text/css">

        button.list-group-item:focus {
            outline: none;
        }

        #tournament-list-container {
            min-height: 536px;
        }

        .tournament-list-group-item {
            height: 130px;
            padding: 15px;
        }
        .tournament-list-group-item:first-child {
            margin-bottom: 10px;
        }
        .tournament-list-group-item+.tournament-list-group-item {
            margin-bottom: 10px;
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
                    <input type="text" name="txtbox_search_tournament" id="txtbox-search-tournament" class="form-control" value="{{ $name }}" placeholder="Search name...">
                </div>
                <div style="margin-top: 5px;">
                    <label class="radio-inline">
                        @if ($status == 1)
                            <input type="radio" name="tournament_filter_status" id="tournament-filter-status-all" value="1" checked="checked"> All
                        @else
                            <input type="radio" name="tournament_filter_status" id="tournament-filter-status-all" value="1"> All
                        @endif
                    </label>
                    <label class="radio-inline">
                        @if ($status == 2)
                            <input type="radio" name="tournament_filter_status" id="tournament-filter-status-upcoming" value="2" checked="checked"> Upcoming
                        @else
                            <input type="radio" name="tournament_filter_status" id="tournament-filter-status-upcoming" value="2"> Upcoming
                        @endif
                    </label>
                    <label class="radio-inline">
                        @if ($status == 3)
                            <input type="radio" name="tournament_filter_status" id="tournament-filter-status-in-progress" value="3" checked="checked"> In Progress
                        @else
                            <input type="radio" name="tournament_filter_status" id="tournament-filter-status-in-progress" value="3"> In Progress
                        @endif
                    </label>
                    <label class="radio-inline">
                        @if ($status == 4)
                            <input type="radio" name="tournament_filter_status" id="tournament-filter-status-completed" value="4" checked="checked"> Completed
                        @else
                            <input type="radio" name="tournament_filter_status" id="tournament-filter-status-completed" value="4"> Completed
                        @endif
                    </label>
                    <select class="form-control" name="tournament_ordering" id="tournament-ordering" style="display: inline-block;width: 125px;margin-left: 10px;">
                        @if ($order == 1)  
                            <option value="1" selected="selected">A through Z</option>
                        @else
                            <option value="1">A through Z</option>
                        @endif
                        @if ($order == 2)
                            <option value="2" selected="selected">Start Date</option>
                        @else
                            <option value="2">Start Date</option>
                        @endif
                        @if ($order == 3)
                            <option value="3" selected="selected">Registration End</option>
                        @else
                            <option value="3">Registration End</option>
                        @endif
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
                                @if ($price == 1)
                                    <input type="radio" name="tournament_filter_prices" id="tournament-filter-prices-1" value="1" checked="checked">&nbsp;Below Rp. 50.000
                                @else
                                    <input type="radio" name="tournament_filter_prices" id="tournament-filter-prices-1" value="1">&nbsp;Below Rp. 50.000
                                @endif
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                @if ($price == 2)
                                    <input type="radio" name="tournament_filter_prices" id="tournament-filter-prices-2" value="2" checked="checked">&nbsp;Rp. 50.000 - Rp. 100.000
                                @else
                                    <input type="radio" name="tournament_filter_prices" id="tournament-filter-prices-2" value="2">&nbsp;Rp. 50.000 - Rp. 100.000
                                @endif
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                @if ($price == 3)
                                    <input type="radio" name="tournament_filter_prices" id="tournament-filter-prices-3" value="3" checked="checked">&nbsp;Rp. 100.000 - Rp. 150.000
                                @else
                                    <input type="radio" name="tournament_filter_prices" id="tournament-filter-prices-3" value="3">&nbsp;Rp. 100.000 - Rp. 150.000
                                @endif
                            </label>
                        </div>
                        <div class="radio" style="margin-bottom: 0;">
                            <label>
                                @if ($price == 4)
                                    <input type="radio" name="tournament_filter_prices" id="tournament-filter-prices-4" value="4" checked="checked">&nbsp;Above Rp. 150.000
                                @else
                                    <input type="radio" name="tournament_filter_prices" id="tournament-filter-prices-4" value="4">&nbsp;Above Rp. 150.000
                                @endif
                            </label>
                        </div>
                    </div>
                    <button class="list-group-item" data-toggle="collapse" data-target="#filter-date-and-location" aria-expanded="false" aria-controls="filter-date-and-location">Date &amp; Location</button>
                    <div class="collapse in list-group-item-detail" id="filter-date-and-location">
                        <div class="form-group" style="margin-bottom: 5px;">
                            <label for="start-date" class="control-label" style="font-weight: normal;">Start Date</label>
                            <div class="input-group date" id="start-date-datetimepicker">
                                <input type="text" class="form-control" id="start-date" name="start_date" value="{{ $start_date }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 5px;">
                            <label for="city" class="control-label" style="font-weight: normal;">Location</label>
                            <select class="form-control" id="city" name="city">
                                @if ($selected_city)
                                    <option value disabled="disabled">-- Filter City --</option>
                                @else
                                    <option value disabled="disabled" selected="selected">-- Filter City --</option>
                                @endif
                                @foreach ($cities as $city)
                                    @if ($selected_city == $city->id)
                                        <option value="{{ $city->id }}" selected="selected">{{ $city->name }}</option>
                                    @else
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button class="form-control btn btn-default btn-custom" id="btn-filter-tournament">Filter</button>
                </div>
            </div>
            <div class="col-xs-8">
                @if (count($tournaments) > 0)
                    @foreach ($tournaments as $key_tournament => $tournament)
                        @if (($key_tournament + 1) % 10 == 1)
                            @if ($key_tournament + 1 == 1)
                                <div id="tournament-list-container-{{ ceil(($key_tournament + 1) / 10) }}">
                            @else
                                <div id="tournament-list-container-{{ ceil(($key_tournament + 1) / 10) }}" style="display: none;">
                            @endif
                        @endif
                            <a href="{{ url('/tournament/'.$tournament->id) }}" class="well-custom tournament-list-group-item">
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
                                    @if ($tournament->cancel == 0) 
                                        @if (date('Y-m-d H:i:s') <= $tournament->registration_closed)
                                            <p class="tournament-list-group-item-detail-2-status" style="color:#5f6472">Upcoming</p>
                                        @else
                                            @if ($tournament->start == 0)
                                                <p class="tournament-list-group-item-detail-2-status" style="color:#5f6472">Upcoming</p>
                                            @else
                                                @if ($tournament->complete == 0)
                                                    <p class="tournament-list-group-item-detail-2-status" style="color:#5f6472">In Progress</p>
                                                @else
                                                    <p class="tournament-list-group-item-detail-2-status" style="color:#5f6472">Complete</p>
                                                @endif
                                            @endif
                                        @endif
                                    @elseif ($tournament->cancel == 1)
                                        <p class="tournament-list-group-item-detail-2-status" style="color:#5f6472">Cancel</p>
                                    @endif
                                </div>
                            </a>
                        @if (($key_tournament + 1) % 10 == 0 || ($key_tournament + 1) == count($tournaments))
                            </div>
                        @endif
                    @endforeach
                    <nav aria-label="Page navigation" style="text-align: center;">
                        <ul id="tournament-pagination" class="pagination pagination-custom">
                            <li class="disabled">
                                <a href="#previous" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            @for ($page_start = 1; $page_start <= (ceil(count($tournaments) / 10)); $page_start++)
                                @if ($page_start == 1)
                                    <li class="active btn-custom">
                                @else
                                    <li>
                                @endif
                                    <a href="#tournament-list-container-{{ $page_start }}">{{ $page_start }}</a>
                                </li>
                            @endfor
                            @if (ceil(count($tournaments) / 10) == 1)
                                <li class="disabled">
                            @else
                                <li>
                            @endif
                                <a href="#next" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                @else
                    <div class="text-center" style="opacity: 0.2;">
                        <div>
                            <i class="fa fa-times" aria-hidden="true" style="font-size: 192px;"></i>
                        </div>
                        <strong style="font-size: 64px;">No Tournament Available</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('participant.footer.footer')
@endsection

@section('script')
    <script src="{{ asset('vendor/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('js/participant/tournament.js') }}"></script>
    <script src="{{ asset('js/participant/tournament-pagination.js') }}"></script>
@endsection
