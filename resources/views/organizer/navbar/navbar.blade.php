<nav class="navbar navbar-inverse" style="min-width: 1024px;border-radius: 0px;margin-bottom: 0px;">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" style="display: table-cell;vertical-align: middle;height: 50px;padding: 0px;padding-right: 15px;float: none;" href="{{ url('/') }}">
                <img alt="Brand" src="{{ asset('img/dota-2-logo.png') }}">
            </a>
        </div>
        <!-- Note that the .navbar-collapse and .collapse classes have been removed from the #navbar -->
        <div id="navbar">
            <ul class="nav navbar-nav navbar-right">
                <li style="padding: 15px;line-height: 20px;color: #9d9d9d;">{{ $organizer->name }}</li>
                <li class="divider-vertical"></li>
                <li>
                    <form id="navbar-form-logout" method="POST" action="{{ url('/organizer/logout') }}">
                    </form>
                    <a role="button" onclick="submitFormLogout()">Sign Out</a>
                    <script type="text/javascript">
                        function submitFormLogout() {
                            document.getElementById("navbar-form-logout").submit();
                        };
                    </script>
                </li>
            </ul>
            <ul class="nav navbar-nav side-nav">
                <li style="padding-left: 16px;">
                    <a class="side-nav-brand" style="display: table-cell;vertical-align: middle;height: 51px;padding: 0px;padding-right: 15px;float: none;" href="{{ url('/') }}">
                        <img alt="Brand" src="{{ asset('img/dota-2-logo.png') }}">
                    </a>
                </li>
                <li>
                    <a href="{{ url('/organizer/dashboard') }}"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>
                </li>
                <li>
                    <a href="{{ url('/organizer/tournament') }}"><i class="fa fa-fw fa-trophy"></i> My Tournament</a>
                </li>
                <li>
                    <a href="{{ url('/organizer/password') }}"><i class="fa fa-fw fa-key"></i> Password</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
