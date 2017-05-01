$("#form-participant-password").on("submit", function(e) {
    e.preventDefault();

    var data = $(this).serialize();
    var btn_password = Ladda.create(document.querySelector("#btn-participant-password"));

    $.ajax({
        "type" : "PUT",
        "url" : api_url + "password",
        "headers" : {
            "Accept" : "application/json",
            "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
        },
        "data" : data,
        "beforeSend" : function() {
            $("#password-alert-container").parent().hide();
            $("#password-alert-container").empty();
            btn_password.start();
        }
    })
        .done(function(data) {
            var li_message = "";
            $.each(data.message, function(index, value) {
                li_message += "<li>" + value + "</li>";
            });
            if (data.code == 200) {
                $("#password-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
            } else {
                $("#password-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
            }
            $("#password-alert-container").parent().show();
            $("#password-alert-container").append(li_message);
        })
        .fail(function() {
            $("#password-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
            $("#password-alert-container").parent().show();
            $("#password-alert-container").append("<li>Something went wrong. Please try again.</li>");
        })
        .always(function() {
        	btn_password.stop();
        });
});