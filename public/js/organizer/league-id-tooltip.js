$(document).ready(function(e) {
    $(".league-id-tooltip").tooltip({
        "delay" : 250,
        "html" : true,
        "title": "" +
            "<p class=\"tooltip-title-modified\">League ID is needed for using the live match feature.</p>" +
            "<p class=\"tooltip-title-modified\">You can get it from <a href=\"http://www.dota2.com/leagues\" class=\"tooltip-title-modified\">http://www.dota2.com/leagues</a></p>",
        "trigger" : "click"
    });
});
