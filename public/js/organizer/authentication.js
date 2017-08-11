$("#form-organizer-login").on("submit", function(e) {
    e.preventDefault();

    var data = $(this).serialize();
    var btn_login = Ladda.create(document.querySelector("#btn-organizer-login"));

    $.ajax({
        "type" : "POST",
        "url" : api_url + "login",
        "data" : data,
        "beforeSend" : function() {
            $("#sign-in-email").parent().removeClass("has-error");
            $("#sign-in-password").parent().removeClass("has-error");
            btn_login.start();
        }
    })
        .done(function(data) {
            if (data.code == 200) {
                location.reload();
            } else {
                btn_login.stop();
                $("#sign-in-email").parent().addClass("has-error");
                $("#sign-in-password").parent().addClass("has-error");
            }
        })
        .fail(function() {
            btn_login.stop();
            $("#sign-in-email").parent().addClass("has-error");
            $("#sign-in-password").parent().addClass("has-error");
        });
});

$("#ckbox-organizer-agree").on("change", function(e) {
    if ($(this).prop("checked")) {
        $("#btn-organizer-register").prop("disabled", false);
    } else {
        $("#btn-organizer-register").prop("disabled", true);
    }
});

$("#form-organizer-register").on("submit", function(e) {
    e.preventDefault();

    var form_data = new FormData(this);
    var btn_register = Ladda.create(document.querySelector("#btn-organizer-register"));

    $.ajax({
        "type" : "POST",
        "url" : api_url + "register",
        "data" : form_data,
        "contentType" : false,
        "processData" : false,
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
