/*jslint browser: true*/
/*global $, mw, window*/
var archifooterComments = (function () {
    "use strict";
    var api;

    function getAvatar(i, item) {
        var $item = $(item);
        var username = $item.find(".c-user a").text().trim();
        var longUsername = "Utilisateur:" + username;
        api.get({action: "ask", query: "[[" + longUsername + "]]|?Avatar"}).done(
            function (data) {
                if (data.query.results[longUsername] && data.query.results[longUsername].printouts.Avatar[0]) {
                    api.get(
                        {
                            action: "query",
                            titles: "File:" + data.query.results[longUsername].printouts.Avatar[0].fulltext,
                            prop: "imageinfo",
                            iiprop: "url",
                            iiurlwidth: 80
                        }
                    ).done(
                        /**
                         * @todo Don't hardcode CSS properties
                         */
                        function (data) {
                            $item.find(".c-avatar").css(
                                "background-image",
                                "url(" + data.query.pages[Object.keys(data.query.pages)[0]].imageinfo[0].thumburl + ")"
                            )
                                .css("height", "80px")
                                .css("width", "80px")
                                .css("border-radius", "100%")
                                .css("border", "3px solid #3abdaf")
                                .css("background-size", "cover")
                                .css("margin-left", "0.9375rem")
                                .css("margin-right", "0.9375rem")
                                .text("");
                        }
                    );
                }
            }
        );
    }

    function getApi() {
        api = new mw.Api();
        $(".c-item").each(getAvatar);
    }

    function init() {
        mw.loader.using("mediawiki.api", getApi);
    }

    return {
        init: init
    };
}());

if (typeof window === "object") {
    window.addEventListener("load", archifooterComments.init, false);
} else {
    throw "Not in a browser";
}
