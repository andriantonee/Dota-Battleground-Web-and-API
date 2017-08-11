@extends('organizer.main.master')

@section('title', 'Document')

@section('style')
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <style type="text/css">
        .pending {
            color : #5F89A3;
        }

        .reject {
            color:#ba3f3f;
        }

        a {
            color: #a43827;
        }
        a:hover {
            color: #913122;
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
                        <h2 style="margin-top: 0px;color:#fff">Document</h2>
                    </div>
                </div>
                <div class="row" style="margin-top: 25px;margin-bottom: 25px;">
                    <div class="col-xs-12 text-center" style="opacity: 0.5;">
                        @if ($organizer->verified == 0)
                            <div>
                                <i class="fa fa-clock-o pending" aria-hidden="true" style="font-size: 128px;"></i>
                            </div>
                            <strong class="pending" style="font-size: 24px;">Business Document Pending</strong>
                        @elseif ($organizer->verified == 2)
                            <div>
                                <i class="fa fa-times reject" aria-hidden="true" style="font-size: 128px;"></i>
                            </div>
                            <strong class="reject" style="font-size: 24px;">Business Document Rejected</strong>
                        @endif
                    </div>
                    @if ($organizer->document_file_name)
                        <div class="col-xs-12 text-center">
                            <a href="{{ asset('/storage/member/document/'.$organizer->document_file_name) }}">Click here to download your latest document</a>
                        </div>
                    @endif
                    <div class="col-xs-offset-3 col-xs-6">
                        <fieldset class="fieldset-custom">
                            <legend>Upload Business Document</legend>
                            <form id="upload-business-document" class="form-horizontal" style="margin-top: 10px;">
                                <div class="alert alert-success" role="alert" style="margin-left: 5px;margin-right: 5px;display: none;">
                                    <ul id="upload-business-document-alert-container">
                                        <!-- All message that want to deliver to the user -->
                                    </ul>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div class="col-xs-9" style="padding-top: 5px;">
                                        <input type="file" id="sign-up-document" name="document" accept="application/pdf" required="required">
                                    </div>
                                    <div class="col-xs-3 text-right">
                                        <button type="submit" id="btn-upload-business-document" class="btn btn-default btn-custom ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9">
                                            <span class="ladda-label">Upload</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection

@section('script')
    <script src="{{ asset('js/organizer/document.js') }}"></script>
@endsection
