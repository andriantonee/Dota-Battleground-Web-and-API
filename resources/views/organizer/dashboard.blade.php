@extends('organizer.main.master')

@section('title', 'Home')

@section('style')
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <style type="text/css">
        a.tournament-link {
            color: black;
        }
        a.tournament-link:hover {
            background-color: #ddd;
            text-decoration: none;
        }
        a.tournament-link:first-child {
            margin-bottom: 15px;
        }
        a.tournament-link:last-child {
            margin-bottom: 0 !important;
        }
        a.tournament-link+a.tournament-link {
            margin-bottom: 15px;
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
                        <h2 style="margin-top: 0px;">Dashboard</h2>
                    </div>
                </div>
                <div class="row" style="margin-top: 25px;margin-bottom: 25px;">
                    @if (count($tournaments) > 0)
                        <div class="col-xs-offset-2 col-xs-8">
                            @foreach ($tournaments as $tournament)
                                <a href="{{ url('/organizer/tournament/'.$tournament->id.'/detail') }}" class="tournament-link" style="border: 1px solid black;display: block;height: 130px;padding: 15px;position: relative;">
                                    <div style="display: inline-block;vertical-align: top;height: 100px;width: 100px;">
                                        <img src="{{ asset('storage/tournament/'.$tournament->logo_file_name) }}" style="width: 100px;height: 100px;border: 1px solid black;">
                                    </div>
                                    <div style="display: inline-block;margin-left: 15px;margin-top: 0;width: 470px;">
                                        <h3 style="margin-top: 0px;margin-bottom: 0px;">{{ $tournament->name }}</h3>
                                        <h6 style="margin-top: 5px;margin-left: 15px;margin-bottom: 0px;">
                                            <div style="display: inline-block;width: 150px;">Tournament Type</div>: {{ $tournament->type }}
                                        </h6>
                                        <h6 style="margin-top: 1px;margin-left: 15px;margin-bottom: 0px;">
                                            <div style="display: inline-block;width: 150px;">Entry Fee</div>: Rp. {{ number_format($tournament->entry_fee, 0, ',', '.') }}
                                        </h6>
                                        <h6 style="margin-top: 1px;margin-left: 15px;margin-bottom: 0px;">
                                            <div style="display: inline-block;width: 150px;">Registration Closed Date</div>: {{ date('d F Y H:i', strtotime($tournament->registration_closed)) }}
                                        </h6>
                                        <h6 style="margin-top: 1px;margin-left: 15px;margin-bottom: 0px;">
                                            <div style="display: inline-block;width: 150px;">Event Date</div>: {{ date('d F Y', strtotime($tournament->start_date)) }} - {{ date('d F Y', strtotime($tournament->end_date)) }}
                                        </h6>
                                        <h6 style="margin-top: 1px;margin-left: 15px;margin-bottom: 0px;">
                                            <div style="display: inline-block;width: 150px;">Created Date</div>: {{ $tournament->created_at->format('d F Y H:i:s') }}
                                        </h6>
                                    </div>
                                    <div class="text-center" style="position: absolute;top: 28px;right: 25px;width: 100px;">
                                        @if ($tournament->approval)
                                            @if ($tournament->approval->accepted == 0)
                                                <i class="fa fa-ban" aria-hidden="true" style="font-size: 55px;"></i>
                                                <h4 style="margin-top: 0px;color: red;">REJECTED</h4>
                                            @endif
                                        @else
                                            <i class="fa fa-clock-o" aria-hidden="true" style="font-size: 55px;"></i>
                                            <h4 style="margin-top: 0px;color: blue;">PENDING</h4>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="col-xs-12 text-center" style="opacity: 0.2;">
                            <div>
                                <i class="fa fa-times" aria-hidden="true" style="font-size: 192px;"></i>
                            </div>
                            <strong style="font-size: 64px;">No Data Available</strong>
                        </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <a href="{{ url('/organizer/tournament/create') }}" role="button" class="btn btn-default" style="border-radius: 0px;border-color: #000000;">
                            <div>
                                <i class="glyphicon glyphicon-plus-sign" style="font-size: 24px;"></i>
                            </div>
                            <span style="width: 100%;text-align: center;">Create New Tournament</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection

@section('script')
@endsection
