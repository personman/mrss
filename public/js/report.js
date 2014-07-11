$(function() {
    // Colorize percentiles
    /*$('.percentileColor').each(function() {
        var td = $(this);
        var value = parseFloat(td.html());

        var breakPoints = [
            {max: 25, color: '#B30000'},
            {max: 75, color: '#000000'},
            {max: 100, color: '#00B300'}
        ];

        for (var i in breakPoints) {
            var point = breakPoints[i];

            if (value <= point.max) {
                td.css('color', point.color);
                break;
            }
        }
    })*/

    // Toggle charts on national report
    $('.openChart').click(function() {
        $(this).parent().parent().next().toggle()
        return false
    })

    // Show all charts
    $('.showAllCharts').click(function() {
        if ($(this).attr('title').search('Hide') == -1) {
            $('.nationalReportChart').show()
            $(this).attr('title', 'Hide all charts')
        } else {
            $('.nationalReportChart').hide()
            $(this).attr('title', 'Show all charts')
        }

        return false
    })
})
