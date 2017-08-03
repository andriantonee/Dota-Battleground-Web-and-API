$(document).ready(function(e) {
    $("#registration-closed-datetimepicker").datetimepicker({
        "format" : "DD/MM/YYYY HH:mm",
        "sideBySide" : true,
        "minDate" : minRegistrationClosedDateScheduled
    })
        .on("dp.change", function(e) {
            minStartDateSchedule = e.date.hour(0).minute(0).second(0);
            $("#start-date-datetimepicker").datetimepicker("destroy");
            $("#start-date-datetimepicker").datetimepicker({
                "format" : "DD/MM/YYYY",
                "minDate" : minStartDateSchedule
            });
        });
    $("#start-date-datetimepicker").datetimepicker({
        "format" : "DD/MM/YYYY",
        "minDate" : minStartDateSchedule
    })
        .on("dp.change", function(e) {
            minEndDateSchedule = e.date.hour(0).minute(0).second(0);
            $("#end-date-datetimepicker").datetimepicker("destroy");
            $("#end-date-datetimepicker").datetimepicker({
                "format" : "DD/MM/YYYY",
                "minDate" : minEndDateSchedule
            });
        });
    $("#end-date-datetimepicker").datetimepicker({
        "format" : "DD/MM/YYYY",
        "minDate" : minEndDateSchedule
    });

    $("#form-tournament-create").on("submit", function(e) {
        e.preventDefault();

        var form_data = new FormData(this);
        var btn_create = Ladda.create(document.querySelector("#btn-tournament-create"));

        $.ajax({
            "type" : "POST",
            "url" : api_url + "tournament/create",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)organizer_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : form_data,
            "contentType" : false,
            "processData" : false,
            "beforeSend" : function() {
                $("#tournament-create-alert-container").parent().hide();
                $("#tournament-create-alert-container").empty();
                btn_create.start();
            }
        })
            .done(function(data) {
                var li_message = "";
                $.each(data.message, function(index, value) {
                    li_message += "<li>" + value + "</li>";
                });
                if (data.code == 201) {
                    $("#tournament-create-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
                } else {
                    $("#tournament-create-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#tournament-create-alert-container").parent().show();
                $("#tournament-create-alert-container").append(li_message);

                if (data.code == 201) {
                    location.replace(data.redirect_url);
                } else {
                    $('html, body').animate({
                        scrollTop: $("#tournament-create-alert-container").parent().offset().top
                    }, 1000);
                }
            })
            .fail(function() {
                $("#tournament-create-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                $("#tournament-create-alert-container").parent().show();
                $("#tournament-create-alert-container").append("<li>Something went wrong. Please try again.</li>");

                $('html, body').animate({
                    scrollTop: $("#tournament-create-alert-container").parent().offset().top
                }, 1000);
            })
            .always(function() {
                btn_create.stop();
            });
    });
});
