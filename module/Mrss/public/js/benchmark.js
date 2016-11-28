
$(function() {
    // Make benchmrks sortable
    $('tbody.sortable').sortable({
        handle: '.sortHandle',
        connectWith: '.sortable',
        update: function(event, ui) {
            parent = ui.item.parent()


            var benchmarkGroupId = parent.attr('id').split('_').pop()

            var benchmarkIds = [];
            var headingIds = [];
            var i = 1;
            parent.find('tr').each(function() {
                var idParts = $(this).attr('id').split('_')
                var id = parseInt(idParts.pop())

                if (idParts.pop() == 'heading') {
                    headingIds[i] = id
                } else {
                    benchmarkIds[i] = id
                }

                i = i + 1
            })

            data = {
                benchmarkGroupId: benchmarkGroupId,
                benchmarks: benchmarkIds,
                headings: headingIds
            }

            $.post('/benchmark/reorder', data, function(result) {
                //console.log(result)
                if (result != 'ok') {
                    alert('There was a problem saving your sequence. ');
                }
            })
        }
    })

    // Add sparklines
    $.fn.sparkline.defaults.line.minSpotColor = false;
    $.fn.sparkline.defaults.line.maxSpotColor = false;
    $.fn.sparkline.defaults.line.spotColor = false;
    $('.inlinesparkline').sparkline();

    // Make On Report editable
    $('a.onReport').click(function() {
        enableOnReportEdit()
        return false
    })

    $('.onReportCheckbox').click(function() {
        var checked = $(this)[0].checked;
        if (checked) {
            checked = '1';
        } else {
            checked = '0';
        }

        var id = $(this).attr('id').substr(1)
        var url = '/benchmark/on-report/' + id + '/' + checked;

        $.post(url, {}, function(data) {
            if (data != 'ok') {
                alert('Error saving on-report setting.')
            }
        })
    })


})

function enableOnReportEdit()
{
    $('.onReportEditable').show()
    $('.onReportIcon').hide()
}
