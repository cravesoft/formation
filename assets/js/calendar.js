window.calendar = {
    init: function () {
        'use strict';
        /* Draw calendar */
        $('#calendar').fullCalendar({
            firstDay:1,
            timeFormat: 'H:mm',
            header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
            defaultView: 'month',
            eventSources: [ getEvents() ],
            viewDisplay: function (view) {
                $('#calendar').fullCalendar('removeEvents');
                $('#calendar').fullCalendar('addEventSource', getEvents());
                $('#calendar').fullCalendar('rerenderEvents');
            }
        });
    }
};

$(document).ready(function() {
    'use strict';
    calendar.init();
});

var getEvents = function() {
    'use strict';
    var view = $('#calendar').fullCalendar('getView');
    var events = [{}];
    if(view.visStart === undefined) {
        return events;
    }
    var url = '../reservations';
    var data = {
        start_date: view.visStart.toUTCString(),
        end_date: view.visEnd.toUTCString()
    };
    $.ajax({
        url: url,
        data: data,
        dataType: 'json',
        async: false,
        success: function(data) {
            if(data.reservations.length) {
                for(var i = 0; i < data.reservations.length ; i++) {
                    events.push({
                        title: data.reservations[i].session.training.title +
                            ' / ' + data.reservations[i].room.name,
                        start: data.reservations[i].startDate.date,
                        end: data.reservations[i].endDate.date,
                        url: '../trainings/' + data.reservations[i].session.training.id,
                        allDay: false
                    });
                }
            }
        }
    });
    return events;
}
