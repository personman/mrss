// Prevent typography widows on labels
$.fn.fixWidows = function() {
    return this.each(function(){
        var string = $(this).html();
        string = string.replace(/ ([^ ]*)$/,'&nbsp;$1');
        $(this).html(string);
    });
}

$(function() {
    $('label, h1, h2, h3, h4, h5').fixWidows()
})

