$(document).on('click', '.set-training', function (e) {
    var self = this;
    var training_id = $(this).data('training_id');
    var action = $(this).data('action');
    var data = {
        training_id: training_id,
        action: action
    };
    $.post("/trainings/set", data, function(data) {
        if(data !== null) {
            $(self).parent().find('.set-training.hidden').removeClass('hidden');
            $(self).addClass('hidden');
        }
    });
    e.preventDefault();
});

$(document).on('click', '.delete-training', function () {
    var self = this;
    var training_id = $(this).data('training_id');
    var title = $(this).parent().parent().find('h4.training-title a span').text();
    $("#deleteModal .modal-body .training-title h4").text(title);
    $('#delete').unbind('click');
    $('#delete').click(function () {
        $('#deleteModal').modal('hide');
        $.post("/trainings/delete", { training_id: training_id }, function(data) {
            $(self).parent().parent().remove();
        });
    });
});
