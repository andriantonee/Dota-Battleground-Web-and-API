@extends('admin.main.master')

@section('title', 'Verify Tournament')

@section('style')
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/modify-pagination.css') }}" rel="stylesheet">
    <style type="text/css">
        .verify-tournament-detail-header {
            border-bottom: 1px solid #e3e3e3;
            color:#fff;
        }
        .verify-tournament-detail-header h2 {
            margin-top: 0;
        }
        .verify-tournament-detail-body {
            margin-top: 25px;
        }
        .verify-tournament-detail-body:last-child {
            margin-bottom: 25px;
        }
        .verify-tournament-detail-footer {
            margin-top: 15px;
            margin-bottom: 25px;
        }
        .verify-tournament-detail-footer button {
            margin: 0 15px;
        }
        #tournament-detail-table > tbody > tr > td:first-child {
            font-weight: bold;
            width: 280px;
        }
        #tournament-detail-table img {
            border: 1px solid black;
            height: 100px;
            width: 100px;
        }
    </style>
@endsection

@section('header')
    @include('admin.navbar.navbar')
@endsection

@section('content')
    <div id="wrapper">
        <div id="page-wrapper">
            <div class="container-fluid well well-transparent">
                @if ($tournament->approval)
                    @if ($tournament->approval->accepted == 1)
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            Accepted by <strong>{{ $tournament->approval->member->name }}</strong> at <strong>{{ $tournament->approval->created_at->format('d F Y H:i:s') }}</strong>.
                        </div>
                    @else
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            Rejected by <strong>{{ $tournament->approval->member->name }}</strong> at <strong>{{ $tournament->approval->created_at->format('d F Y H:i:s') }}</strong>.
                        </div>
                    @endif
                @endif
                <div class="row verify-tournament-detail-header">
                    <div class="col-xs-12">
                        <h2>{{ $tournament->name }}</h2>
                    </div>
                </div>
                <div class="row verify-tournament-detail-body">
                    <div class="col-xs-12">
                        <table id="tournament-detail-table" class="table table-schedule table-striped table-no-border">
                            <tbody>
                                <tr>
                                    <td>ID</td>
                                    <td>{{ $tournament->id }}</td>
                                </tr>
                                <tr>
                                    <td>Tournament Name</td>
                                    <td>{{ $tournament->name }}</td>
                                </tr>
                                <tr>
                                    <td>Organizer Name</td>
                                    <td>{{ $tournament->owner->name }}</td>
                                </tr>
                                <tr>
                                    <td>Description</td>
                                    <td>{!! $tournament->description !!}</td>
                                </tr>
                                <tr>
                                    <td>Logo</td>
                                    <td><img src="{{ asset('/storage/tournament/'.$tournament->logo_file_name) }}"></td>
                                </tr>
                                <tr>
                                    <td>Type</td>
                                    <td>{{ $tournament->type }}</td>
                                </tr>
                                <tr>
                                    <td>League ID</td>
                                    <td>{{ $tournament->leagues_id ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td>City</td>
                                    <td>{{ $tournament->city ? $tournament->city->detail : '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Address</td>
                                    <td>{{ $tournament->address ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Max Participant</td>
                                    <td>{{ $tournament->max_participant }} Teams</td>
                                </tr>
                                <tr>
                                    <td>Rules</td>
                                    <td>{!! $tournament->rules !!}</td>
                                </tr>
                                <tr>
                                    <td>Prize 1st</td>
                                    <td>{{ $tournament->prize_1st ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Prize 2nd</td>
                                    <td>{{ $tournament->prize_2nd ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Prize 3rd</td>
                                    <td>{{ $tournament->prize_3rd ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Prize Other</td>
                                    <td>{!! $tournament->prize_other ?: '-' !!}</td>
                                </tr>
                                <tr>
                                    <td>Entry Fee</td>
                                    <td>Rp. {{ number_format($tournament->entry_fee, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td>Registration Closed Date</td>
                                    <td>{{ date('d F Y H:i', strtotime($tournament->registration_closed)) }}</td>
                                </tr>
                                <tr>
                                    <td>Registration Need Identification Card</td>
                                    <td>{{ $tournament->need_identifications }}</td>
                                </tr>
                                <tr>
                                    <td>Start Date</td>
                                    <td>{{ date('d F Y', strtotime($tournament->start_date)) }}</td>
                                </tr>
                                <tr>
                                    <td>End Date</td>
                                    <td>{{ date('d F Y', strtotime($tournament->end_date)) }}</td>
                                </tr>
                                <tr>
                                    <td>Created Date</td>
                                    <td>{{ $tournament->created_at->format('d F Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td>Last Updated Date</td>
                                    <td>{{ $tournament->updated_at->format('d F Y H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row verify-tournament-detail-footer">
                    <div class="col-xs-12 text-center">
                        @if ($tournament->approval)
                            @if ($tournament->start == 0 && $tournament->registrations_count <= 0)
                                @if ($tournament->approval->accepted == 1)
                                    <button id="btn-undo-tournament" class="btn btn-primary" data-action="Accepted" data-tournament-name="{{ $tournament->name }}">
                                        <i class="fa fa-fw fa-undo"></i>&nbsp;Undo Accepted Tournament
                                    </button>
                                @else
                                    <button id="btn-undo-tournament" class="btn btn-primary" data-action="Rejected" data-tournament-name="{{ $tournament->name }}">
                                        <i class="fa fa-fw fa-undo"></i>&nbsp;Undo Rejected Tournament
                                    </button>
                                @endif
                            @endif
                        @else
                            <button id="btn-approve-tournament" class="btn btn-success" data-tournament-name="{{ $tournament->name }}"><i class="fa fa-fw fa-check"></i>&nbsp;Approve</button>
                            <button id="btn-decline-tournament" class="btn btn-danger" data-tournament-name="{{ $tournament->name }}"><i class="fa fa-fw fa-times"></i>&nbsp;Decline</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection

@section('script')
    <script src="{{ asset('js/admin/verify-tournament-detail.js') }}"></script>
@endsection
