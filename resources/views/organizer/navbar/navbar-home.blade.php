<nav class="navbar navbar-inverse navbar-custom" style="min-width: 1024px;border-radius: 0px;">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" style="display: table-cell;vertical-align: middle;height: 50px;padding: 0px;padding-right: 15px;float: none;" href="{{ url('/') }}">
                <img alt="Brand" src="{{ asset('img/dota-2-logo.png') }}">
            </a>
        </div>
        <!-- Note that the .navbar-collapse and .collapse classes have been removed from the #navbar -->
        <div id="navbar">
            <form id="form-organizer-login" class="navbar-form navbar-right">
                <div class="form-group">
                    <input type="text" class="form-control" id="sign-in-email" name="email" placeholder="Email" required="required">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="sign-in-password" name="password" placeholder="Password" required="required">
                </div>
                <button type="submit" id="btn-organizer-login" class="btn btn-default btn-custom ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9">
                    <span class="ladda-label">Sign In</span>
                </button>
            </form>
        </div>
    </div>
</nav>
