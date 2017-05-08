<nav class="navbar navbar-inverse" style="min-width: 1024px;border-radius: 0px;">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" style="display: table-cell;vertical-align: middle;height: 50px;padding: 0px;padding-right: 15px;float: none;" href="{{ url('/') }}">
                <img alt="Brand" src="{{ asset('img/dota-2-logo.png') }}">
            </a>
        </div>
        <!-- Note that the .navbar-collapse and .collapse classes have been removed from the #navbar -->
        <div id="navbar">
            <ul class="nav navbar-nav">
                <li><a href="{{ url('/tournament') }}">Tournaments</a></li>
                <li class="divider-vertical"></li>
                <li><a href="{{ url('/team') }}">Teams</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                @if ($participant)
                    <li class="dropdown dropdown-notifications">
                        <a role="button" class="dropdown-toggle dropdown-toggle-open" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="padding: 15px 0px;">
                            @if ($unread_notifications)
                                <i data-count="{{ $unread_notifications }}" class="glyphicon glyphicon-bell notification-icon"></i>
                            @else
                                <i class="glyphicon glyphicon-bell"></i>
                            @endif
                        </a>
                        <div class="dropdown-container" style="right: 0;left: auto;margin-top: 0px;">
                            <div class="dropdown-toolbar">
                                <h3 class="dropdown-toolbar-title" style="line-height: 1.5;">
                                    @if ($unread_notifications)
                                        Notifications ({{ $unread_notifications }})
                                    @else
                                        Notifications
                                    @endif
                                </h3>
                            </div>
                            <ul class="dropdown-menu">
                                @foreach ($notifications as $notification)
                                    @if ($notification->member_join_team)
                                        <a href="{{ url('/team/'.$notification->member_join_team->team->id.'?notification_id='.$notification->id) }}" style="text-decoration: none;">
                                            @if ($notification->read_status)
                                                <li class="notification">
                                            @else
                                                <li class="notification active">
                                            @endif
                                                <div class="media">
                                                    <div class="media-body" style="padding-top: 0px;">
                                                        <strong class="notification-title">{{ $notification->member_join_team->member->name }} is part of the team {{ $notification->member_join_team->team->name }} now.</strong>
                                                        <div class="notification-meta">
                                                            <small class="timestamp">
                                                                <i class="fa fa-users" aria-hidden="true"></i>&nbsp;&nbsp;{{ date('d F Y, H:i', strtotime($notification->created_at)) }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </a>
                                    @elseif ($notification->team_invitation)
                                        <a href="{{ url('/team/'.$notification->team_invitation->team->id.'?notification_id='.$notification->id) }}" style="text-decoration: none;">
                                            @if ($notification->read_status)
                                                <li class="notification">
                                            @else
                                                <li class="notification active">
                                            @endif
                                                <div class="media">
                                                    <div class="media-body" style="padding-top: 0px;">
                                                        <strong class="notification-title">Team {{ $notification->team_invitation->team->name }} sent you an invitation.</strong>
                                                        <div class="notification-meta">
                                                            <small class="timestamp">
                                                                <i class="fa fa-users" aria-hidden="true"></i>&nbsp;&nbsp;{{ date('d F Y, H:i', strtotime($notification->created_at)) }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </a>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </li>
                    <li class="dropdown">
                        <a role="button" class="dropdown-toggle dropdown-toggle-open" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp;<span id="navbar-login-name">{{ $participant->name }}</span>&nbsp;<span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ url('/profile') }}">Profile</a></li>
                            <li><a role="button" data-toggle="modal" data-target="#password-modal">Password</a></li>
                            <li>
                                <form id="navbar-form-logout" method="POST" action="{{ url('/logout') }}">
                                </form>
                                <a role="button" onclick="submitFormLogout()">Sign Out</a>
                                <script type="text/javascript">
                                    function submitFormLogout() {
                                        document.getElementById("navbar-form-logout").submit();
                                    };
                                </script>
                            </li>
                        </ul>
                    </li>
                @else
                    <li><a role="button" data-toggle="modal" data-target="#sign-in-modal">Sign In</a></li>
                    <li class="divider-vertical"></li>
                    <li><a role="button" data-toggle="modal" data-target="#sign-up-modal">Sign Up</a></li>
                @endif
            </ul>
        </div>
    </div>
</nav>

@if ($participant)
    <!-- Password Modal -->
    <div class="modal modal-remove-padding-right" id="password-modal" tabindex="-1" role="dialog" aria-labelledby="password-modal-label">
        <div class="modal-dialog modal-dialog-fixed-width-320" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-border-bottom-custom">
                    <h1 class="modal-title modal-title-align-center" id="password-modal-label">Password</h1>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success" role="alert" style="margin-left: 5px;margin-right: 5px;display: none;">
                        <ul id="password-alert-container">
                            <!-- All message that want to deliver to the user -->
                        </ul>
                    </div>
                    <form id="form-participant-password">
                        <div class="form-inline form-inline-authentication">
                            <div class="form-group">
                                <label for="old-password">
                                    <i class="fa fa-key" aria-hidden="true"></i>
                                </label>
                                <input type="password" class="form-control" id="old-password" name="old_password" placeholder="Old Password" required="required">
                            </div>
                            <div class="form-group">
                                <label for="new-password">
                                    <i class="fa fa-key" aria-hidden="true"></i>
                                </label>
                                <input type="password" class="form-control" id="new-password" name="new_password" placeholder="New Password" required="required">
                            </div>
                            <div class="form-group">
                                <label for="new-password-confirmation">
                                    <i class="fa fa-key" aria-hidden="true"></i>
                                </label>
                                <input type="password" class="form-control" id="new-password-confirmation" name="new_password_confirmation" placeholder="New Password Confirmation" required="required">
                            </div>
                        </div>
                        <div class="button-container-authentication">
                            <button type="submit" class="btn btn-default ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9" id="btn-participant-password">
                                <span class="ladda-label">Change</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Sign In Modal -->
    <div class="modal modal-remove-padding-right" id="sign-in-modal" tabindex="-1" role="dialog" aria-labelledby="sign-in-modal-label">
        <div class="modal-dialog modal-dialog-fixed-width-320" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-border-bottom-custom">
                    <h1 class="modal-title modal-title-align-center" id="sign-in-modal-label">Sign In</h1>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success" role="alert" style="margin-left: 5px;margin-right: 5px;display: none;">
                        <ul id="login-alert-container">
                            <!-- All message that want to deliver to the user -->
                        </ul>
                    </div>
                    <form id="form-participant-login">
                        <div class="form-inline form-inline-authentication">
                            <div class="form-group">
                                <label for="sign-in-email">
                                    <i class="fa fa-envelope" aria-hidden="true"></i>
                                </label>
                                <input type="email" class="form-control" id="sign-in-email" name="email" placeholder="Email" required="required">
                            </div>
                            <div class="form-group">
                                <label for="sign-in-password">
                                    <i class="fa fa-key" aria-hidden="true"></i>
                                </label>
                                <input type="password" class="form-control" id="sign-in-password" name="password" placeholder="Password" required="required">
                            </div>
                        </div>
                        <div class="button-container-authentication">
                            <button type="submit" class="btn btn-default ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9" id="btn-participant-login">
                                <span class="ladda-label">Login</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sign Up Modal -->
    <div class="modal modal-remove-padding-right" id="sign-up-modal" tabindex="-1" role="dialog" aria-labelledby="mySignInModalLabel">
        <div class="modal-dialog modal-dialog-fixed-width-320" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-border-bottom-custom">
                    <h1 class="modal-title modal-title-align-center" id="mySignInModalLabel">Sign Up</h1>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success" role="alert" style="margin-left: 5px;margin-right: 5px;display: none;">
                        <ul id="register-alert-container">
                            <!-- All message that want to deliver to the user -->
                        </ul>
                    </div>
                    <form id="form-participant-register">
                        <div class="form-inline form-inline-authentication">
                            <div class="form-group">
                                <label for="sign-up-name">
                                    <i class="fa fa-user" aria-hidden="true" style="padding: 0px 2px;"></i>
                                </label>
                                <input type="text" class="form-control" id="sign-up-name" name="name" placeholder="Name" required="required">
                            </div>
                            <div class="form-group">
                                <label for="sign-up-email">
                                    <i class="fa fa-envelope" aria-hidden="true"></i>
                                </label>
                                <input type="email" class="form-control" id="sign-up-email" name="email" placeholder="Email" required="required">
                            </div>
                            <div class="form-group">
                                <label for="sign-up-password">
                                    <i class="fa fa-key" aria-hidden="true"></i>
                                </label>
                                <input type="password" class="form-control" id="sign-up-password" name="password" placeholder="Password" required="required">
                            </div>
                            <div class="form-group">
                                <label for="sign-up-confirm-password">
                                    <i class="fa fa-key" aria-hidden="true"></i>
                                </label>
                                <input type="password" class="form-control" id="sign-up-confirm-password" name="password_confirmation" placeholder="Confirm Password" required="required">
                            </div>
                        </div>
                        <div class="checkbox" style="padding: 0px 15px;">
                            <label>
                                <input type="checkbox" name="agree" id="ckbox-participant-agree"> I agree to the terms and conditions
                            </label>
                        </div>
                        <div class="button-container-authentication">
                            <button type="submit" class="btn btn-default ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9" id="btn-participant-register" disabled="disabled">
                                <span class="ladda-label">Register</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif