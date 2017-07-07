$(document).ready(function(e) {
    $("#txtbox-search-tournament").on("keyup", function(e) {
        if (e.which == 13) {
            var name = $("#txtbox-search-tournament").val();

            var url_search = window.location.origin + "/tournament?name=" + name;
            window.location.href = url_search;
        }
    });
});
