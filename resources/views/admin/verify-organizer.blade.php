@extends('admin.main.master')

@section('title', 'Verify Organizer')

@section('style')
    <link href="{{ asset('vendor/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/modify-modal.css') }}" rel="stylesheet">
    <style type="text/css">
        .verify-organizer-header {
            border-bottom: 1px solid #e3e3e3;
            color:#fff;
        }
        .verify-organizer-header h2 {
            margin-top: 0;
        }
        .verify-organizer-body {
            margin-top: 15px;
        }
        .btn-width-25 {
            width: 25px;
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
                <div class="row verify-organizer-header">
                    <div class="col-xs-12">
                        <h2>Verify Organizer</h2>
                    </div>
                </div>
                <div class="row verify-organizer-body">
                    <div class="col-xs-12">
                        <table id="organizer-table" class="table table-schedule table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Member ID</th>
                                    <th>Name</th>
                                    <th>E-mail</th>
                                    <th>Status</th>
                                    <th>Register At</th>
                                    <th style="width: 70px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($organizers as $organizer)
                                    <tr class="organizer-table-row" id="organizer-table-row-{{ $organizer->id }}">
                                        <td>{{ $organizer->id }}</td>
                                        <td>{{ $organizer->name }}</td>
                                        <td>{{ $organizer->email }}</td>
                                        <td class="text-center">
                                            @if ($organizer->verified == 0)
                                                <span class="label label-warning">Pending</span>
                                            @elseif ($organizer->verified == 1)
                                                <span class="label label-success">Accepted</span>
                                            @elseif ($organizer->verified == 2)
                                                <span class="label label-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>{{ $organizer->created_at->format('d F Y H:i:s') }}</td>
                                        <td class="text-center">
                                            @if ($organizer->verified != 1)
                                                <button class="btn btn-default btn-xs btn-success btn-width-25 btn-approve-organizer" data-id="{{ $organizer->id }}"><i class="fa fa-check"></i></button>
                                            @endif
                                            @if ($organizer->verified != 2)
                                                <button class="btn btn-default btn-xs btn-danger btn-width-25 btn-decline-organizer" data-id="{{ $organizer->id }}"><i class="fa fa-times"></i></button>
                                            @endif
                                            @if ($organizer->document_file_name)
                                                <a role="button" href="{{ asset('/storage/member/document/'.$organizer->document_file_name) }}" class="btn btn-default btn-xs btn-primary btn-width-25"><i class="fa fa-download"></i></a>
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
    <script src="{{ asset('js/admin/verify-organizer.js') }}"></script>
@endsection
