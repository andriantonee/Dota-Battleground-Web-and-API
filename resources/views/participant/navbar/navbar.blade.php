<nav class="navbar navbar-inverse" style="min-width: 1024px;border-radius: 0px;">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" style="display: table-cell;vertical-align: middle;height: 50px;padding: 0px;padding-right: 15px;float: none;" href="{{ url('/') }}">
                <img alt="Brand" src="img/dota-2-logo.png">
            </a>
        </div>
        <!-- Note that the .navbar-collapse and .collapse classes have been removed from the #navbar -->
        <div id="navbar">
            <ul class="nav navbar-nav">
                <li><a href="#">Tournaments</a></li>
                <li class="divider-vertical"></li>
                <li><a href="#about">Teams</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                @if ($user)
                    <li class="dropdown dropdown-notifications">
                        <a role="button" class="dropdown-toggle dropdown-toggle-open" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="padding: 15px 0px;">
                            <i data-count="2" class="glyphicon glyphicon-bell notification-icon"></i>
                        </a>
                        <div class="dropdown-container" style="right: 0;left: auto;margin-top: 0px;">
                            <div class="dropdown-toolbar">
                                <div class="dropdown-toolbar-actions">
                                    <a href="#">Mark all as read</a>
                                </div>
                                <h3 class="dropdown-toolbar-title" style="line-height: 1.5;">Notifications (2)</h3>
                            </div>
                            <ul class="dropdown-menu">
                                <li class="notification">
                                    <div class="media">
                                        <div class="media-left">
                                            <div class="media-object">
                                                <img data-src="holder.js/50x50?bg=cccccc" class="img-circle" alt="50x50" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2250%22%20height%3D%2250%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2050%2050%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_15b7711354c%20text%20%7B%20fill%3A%23919191%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_15b7711354c%22%3E%3Crect%20width%3D%2250%22%20height%3D%2250%22%20fill%3D%22%23cccccc%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%227.5%22%20y%3D%2229.5%22%3E50x50%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" data-holder-rendered="true" style="width: 50px; height: 50px;">
                                            </div>
                                        </div>
                                        <div class="media-body">
                                            <strong class="notification-title"><a href="#">Dave Lister</a> commented on <a href="#">DWARF-13 - Maintenance</a></strong>
                                            <p class="notification-desc">I totally don't wanna do it. Rimmer can do it.</p>
                                            <div class="notification-meta">
                                                <small class="timestamp">27. 11. 2015, 15:00</small>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="notification active">
                                    <div class="media">
                                        <div class="media-left">
                                            <div class="media-object">
                                                <img data-src="holder.js/50x50?bg=cccccc" class="img-circle" alt="50x50" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2250%22%20height%3D%2250%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2050%2050%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_15b77113552%20text%20%7B%20fill%3A%23919191%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_15b77113552%22%3E%3Crect%20width%3D%2250%22%20height%3D%2250%22%20fill%3D%22%23cccccc%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%227.5%22%20y%3D%2229.5%22%3E50x50%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" data-holder-rendered="true" style="width: 50px; height: 50px;">
                                            </div>
                                        </div>
                                        <div class="media-body">
                                            <strong class="notification-title"><a href="#">Nikola Tesla</a> resolved <a href="#">T-14 - Awesome stuff</a></strong>
                                            <p class="notification-desc">Resolution: Fixed, Work log: 4h</p>
                                            <div class="notification-meta">
                                                <small class="timestamp">27. 10. 2015, 08:00</small>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="notification">
                                    <div class="media">
                                        <div class="media-left">
                                            <div class="media-object">
                                                <img data-src="holder.js/50x50?bg=cccccc" class="img-circle" alt="50x50" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2250%22%20height%3D%2250%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2050%2050%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_15b77113554%20text%20%7B%20fill%3A%23919191%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_15b77113554%22%3E%3Crect%20width%3D%2250%22%20height%3D%2250%22%20fill%3D%22%23cccccc%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%227.5%22%20y%3D%2229.5%22%3E50x50%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" data-holder-rendered="true" style="width: 50px; height: 50px;">
                                            </div>
                                        </div>
                                        <div class="media-body">
                                            <strong class="notification-title"><a href="#">James Bond</a> resolved <a href="#">B-007 - Desolve Spectre organization</a></strong>
                                            <div class="notification-meta">
                                                <small class="timestamp">1. 9. 2015, 08:00</small>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="notification">
                                    <div class="media">
                                        <div class="media-left">
                                            <div class="media-object">
                                                <img data-src="holder.js/50x50?bg=cccccc" class="img-circle" alt="50x50" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2250%22%20height%3D%2250%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2050%2050%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_15b77113554%20text%20%7B%20fill%3A%23919191%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_15b77113554%22%3E%3Crect%20width%3D%2250%22%20height%3D%2250%22%20fill%3D%22%23cccccc%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%227.5%22%20y%3D%2229.5%22%3E50x50%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" data-holder-rendered="true" style="width: 50px; height: 50px;">
                                            </div>
                                        </div>
                                        <div class="media-body">
                                            <strong class="notification-title"><a href="#">James Bond</a> resolved <a href="#">B-007 - Desolve Spectre organization</a></strong>
                                            <div class="notification-meta">
                                                <small class="timestamp">1. 9. 2015, 08:00</small>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div class="dropdown-footer text-center">
                                <a href="#">View All</a>
                            </div>
                        </div>
                    </li>
                    <li class="dropdown">
                        <a role="button" class="dropdown-toggle dropdown-toggle-open" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp;<span id="navbar-login-name">{{ $user->name }}</span>&nbsp;<span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ url('/profile') }}">Profile</a></li>
                            <li><a href="#">Password</a></li>
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
