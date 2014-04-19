$(document).on('click', '.edit-request', function (e) {
    var self = this;
    var request_id = $(this).data('request_id');
    var action = $(this).data('action');
    var data = {
        request_id: request_id,
        action: action
    };
    $.post("/requests/edit", data, function(data) {
        if(data !== null) {
            if(data['action'] === 'refuse') {
                $(self).text('Refused');
            } else {
                $(self).text('Accepted');
            }
            $(self).removeClass('edit-request').attr('disabled', 'disabled');
            $(self).closest('a').next().remove();
            $(self).closest('a').prev().remove();
        }
    });
    e.preventDefault();
});
