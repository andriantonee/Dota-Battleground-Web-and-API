$(document).ready(function(e) {
    $("input[name=\"tournament_filter\"]").on("change", function(e) {
        var status = $(this).val();

        var url_search = window.location.origin + "/organizer/tournament?status=" + status;
        window.location.href = url_search;
    });
});
