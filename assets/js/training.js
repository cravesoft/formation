$(document).on('click', '.create-request', function () {
    var self = this;
    var session_id = $(this).data('id');
    var $session = $('#session'+session_id+' ul')
    var title = $('h2.training-title').text();
    var time = $session.find('.session-time span').text();
    var level = $session.find('.session-level span').text();
    $("#requestModal .modal-body .training-title h4").text(title);
    $("#requestModal .modal-body .session-time span").text(time);
    $("#requestModal .modal-body .session-level span").text(level);
    $('#request').unbind('click');
    $('#request').click(function () {
        $('#requestModal').modal('hide');
        $(self).unbind('click').removeClass('btn-success').prop('disabled', true).text('Added');
        $.post("/requests/new", { session_id: session_id }, function(data) {
            var remaining_seats = parseInt($session.find('.session-users span').text());
            remaining_seats--;
            $session.find('.session-users span').text(remaining_seats);
        });
    });
});
