var savedCharts = {}

$(function() {

    setUpSelects()

    // Chart type
    updateFormForChartType()
    $('#inputType').change(function() {
        updateFormForChartType()
    })
})

function setUpSelects()
{
    $('#benchmark1, #benchmark2, #benchmark3').chosen({search_contains: true})
}

function updateFormForChartType()
{
    var chartType = $('#inputType').val()
    var sizeField = $('#control-group-benchmark3')
    var benchmark2 = $('#control-group-benchmark2')
    var yearField = $('#control-group-years')

    if (chartType == 'bubble') {
        sizeField.slideDown()
    } else {
        sizeField.slideUp();
    }

    if (chartType == 'line') {
        yearField.slideUp()
        benchmark2.slideUp()
    } else {
        yearField.slideDown()
        benchmark2.slideDown()
    }
}

function postChart(id)
{
    if (savedCharts[id]) {
        //config = $.parseJSON(savedCharts[id])
        //config['buttons'] = null
        //console.log(config)
        //return false
        //postToURL('/reports/explore', config)
        post_to_url('/reports/explore', {'id': id})
    }

    return false
}

function post(path, params, method) {
    method = method || "post"; // Set method to post by default if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
        }
    }

    document.body.appendChild(form);
    form.submit();
}

function post_to_url(path, params, method) {
    method = method || "post"; // Set method to post by default, if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    var addField = function( key, value ){
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("type", "hidden");
        hiddenField.setAttribute("name", key);
        hiddenField.setAttribute("value", value );

        form.appendChild(hiddenField);
    };

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            if( params[key] instanceof Array ){
                for(var i = 0; i < params[key].length; i++){
                    addField( key, params[key][i] )
                }
            }
            else{
                addField( key, params[key] );
            }
        }
    }

    document.body.appendChild(form);
    form.submit();
}
