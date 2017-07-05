@extends('organizer.main.master')

@section('title', 'Home')

@section('style')
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <style type="text/css">
        .pending{
            color : #5F89A3;
        }
        .reject{
            color:#ba3f3f;
        }
        a.tournament-link{
            height: 130px;
            padding: 15px;
            position: relative;
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
        .tournament-image{
            display: inline-block;
            vertical-align: top;
            height: 100px;
            width: 100px;
        }
        .tournament-image > img{
            width: 100px;
            height: 100px;
        }
        .tournament-detail{
            display: inline-block;
            margin-left: 15px;
            margin-top: 0;
            width: 470px; 
        }
        .tournament-detail-1{
            margin-top:4px;
        }
        .tournament-detail-1 > h6{
            margin-top: 1px;
            margin-left: 15px;
            margin-bottom: 0px;
            color:#9d9d9d
        }
        .tournament-detail-1 > h6 > div{
            display: inline-block;
            width: 150px;
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
                        <h2 style="margin-top: 0px;color:#fff">Dashboard</h2>
                    </div>
                </div>
                <div class="row" style="margin-top: 25px;margin-bottom: 25px;">
                    @if (count($tournaments) > 0)
                        <div class="col-xs-offset-2 col-xs-8">
                            @foreach ($tournaments as $tournament)
                                <a href="{{ url('/organizer/tournament/'.$tournament->id.'/detail') }}" class="tournament-link well-custom">
                                    <div class="tournament-image">
                                        <img src="{{ asset('storage/tournament/'.$tournament->logo_file_name) }}">
                                    </div>
                                    <div class="tournament-detail">
                                        <h3 style="margin-top: 0px;margin-bottom: 0px;">{{ $tournament->name }}</h3>
                                        <div class="tournament-detail-1">
                                            <h6>
                                            <div>Tournament Type</div>: {{ $tournament->type }}
                                            </h6>
                                            <h6>
                                                <div>Entry Fee</div>: Rp. {{ number_format($tournament->entry_fee, 0, ',', '.') }}
                                            </h6>
                                            <h6>
                                                <div>Registration Closed Date</div>: {{ date('d F Y H:i', strtotime($tournament->registration_closed)) }}
                                            </h6>
                                            <h6>
                                                <div>Event Date</div>: {{ date('d F Y', strtotime($tournament->start_date)) }} - {{ date('d F Y', strtotime($tournament->end_date)) }}
                                            </h6>
                                            <h6>
                                                <div>Created Date</div>: {{ $tournament->created_at->format('d F Y H:i:s') }}
                                            </h6>
                                        </div>
                                        
                                    </div>
                                    <div class="text-center" style="position: absolute;top: 28px;right: 25px;width: 100px;">
                                        @if ($tournament->approval)
                                            @if ($tournament->approval->accepted == 0)
                                                <i class="fa fa-ban reject" aria-hidden="true" style="font-size: 55px;"></i>
                                                <h4 class="reject" style="margin-top: 0px;">REJECTED</h4>
                                            @endif
                                        @else
                                            <i class="fa fa-clock-o pending" aria-hidden="true" style="font-size: 55px;"></i>
                                            <h4 class="pending" style="margin-top: 0px;">PENDING</h4>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="col-xs-12 text-center" style="opacity: 0.2;">
                            <div>
                                <i class="fa fa-times reject" aria-hidden="true" style="font-size: 192px;"></i>
                            </div>
                            <strong class="reject" style="font-size: 64px;">No Data Available</strong>
                        </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <a href="{{ url('/organizer/tournament/create') }}" role="button" class="btn btn-default btn-custom">
                            <div>
                                <i class="glyphicon glyphicon-plus-sign" style="font-size: 24px;color:#fff"></i>
                            </div>
                            <span style="width: 100%;text-align: center;color:#fff;">Create New Tournament</span>
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
