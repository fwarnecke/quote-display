$("document").ready(function() {
    switch ( $("#title").html()) {
        case "Quotes":
            quote();
            break;

        case "New Quote":
            edit();
            break;

        case "Edit Quote":
            edit();
            break;

        case "Settings":
            settings();
            break;
    
        default:
            break;
    }
});

function quote() {
    $("#table").DataTable({"ordering": false});
}

function edit() {

}

function settings() {
    $("#size").attr({ "max" : fontSizeLimit()});

    $("#countSel").on("change", function() {
        var limit = fontSizeLimit();
        $("#size").attr({ "max" : limit});
        if ($("#size").val() > limit) {
            $("#size").val(limit);
        }
    });

    $("#aniTypeSel").on("change", function() {
        if ($("#aniTypeSel").val() == "move") {
            $("#delay_yes").prop("checked", false);
            $("#delay_yes").prop("disabled", true);
            $("#delay_no").prop("checked", true);
            $("#delay_no").prop("disabled", true);
        } else {
            $("#delay_yes").prop("disabled", false);
            $("#delay_no").prop("disabled", false);
        }
    });

    initializePicker();
}

function fontSizeLimit() {
    var countX = parseInt($("#countSel").val().charAt(0));
    var countY = parseInt($("#countSel").val().charAt(2));

    if (countX == countY) {
        switch (countX) {
            case 1:
                return 55;
                break;

            case 2:
                return 35;
                break;

            case 3:
                return 20;
                break;
        
            default:
                return 17;
                break;
        }
    } else {
        var count = (countX>countY)? countX : countY;
        switch (count) {
            case 2:
                return 45;
                break;

            case 3:
                return 25;
                break;
        
            default:
                return 17;
                break;
        }
    }
    return 17;
}

function bcIsLight(color) {
    color = color.replace("#", "");
    r = parseInt(color.substring(0, 2), 16);
    g = parseInt(color.substring(2, 4), 16);
    b = parseInt(color.substring(4, 6), 16);

    var a = 1 - (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return (a < 0.5);
}

function initializePicker() {
    //close picker on click outside
    $(window).on("click", function(event) {
        $(".color-selector").each( function() {
            if (!$(event.target).closest(".form-group").find(this).is(this)) {
                $(this).css("visibility", "hidden");
            }
        });
    });

    //open picker if clicked on if closed or close if opend
    $(".color-picker").on("click", function(event) {
        selector = $(event.target).parent().find(".color-selector");
        if (selector.css("visibility") == "visible" ) {
            selector.css("visibility", "hidden");
        } else {
            selector.css("visibility", "visible");
        }
    });

    //apply color selection to picker on load
    $(".color-picker").each( function() {
        selected = $(this).closest(".form-group").find("input:checked");
        color = $(selected).val().toUpperCase();
        $(this).css("background-color", "#" + color);
        $(this).val(selected.data("colorName") + ": #" + color);
        $(this).css("color", bcIsLight(color)?"#555555":"white");
    });

    //apply color selection to picker on color select
    $(".color-picker-radio").on("click", function() {
        color = $(event.target).val().toUpperCase();
        picker = $(event.target).closest(".form-group").find(".color-picker");
        picker.css("background-color", "#" + color);
        picker.val($(event.target).data("colorName") + ": #" + color);
        picker.css("color", bcIsLight(color)?"#555555":"white");
    });
}