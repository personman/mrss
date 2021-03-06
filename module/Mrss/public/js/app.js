var allColleges = [];
var allCollegesUrl = '/files/all-colleges.json';
var menuSearchPlaceholder = 'Search Institutions';

$(function() {
    addAdminMenuSearch();
    configHighcharts();
});

function configHighcharts()
{
    if (typeof Highcharts != 'undefined') {
        Highcharts.setOptions({
            lang: {
                thousandsSep: ','
            }
        });
    }
}

function addAdminMenuSearch()
{
    if (institutionsLabel) {
        menuSearchPlaceholder = menuSearchPlaceholder.replace('Institutions', institutionsLabel);
    }

    // The li and form
    var li = $('<li>');
    var form = $('<form>');

    // The input
    var input = $('<input>', {
        type: 'text',
        id: 'adminMenuSearch',
        style: 'margin-left: 3px',
        value: menuSearchPlaceholder
    });

    form.submit(function() {
        return false;
    });


    form.prepend(input);
    li.prepend(form);

    // Drop it into place
    var adminMenu = $('.adminMenuIcon').parents('.dropdown').find('.dropdown-menu');
    adminMenu.prepend(li);
    adminMenu.css('z-index', 97);

    // Prepare the form interactivity
    setUpSearchForm();
}

function setUpSearchForm()
{
    var input = $('#adminMenuSearch');

    if (!allColleges.length) {
        input.focus(function() {
            setSearchFormStateLoading(input);

            if (input.val() == menuSearchPlaceholder) {
                input.val('');
            }

            // Download the search list
            $.get(allCollegesUrl, [], function(data)
            {
                allColleges = data;

                finishSearchSetup();

            });
        });
    }
}

function setSearchFormStateLoading()
{

}

function finishSearchSetup()
{
    var limit = 20;

    var input = $('#adminMenuSearch');
    input.autocomplete({
        delay: 100,
        minLength: 2,
        //source: allColleges,
        source: function(request, response) {
            var results = $.ui.autocomplete.filter(allColleges, request.term);

            response(results.slice(0, limit));
        },
        select: function(event, ui) {
            var id = ui.item.id;

            goToCollege(id);
        },
        open: function(){
            $(this).autocomplete('widget').css('z-index', 100).css('background-color', 'white');
            return false;
        }
    })
}

function goToCollege(id)
{
    location.href = "/colleges/view/" + id;
}

function round(value, exp)
{
    if (typeof exp === 'undefined' || +exp === 0)
        return Math.round(value);

    value = +value;
    exp  = +exp;

    if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0))
        return NaN;

    // Shift
    value = value.toString().split('e');
    value = Math.round(+(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp)));

    // Shift back
    value = value.toString().split('e');
    value = +(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp));

    return value.toFixed(exp);
}

function minuteSecondFormatter(val)
{
    if (typeof val.value != 'undefined') {
        val = val.value
    } else if (typeof val.y != 'undefined') {
        val = val.y
    }

    val = parseInt(val)
    var minutes = Math.floor(val / 60);
    var seconds = val - (minutes * 60);

    seconds = pad('00', seconds, true);

    val = minutes + ':' + seconds;

    return val
}

function numericRadio(val, map)
{
    var formatted = null;

    if (map) {
        var value = null;
        if (typeof val.value != 'undefined') {
            value = val.value
        } else {
            value = val.y
        }

        map = jQuery.parseJSON(map)
        var keys = Object.keys(map)
        var nearestKey = closest(value, keys)
        formatted = map[nearestKey];
    }

    return formatted;
}

function closest (num, arr) {
    var curr = arr[0];
    var diff = Math.abs (num - curr);
    for (var val = 0; val < arr.length; val++) {
        var newdiff = Math.abs (num - arr[val]);
        if (newdiff < diff) {
            diff = newdiff;
            curr = arr[val];
        }
    }
    return curr;
}

function pad(pad, str, padLeft) {
    if (typeof str === 'undefined')
        return pad;
    if (padLeft) {
        return (pad + str).slice(-pad.length);
    } else {
        return (str + pad).substring(0, pad.length);
    }
}

function ucwords (str) {
    return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
        return $1.toUpperCase();
    });
}
