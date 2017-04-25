$("#form-participant-login").on("submit", function(e) {
    e.preventDefault();

    var data = $(this).serialize();
    var btn_login = Ladda.create(document.querySelector("#btn-participant-login"));

    $.ajax({
        "type" : "POST",
        "url" : apiUrl + "login",
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

$("#ckbox-participant-agree").on("change", function(e) {
    if ($(this).prop("checked")) {
        $("#btn-participant-register").prop("disabled", false);
    } else {
        $("#btn-participant-register").prop("disabled", true);
    }
});

$("#form-participant-register").on("submit", function(e) {
    e.preventDefault();

    var data = $(this).serialize();
    var btn_register = Ladda.create(document.querySelector("#btn-participant-register"));

    $.ajax({
        "type" : "POST",
        "url" : apiUrl + "register",
        "data" : data,
        "beforeSend" : function() {
            $("#register-alert-container").parent().hide();
            $("#register-alert-container").empty();
            btn_register.start();
        }
    })
        .done(function(data) {
            var li_message = "";
            $.each(data.message, function(index, value) {
                li_message += "<li>" + value + "</li>";
            });
            if (data.code == 201) {
                $("#register-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
            } else {
                $("#register-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
            }
            $("#register-alert-container").parent().show();
            $("#register-alert-container").append(li_message);
        })
        .fail(function() {
            $("#register-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
            $("#register-alert-container").parent().show();
            $("#register-alert-container").append("<li>Something went wrong. Please try again.</li>");
        })
        .always(function() {
            btn_register.stop();
        });
});