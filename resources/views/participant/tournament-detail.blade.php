@extends('participant.main.master')

@section('title', 'Tournament Detail')

@section('style')
    <link href="{{ asset('css/participant/footer.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/tab-pages.css') }}" rel="stylesheet">
    <style type="text/css">
        #tournament-container {
            min-height: 536px;
        }

        #tournament-header {
            height: 135px;
            padding: 0px 15px 5px 45px;
        }
        #tournament-header-logo {
            display: inline-block;
            vertical-align: top;
        }
        #tournament-header-logo > img {
            border: 1px solid black;
            height: 130px;
            width: 130px;
        }
        #tournament-header-detail {
            border-bottom: 1px solid #ddd;
            display: inline-block;
            margin-left: 5px;
            margin-top: 20px;
            padding-bottom: 10px;
            width: 500px;
            vertical-align: top;
        }
        #tournament-header-detail-name {
            margin-bottom: 5px;
            margin-top: 0;
        }
        p.tournament-header-detail-other {
            font-size: 13px;
            margin-bottom: 0px;
            margin-top: 0px;
            padding-left: 20px;
        }
        #tournament-header-registration {
            border: 1px solid black;
            display: inline-block;
            height: 100%;
            margin-left: 15px;
            width: 276px;
            vertical-align: top;
        }
        #tournament-header-registration-container {
            display: table;
            height: 100%;
            width: 100%;
        }
        #tournament-header-registration-content {
            display: table-cell;
            text-align: center;
            vertical-align: bottom;
        }
        #tournament-header-registration-status {
            margin-bottom: 5px;
            margin-top: 0;
        }
        #tournament-header-registration-closed {
            margin-bottom: 5px;
            margin-top: 0;
            font-size: 10px;
        }
        #tournament-header-registration-action {
            border-radius: 0;
            margin-bottom: 15px;
            width: 180px;
        }

        #tournament-body {
            padding: 0px 15px;
        }
        #tournament-body > div.well {
            background-color: #fff;
            border: 1px solid #000;
            border-radius: 0;
            min-height: 386px;
        }
        .tab-pane-title {
            border-bottom: 1px solid #ddd;
            margin-bottom: 0;
            margin-top: 0;
            padding-bottom: 10px;
        }
        table.table-tournament-detail {
            margin-bottom: 0;
            margin-top: 20px;
        }
        table.table-tournament-detail > tbody > tr > td {
            border: none;
        }
        .tournament-rule-content {
            margin-bottom: 0;
            margin-top: 20px;
            max-height: 384px;
            min-height: 384px;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .prizes-container {
            margin-top: 20px;
        }
        .prizes-content {
            border: 1px solid #000;
            display: flex;
            display: -webkit-flex;
            flex-direction: row;
            -webkit-flex-direction: row;
            -ms-flex-direction: row;
            height: 80px;
            width: 800px;
        }
        .prizes-content:first-child {
            margin-bottom: 5px;
        }
        .prizes-content+.prizes-content {
            margin-bottom: 5px;
        }
        .prizes-content:last-child {
            margin-bottom: 0;
        }
        .prizes-rank {
            border-right: 1px solid #000;
            height: 100%;
            width: 125px;
        }
        .prizes-rank > h1 {
            font-size: 78px;
            transform: translate(7px, -32px) rotateZ(-15deg);
            -webkit-transform: translate(7px, -32px) rotateZ(-15deg);
            -moz-transform: translate(7px, -32px) rotateZ(-15deg);
            -ms-transform: translate(7px, -32px) rotateZ(-15deg);
            -o-transform: translate(7px, -32px) rotateZ(-15deg);
        }
        .prizes-rank > h1 > span {
            font-size: 48px;
            margin-left: -3px;
        }
        .prizes-rank-gold {
            background-color: #ffd700;
        }
        .prizes-rank-silver {
            background-color: #c0c0c0;
        }
        .prizes-rank-copper {
            background-color: #b87333;
        }
        .prizes-detail-container {
            background-color: #eee;
            height: 100%;
            width: 675px;
        }
        .prizes-detail-content {
            display: table;
            height: 100%;
            padding-left: 15px;
            width: 100%;
        }
        .prizes-detail-content > h3 {
            display: table-cell;
            vertical-align: middle;
        }
    </style>
@endsection

@section('content')
    <div id="tournament-container" class="container">
        <div id="tournament-header">
            <div id="tournament-header-logo">
                <img src="{{ asset('img/the-boston-major.png') }}">
            </div>
            <div id="tournament-header-detail">
                <h1 id="tournament-header-detail-name">Tournaments Name</h1>
                <p class="tournament-header-detail-other">Organized By : Lenovo Gaming League</p>
                <p class="tournament-header-detail-other">Event Start : 14 March 2017</p>
            </div>
            <div id="tournament-header-registration">
                <div id="tournament-header-registration-container">
                    <div id="tournament-header-registration-content">
                        <h4 id="tournament-header-registration-status">REGISTRATION IS OPEN!</h4>
                        <p id="tournament-header-registration-closed">Registration Ends : 13 March 2017, 5.30 PM</p>
                        <button id="tournament-header-registration-action" class="btn btn-default">REGISTER</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="tournament-body">
            <div class="well well-lg">
                <h4 style="margin-top: 0;">Overview</h4>
                <p>Welcome to the Lenovo Dota 2 League! Up to 256 teams will compete for Matilda IV tanks, game gold and cash prizes!</p>
                <p>This is a 2 part tournament, where you will be placed into a Round Robin qualifier with other teams and competing for qualifying spots to a Single Elimination playoff.</p>
                <p>For more information, rules and schedule can be found below.</p>
                <p>NOTE: The organizers are monitoring sign-ups and format/schedule will be set after registration closes.</p>
                <div class="panel with-nav-tabs panel-default" style="border: none;">
                    <div class="panel-heading" style="background-color: transparent;border-color: #000000;">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#details-tab" data-toggle="tab">Details</a></li>
                            <li><a href="#rules-tab" data-toggle="tab">Rules</a></li>
                            <li><a href="#prizes-tab" data-toggle="tab">Prizes</a></li>
                            <li><a href="#schedule-tab" data-toggle="tab">Schedule</a></li>
                            <li><a href="#bracket-tab" data-toggle="tab">Bracket</a></li>
                            <li><a href="#live-match-tab" data-toggle="tab">Live Match</a></li>
                        </ul>
                    </div>
                    <div class="panel-body" style="border: 1px solid #000000;border-top: none;">
                        <div class="tab-content">
                            <div class="tab-pane fade in active" id="details-tab">
                                <h4 class="tab-pane-title">Details</h4>
                                <table class="table table-striped table-tournament-detail">
                                    <tbody>
                                        <tr>
                                            <td>Location</td>
                                            <td>Medan</td>
                                        </tr>
                                        <tr>
                                            <td>Registration Close</td>
                                            <td>13 March 2017</td>
                                        </tr>
                                        <tr>
                                            <td>Event Start</td>
                                            <td>14 March 2017</td>
                                        </tr>
                                        <tr>
                                            <td>Event End</td>
                                            <td>31 March 2017</td>
                                        </tr>
                                        <tr>
                                            <td>Entry Fee</td>
                                            <td>Rp. 150.000</td>
                                        </tr>
                                        <tr>
                                            <td>Event Type</td>
                                            <td>Single Elimination</td>
                                        </tr>
                                        <tr>
                                            <td>Min Participants</td>
                                            <td>2</td>
                                        </tr>
                                        <tr>
                                            <td>Max Participants</td>
                                            <td>16</td>
                                        </tr>
                                        <tr>
                                            <td>Current Participants</td>
                                            <td>7</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="rules-tab">
                                <h4 class="tab-pane-title">Rules</h4>
                                <div class="tournament-rule-content">
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                    </p>
                                    <p>
                                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?
                                    </p>
                                    <p>
                                        At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.
                                    </p>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                    </p>
                                    <p>
                                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?
                                    </p>
                                    <p>
                                        At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.
                                    </p>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="prizes-tab">
                                <h4 class="tab-pane-title">Prizes</h4>
                                <div class="prizes-container">
                                    <div class="prizes-content">
                                        <div class="prizes-rank prizes-rank-gold">
                                            <h1>1<span>ST</span></h1>
                                        </div>
                                        <div class="prizes-detail-container">
                                            <div class="prizes-detail-content">
                                                <h3>Rp. 2.000.000 + Steam Wallet sebesar Rp. 500.000</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="prizes-content">
                                        <div class="prizes-rank prizes-rank-silver">
                                            <h1>2<span>ND</span></h1>
                                        </div>
                                        <div class="prizes-detail-container">
                                            <div class="prizes-detail-content">
                                                <h3>Rp. 1.000.000 + Steam Wallet sebesar Rp. 500.000</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="prizes-content">
                                        <div class="prizes-rank prizes-rank-copper">
                                            <h1>3<span>RD</span></h1>
                                        </div>
                                        <div class="prizes-detail-container">
                                            <div class="prizes-detail-content">
                                                <h3>Steam Wallet sebesar Rp. 500.000</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="schedule-tab">
                                <h4 class="tab-pane-title">Schedule</h4>
                            </div>
                            <div class="tab-pane fade" id="bracket-tab">
                                <h4 class="tab-pane-title">Bracket</h4>
                            </div>
                            <div class="tab-pane fade" id="live-match-tab">
                                <h4 class="tab-pane-title">Live Matches</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('participant.footer.footer')
@endsection

@section('script')
@endsection
