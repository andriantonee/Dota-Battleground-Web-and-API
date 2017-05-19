$(document).ready(function() {
    $("#form-tournament-confirm-payment").on("submit", function(e) {
        e.preventDefault();

        var form_data = new FormData(this);
        var btn_confirm = Ladda.create(document.querySelector("#btn-tournament-confirm-payment"));

        $.ajax({
            "type" : "POST",
            "url" : api_url + "tournament/confirm-payment/" + location.pathname.split("/")[3],
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : form_data,
            "contentType" : false,
            "processData" : false,
            "beforeSend" : function() {
                $("#tournament-confirm-payment-alert-container").parent().hide();
                $("#tournament-confirm-payment-alert-container").empty();
                btn_confirm.start();
            }
        })
            .done(function(data) {
                var li_message = "";
                $.each(data.message, function(index, value) {
                    li_message += "<li>" + value + "</li>";
                });
                if (data.code == 200) {
                    $("#tournament-confirm-payment-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
                } else {
                    $("#tournament-confirm-payment-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#tournament-confirm-payment-alert-container").parent().show();
                $("#tournament-confirm-payment-alert-container").append(li_message);

                if (data.code == 200) {
                    $("#proof-payment").attr("src", data.image_url);
                    $("#proof-payment").show();
                }
            })
            .fail(function() {
                $("#tournament-confirm-payment-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                $("#tournament-confirm-payment-alert-container").parent().show();
                $("#tournament-confirm-payment-alert-container").append("<li>Something went wrong. Please try again.</li>");
            })
            .always(function() {
                btn_confirm.stop();
            });
    });
});
