@extends('admin.main.master')

@section('title', 'Verify Tournament')

@section('style')
    <link href="{{ asset('vendor/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <style type="text/css">
        .verify-tournament-header {
            border-bottom: 1px solid #e3e3e3;
            color:#fff;
        }
        .verify-tournament-header h2 {
            margin-top: 0;
        }
        .verify-tournament-body {
            margin-top: 15px;
        }
        #tournament-table > tbody > tr.tournament-table-row {
            cursor: pointer;
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
                <div class="row verify-tournament-header">
                    <div class="col-xs-12">
                        <h2>Verify Tournament</h2>
                    </div>
                </div>
                <div class="row verify-tournament-body">
                    <div class="col-xs-12">
                        <table id="tournament-table" class="table table-schedule table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tournament Name</th>
                                    <th>Organizer Name</th>
                                    <th>Entry Fee</th>
                                    <th>Registration Closed</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Created Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tournaments as $tournament)
                                    <tr class="tournament-table-row" data-id="{{ $tournament->id }}">
                                        <td>{{ $tournament->id }}</td>
                                        <td>{{ $tournament->name }}</td>
                                        <td>{{ $tournament->owner->name }}</td>
                                        <td>Rp. {{ number_format($tournament->entry_fee, 0, ',', '.') }}</td>
                                        <td>{{ date('d F Y H:i', strtotime($tournament->registration_closed)) }}</td>
                                        <td>{{ date('d F Y', strtotime($tournament->start_date)) }}</td>
                                        <td>{{ date('d F Y', strtotime($tournament->end_date)) }}</td>
                                        <td>{{ $tournament->created_at->format('d F Y H:i:s') }}</td>
                                        <td class="text-center">
                                            @if ($tournament->approval)
                                                @if ($tournament->approval->accepted == 1)
                                                    <span class="label label-success">Accepted</span>
                                                @else
                                                    <span class="label label-danger">Rejected</span>
                                                @endif
                                            @else
                                                <span class="label label-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection

@section('script')
    <script src="{{ asset('vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/admin/verify-tournament.js') }}"></script>
@endsection
