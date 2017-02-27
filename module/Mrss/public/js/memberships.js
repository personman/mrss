// jquery extend function
$.extend(
    {
        redirectPost: function(location, args)
        {
            var form = '';
            $.each( args, function( key, value ) {
                form += '<input type="hidden" name="'+key+'" value="'+value+'">';
            });
            $('<form action="'+location+'" method="POST">'+form+'</form>').appendTo('body').submit();
        }
    });

$(document).ready(function() {
    // Send invoice
    $('.sendLink').click(function() {
        console.log('in click')
        var id = $(this).attr('id').split('-').pop()
        var to = prompt('To whom?')

        var redirect = '/memberships/invoice';
        $.redirectPost(redirect, {id: id, to: to});

        return false
    })


    $('#subscriptions').dataTable({
        "order": [[1, "asc"]],
        'aoColumnDefs': [
            // Don't sort on the action column
            {'bSortable': false, 'aTargets': [7]}
        ],
        // show X entries options
        "aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
        'iDisplayLength': 25
    });

} );
