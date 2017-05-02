var search_team_typing_timer;
var search_team_done_typing_interval = 500;

$("#txtbox-search-team").on("keyup", function(e) {
    clearTimeout(search_team_typing_timer);
    search_team_typing_timer = setTimeout(search_team_done_typing, search_team_done_typing_interval);
});

$("#txtbox-search-team").on("keydown", function(e) {
    clearTimeout(search_team_typing_timer);
});

function search_team_done_typing() {
    $.ajax({
        "type" : "GET",
        "url" : api_url + "team/search",
        "headers" : {
            "Accept" : "application/json",
            "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
        },
        "data" : {
            "search_keyword" : $("#txtbox-search-team").val()
        }
    })
        .done(function(data) {
            var data_html = "";
            $.each(data.teams, function(index, value) {
                data_html = data_html +
                    "<a class=\"team-list-content\" href=\"" + value.url + "\">" +
                        "<div class=\"row\" style=\"border: 1px solid #000000;margin-bottom: 15px;padding: 10px 0px;\">" +
                            "<div class=\"col-xs-2\">" +
                                "<div class=\"thumbnail\" style=\"height: 80px;width: 80px;margin: 0px auto;\">" +
                                    "<img src=\"" + value.picture_file_name + "\" style=\"height: 70px;width: 70px;\">" +
                                "</div>" +
                            "</div>" +
                            "<div class=\"col-xs-5\">" +
                                "<h3 style=\"margin-top: 13px;\">" + value.name + "</h3>" +
                                "<p><span class=\"team-count\">" + value.details_count + "</span> Member</p>" +
                            "</div>" +
                            "<div class=\"col-xs-5 text-right\" style=\"padding-top: 20px;\">";
                if (value.details.length == 0 && data.isLogin) {
                    if (value.invitation_list.length > 0) {
                        data_html = data_html + 
                                "<button class=\"btn btn-default accept-invite-request\" style=\"font-size: 20px;\" data-team-id=\"" + value.id + "\" data-team-name=\"" + value.name + "\" data-refresh=\"false\">" +
                                    "<i class=\"glyphicon glyphicon-ok\"></i>&nbsp;&nbsp;Accept" +
                                "</button>&nbsp;" +
                                "<button class=\"btn btn-default reject-invite-request\" style=\"font-size: 20px;\" data-team-id=\"" + value.id + "\" data-team-name=\"" + value.name + "\" data-refresh=\"false\" data-with-password=\"" + value.join_password + "\">" +
                                    "<i class=\"glyphicon glyphicon-remove\"></i>&nbsp;&nbsp;Reject" +
                                "</button>";
                    } else {
                        if (value.join_password) {
                            data_html = data_html +
                                "<button class=\"btn btn-default join-with-password\" style=\"font-size: 20px;\" data-team-id=\"" + value.id + "\" data-team-name=\"" + value.name + "\" data-refresh=\"false\">" +
                                    "<i class=\"glyphicon glyphicon-log-in\"></i>&nbsp;&nbsp;Join Team" +
                                "</button>";
                        } else {
                            data_html = data_html +
                                "<button class=\"btn btn-default join-without-password\" style=\"font-size: 20px;\" data-team-id=\"" + value.id + "\" data-team-name=\"" + value.name + "\" data-refresh=\"false\">" +
                                    "<i class=\"glyphicon glyphicon-log-in\"></i>&nbsp;&nbsp;Join Team" +
                                "</button>";
                        }
                    }
                }
                data_html = data_html +
                            "</div>" +
                        "</div>" +
                    "</a>";
            });

            $("#team-list-container").html(data_html);
        })
        .fail(function() {
            $("#team-list-container").empty();
        });
};
