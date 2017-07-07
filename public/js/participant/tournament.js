$(document).ready(function(e) {
    $("#start-date-datetimepicker").datetimepicker({
        "format" : "DD/MM/YYYY",
        "sideBySide" : true
    });

    $("#txtbox-search-tournament").on("keyup", function(e) {
        if (e.which == 13) {
            search();
        }
    });
    $("input[name=\"tournament_filter_status\"], #tournament-ordering").on("change", function(e) {
        search();
    });
    $("#btn-filter-tournament").on("click", function(e) {
        search();
    });

    function search() {
        var name = $("#txtbox-search-tournament").val();
        var status = $("input[name=\"tournament_filter_status\"]:checked").val();
        var order = $("#tournament-ordering").val();
        var price = $("input[name=\"tournament_filter_prices\"]:checked").val();
        var start_date = $("#start-date").val();
        var city = $("#city").val();

        var url_search = window.location.origin + "/tournament?name=" + name + "&status=" + status + "&order=" + order;
        if (typeof price !== "undefined") {
            url_search = url_search + "&price=" + price;
        }
        if (start_date !== "") {
            url_search = url_search + "&start_date=" + start_date;
        }
        if (city !== null) {
            url_search = url_search + "&city=" + city;
        }
        window.location.href = url_search;
    };
});
