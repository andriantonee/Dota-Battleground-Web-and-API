$(document).ready(function(e) {
	$("#form-tournament-settings").on("submit", function(e) {
        e.preventDefault();

        var data = $(this).serialize();
        var btn_update = Ladda.create(document.querySelector("#btn-tournament-settings"));

        $.ajax({
            "type" : "PUT",
            "url" : api_url + "tournament/" + location.pathname.split("/")[3],
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)organizer_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : data,
            "beforeSend" : function() {
                $("#tournament-settings-alert-container").parent().hide();
                $("#tournament-settings-alert-container").empty();
                btn_update.start();
            }
        })
            .done(function(data) {
                var li_message = "";
                $.each(data.message, function(index, value) {
                    li_message += "<li>" + value + "</li>";
                });
                if (data.code == 200) {
                    $("#tournament-settings-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
                } else {
                    $("#tournament-settings-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#tournament-settings-alert-container").parent().show();
                $("#tournament-settings-alert-container").append(li_message);

                $('html, body').animate({
                    scrollTop: $("#tournament-settings-alert-container").parent().offset().top
                }, 1000);
            })
            .fail(function() {
                $("#tournament-settings-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                $("#tournament-settings-alert-container").parent().show();
                $("#tournament-settings-alert-container").append("<li>Something went wrong. Please try again.</li>");

                $('html, body').animate({
                    scrollTop: $("#tournament-settings-alert-container").parent().offset().top
                }, 1000);
            })
            .always(function() {
                btn_update.stop();
            });
    });
});
