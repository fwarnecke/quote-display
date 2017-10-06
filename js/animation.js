var settings = $.parseJSON(settingsJSON);
var data = [];

function animate( aniName) {
    switchQuote();
    
    $("#page-wrapper").css( "animation", aniName + "-in " + settings.aniDuration + "s linear");
    $("#page-wrapper").css( "-webkit-animation", aniName + "-in " + settings.aniDuration + "s linear");

    setTimeout(function() {
        $("#page-wrapper").css( "animation", aniName + "-out " + settings.aniDuration + "s linear");
        $("#page-wrapper").css( "-webkit-animation", aniName + "-out " + settings.aniDuration + "s linear");

        setTimeout(function() {
            animate( aniName);
        }, parseFloat(settings.aniDuration) * 1000);
    }, parseInt(settings.time) * 1000);
}

function animateDelayed(aniName) {
    switchQuote();

    var count = parseInt( settings.count.charAt(0)) * parseInt( settings.count.charAt(2));

    var time = parseFloat(settings.aniDuration);

    var timeTotal = (time + 0.5) * count;

    $(".quote-container").css("visibility", "hidden");

    //"Liste" von Zufallszahlen
    //brauche bessere funktion: diese hat keine feste Laufzeit
    var container = [];
    for (var i = 0; i < count; i++) {
        do {
            random = Math.floor( Math.random() * count);
        } while ( container.indexOf( random) != -1);
        container[i] = random;
    }

    //Erscheine-Animation, immer versetzt um die Zeit der Animationen vorher
    for (var i = 0; i < count; i++) {
        setTimeout(function(containerNr) {
            animateContainerIn(aniName, containerNr);
        },(time + 0.5) * i * 1000, container[i]);
    }

    //Wartezeit + Zeit, die das Erscheinen braucht
    setTimeout(function() {
        //Ausblende-Animation, immer versetzt um die Zeit der Animationen vorher
        for (var i = 0; i < count; i++) {
            setTimeout(function(containerNr) {
                animateContainerOut(aniName, containerNr);
            },(time + 0.5) * i * 1000, container[i]);
        }

        //Rekursiver Neu-Aufruf der Funktion versetzt um die Zeit, die das Ausblenden braucht
        setTimeout(function() {
            animateDelayed(aniName);
        }, timeTotal * 1000);
    }, (parseInt(settings.time) + timeTotal) * 1000);
}

function animateContainerIn(aniName, containerNr) {
    $(".quote-nr-" + containerNr).css("visibility", "visible");

    $(".quote-nr-" + containerNr).css( "animation", aniName + "-in " + settings.aniDuration + "s linear");
    $(".quote-nr-" + containerNr).css( "-webkit-animation", aniName + "-in " + settings.aniDuration + "s linear");
}

function animateContainerOut(aniName, containerNr) {
    $(".quote-nr-" + containerNr).css( "animation", aniName + "-out " + settings.aniDuration + "s linear");
    $(".quote-nr-" + containerNr).css( "-webkit-animation", aniName + "-out " + settings.aniDuration + "s linear");

    setTimeout(function() {
        $(".quote-nr-" + containerNr).css("visibility", "hidden");
    }, parseFloat(settings.aniDuration) * 1000);
}

function switchQuote() {
    var count = parseInt( settings.count.charAt(0)) * parseInt( settings.count.charAt(2));

    if ( count > data.length) {
        alert("Too few quotes for displaying. Create new quotes and/or select less to show.");
    } else {
        var quotes = [];
        for( i = 0; i < count; i++) {
            do { 
                random = Math.floor( Math.random() * data.length);
            } while ( quotes.indexOf( random) != -1);
            quotes[i] = random;
        }
        for( i = 0; i < count; i++) {
            var value = data[quotes.pop()];
            $(".quote-nr-"+ i).find(".author").html( value[1]);
            $(".quote-nr-"+ i).find(".quote").html( value[0]);
            $(".quote-nr-"+ i).css("background-color", "#" + value[2]);
        }
    }
}

function getData() {
    var request = $.ajax({
        url: "data.php",
        method: "POST",
        data: { req : "initial" },
    });
    
    request.done(function( rData ) {
        data = $.parseJSON(rData)
        start();
    });
    
    request.fail(function( jqXHR, textStatus ) {
        $("#connection-lost").css("display", "initial");
    });
}

function start() {
    $("#loading-wrapper").hide();
    $("header").css( "-webkit-filter", "none");
    $("header").css( "    -ms-filter", "none");
    $("header").css( "        filter", "none");
    $("#page-wrapper").css( "-webkit-filter", "none");
    $("#page-wrapper").css( "    -ms-filter", "none");
    $("#page-wrapper").css( "        filter", "none");
    $("#page-wrapper").css( "z-index", "0");

    if (settings.delayed == "true") {
        animateDelayed(settings.animation);
    } else {
        animate(settings.animation);
    }

    update();
}

function update() {
    setTimeout(function() {
        var request = $.ajax({
            url: "data.php",
            method: "POST"
        });
        
        request.done(function( rData ) {
            data = $.parseJSON(rData)
            update();
        });
        
        request.fail(function( jqXHR, textStatus ) {
            $("#connection-lost").css("display", "initial");
        });
        
    }, 10 * 60 * 1000);
}

$(document).ready( function() {
    $(".quote").css( "font-size", settings.size + "px");
    $(".quote").css( "color", "#" + settings.colorQ);
    
    $(".author").css( "font-size", (settings.size - 4) +"px");
    $(".author").css( "color", "#" + settings.colorA);
    
    setTimeout(function() {
        getData();
    }, 100 * 1000);
    //getData();
});