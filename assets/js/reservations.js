Date.prototype.addMinutes= function(m) {
    this.setMinutes(this.getMinutes()+m);
    return this;
}

window.reservations = {};

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
            defaultView: 'agendaWeek',
            eventSources: [ getEvents() ],
            viewDisplay: function (view) {
                $('#calendar').fullCalendar('removeEvents');
                $('#calendar').fullCalendar('addEventSource', getEvents());
                $('#calendar').fullCalendar('rerenderEvents');
            },
            editable: true,
            eventResize: function(event, dayDelta, minuteDelta, revertFunc) {
                if(event.start <= new Date() || isOverlapping(event)) {
                    revertFunc();
                }
            },
            eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc) {
                if(allDay) {
                    revertFunc();
                } else {
                    var room_id = Number($('#room option:selected').first().val());
                    var found = false;
                    for(var i = 0 ; i < reservations[room_id].length ; i++) {
                        if(event === reservations[room_id][i]) {
                            found = true;
                            break;
                        }
                    }
                    if(event.start <= new Date() || !found || isOverlapping(event)) {
                        revertFunc();
                    }
                }

            },
            eventClick: function(calEvent, jsEvent, view) {
                var room_id = Number($('#room option:selected').first().val());
                for(var i = 0 ; i < reservations[room_id].length ; i++) {
                    if(calEvent === reservations[room_id][i]) {
                        if(confirm("Delete this event?")) {
                            reservations[room_id].splice(i, 1);
                            $('#calendar').fullCalendar("removeEvents", calEvent._id);
                            $('#calendar').fullCalendar("rerenderEvents");
                        }
                        break;
                    }
                }
            },
            dayClick: function(startDate, allDay, jsEvent, view) {
                var endDate = new Date(startDate).addMinutes(30);
                if(!allDay) {
                    var event = {
                        title: $('#training_title').val(),
                        start: startDate,
                        end: endDate,
                        color: 'red',
                        allDay: false
                    };
                    if(startDate > new Date() && !isOverlapping(event)) {
                        var room_id = Number($('#room option:selected').first().val());
                        reservations[room_id].push(event);
                        $('#calendar').fullCalendar('renderEvent', event, true);
                    }
                }
                $('#calendar').fullCalendar('select', startDate, endDate, allDay);
                $('#calendar').fullCalendar('gotoDate', startDate);
            }
        });
    }
};

function isOverlapping(event) {
    var array = $('#calendar').fullCalendar('clientEvents');
    for(i in array) {
        if(event !== array[i]) {
            if(event.end > array[i].start && event.start < array[i].end) {
                return true;
            }
        }
    }
    return false;
}

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
    var room_id = Number($('#room option:selected').first().val());
    var url = '../reservations';
    var data = {
        room_id: room_id,
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
                        title: data.reservations[i].session.training.title,
                        start: data.reservations[i].startDate.date,
                        end: data.reservations[i].endDate.date,
                        url: '../trainings/' + data.reservations[i].session.training.id,
                        allDay: false
                    });
                }
            }
        }
    });
    if(room_id in reservations) {
        for(var i = 0 ; i < reservations[room_id].length ; i++) {
            events.push(reservations[room_id][i]);
        }
    } else {
        reservations[room_id] = [];
    }
    return events;
}

$('#submit-session').click(function(e) {
    'use strict';
    var url = '../reservations/new';
    for(var room_id in reservations) {
        for(var i = 0 ; i < reservations[room_id].length ; i++) {
            var event = reservations[room_id][i];
            var data = {
                room_id: room_id,
                start_date: event.start.toUTCString(),
                end_date: event.end.toUTCString()
            };
            $.ajax({
                url: url,
                type: "POST",
                data: data,
                dataType: 'json',
                async: false,
                success: function(data) {
                    if(null !== data) {
                        $('#reservations').append('<input type="hidden" name="reservations[]" value="'+data.reservation_id+'">');
                    }
                }
            });
        }
    }
    //e.preventDefault();
});

$("#room").change(function() {
    $('#calendar').fullCalendar('render');
});
