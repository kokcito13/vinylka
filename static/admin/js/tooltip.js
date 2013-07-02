(function ($) {
    $.fn.easyTooltip = function (options) {
        var defaults = {
            xOffset: 10,
            yOffset: 25,
            tooltipId: "easyTooltip",
            clickRemove: false,
            content: "",
            useElement: ""
        };
        var options = $.extend(defaults, options);
        var content;
        this.each(function () {
            var title = $(this).attr("title");
            $(this).hover(function (e) {
                content = (options.content != "") ? options.content : title;
                content = (options.useElement != "") ? $("#" + options.useElement).html() : content;
                $(this).attr("title", "");
                if (content != "" && content != undefined) {
                    $("body").append("<div id='" + options.tooltipId + "'>" + "<div><div style='width:5px;height:29px;float:left;'></div><div style='background:#e9e9e9;padding-right:10px;padding-left:10px;border:1px solid #878787;min-width:135px;-webkit-border-radius: 10px;-moz-border-radius: 10px;border-radius: 10px;width:auto!important;width:135px;height:24px;float:left;padding-top:7px;text-align:center;color:black;font-size:13px;'>" + content + "</div><div style='width:5px;height:29px;float:left;'></div><div style='clear:both;'></div><div style='width:12px;height:9px;position:relative;top:-3px;left:8px;'></div></div>" + "</div>");
                    $("#" + options.tooltipId).css("position", "absolute").css("top", (e.pageY - options.yOffset) + "px").css("left", (e.pageX + options.xOffset) + "px").css("display", "none").delay(500).fadeIn("fast")
                }
            }, function () {
                $("#" + options.tooltipId).remove();
                $(this).attr("title", title)
            });

            $(this).mousemove(function (e) {
                if ($(document).width() / 2 < e.pageX) {
                    $("#" + options.tooltipId)

                    .css("top", (e.pageY - options.yOffset - options.yOffset) + "px").css('left', (e.pageX - $("#" + options.tooltipId).width() - options.xOffset - options.xOffset) + "px").css("display", "none").fadeIn("fast")
                } else {
                    $("#" + options.tooltipId)

                    .css("top", (e.pageY - options.yOffset) + "px").css("left", (e.pageX + options.xOffset) + "px").css("display", "none").fadeIn("fast")
                }
            });
            if (options.clickRemove) {
                $(this).mousedown(function (e) {
                    $("#" + options.tooltipId).remove();
                    $(this).attr("title", title)
                })
            }
        })
    }
})(jQuery);