$("#form-admin-login").on("submit", function(e) {
    e.preventDefault();

    var data = $(this).serialize();
    var btn_login = Ladda.create(document.querySelector("#btn-admin-login"));

    $.ajax({
        "type" : "POST",
        "url" : api_url + "login",
        "data" : data,
        "beforeSend" : function() {
            $("#login-alert-container").parent().hide();
            $("#login-alert-container").empty();
            btn_login.start();
        }
    })
        .done(function(data) {
            var li_message = "";
            $.each(data.message, function(index, value) {
                li_message += "<li>" + value + "</li>";
            });
            if (data.code == 200) {
                $("#login-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
            } else {
                btn_login.stop();
                $("#login-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
            }
            $("#login-alert-container").parent().show();
            $("#login-alert-container").append(li_message);

            if (data.code == 200) {
                location.reload();
            }
        })
        .fail(function() {
            btn_login.stop();
            $("#login-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
            $("#login-alert-container").parent().show();
            $("#login-alert-container").append("<li>Something went wrong. Please try again.</li>");
        });
});

