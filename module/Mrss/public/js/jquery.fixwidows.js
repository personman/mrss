// Prevent typography widows on labels
$.fn.fixWidows = function() {
    return this.each(function(){
        var string = $(this).html();

        // Check for inputs so we don't break multicheckboxes
        if (string.indexOf('<input') == -1) {
            string = string.replace(/ ([^ ]*)$/,'&nbsp;$1');
            $(this).html(string);
        }
    });
}

$(function() {
    $('label, h1, h2, h3, h4, h5').not('input').fixWidows()
})

