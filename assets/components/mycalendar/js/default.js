var Calendar = $('#calendar');
var Dialog = $('#dialog');
var dayClicked = false;
var delClicked = false;
var eventSource = [{
		url: mcal_config.actionUrl,
		type: 'POST',
		dataType: 'JSON',
		data: {
			action: 'getEvents'
		},
		error: function() {
			alert('There was an error while fetching events!');
		}
	}];

if (typeof mcal_config.googleCalendars != 'undefined' && mcal_config.googleCalendars.length > 0)
	eventSource = eventSource.concat(mcal_config.googleCalendars);

$(document)
	.on('mousedown', '.event-close-btn', function() {
		delClicked = true;
		return false;
	})
	.on('click', '.event-close-btn', function() {
		return false;
	})
	.on('change', '#title, #start_date', function() {
		var _val = $.trim($(this).val());
		if (_val != '') $(this).removeClass('input-error');
	})
	 .on('change', '#start_time, #end_time', function() {
	 	$(this).removeClass('input-error');
	 })
	.on('click', '#allday', function() {
		if ($(this).is(':checked')) {
			$('input.time').prop('disabled','disabled');
		} else {
			$('input.time').removeAttr('disabled');
		}
	});
/********************************************/
$(document).ready(function() {
	Calendar.fullCalendar({
		header: {
			left: 'today,prev,next',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		views: {
			month: { // name of view
				titleFormat: 'MMMM YYYY'
			},
			week: {
				// options apply to basicWeek and agendaWeek views
				titleFormat: "DD MMMM YYYY",
				columnFormat: 'ddd, D'
			},
			day: {
				titleFormat: "LL"
			}
		},
		monthYearFormat: 'MMMM YYYY',
		defaultView: mcal_config.defaultView,
		defaultTimedEventDuration: mcal_config.defaultDuration,
		axisFormat: mcal_config.axisFormat,
		weekNumbers: mcal_config.showWeekNumber,
		weekends: mcal_config.showWeekends,
		height: mcal_config.height,
		hiddenDays: mcal_config.hiddenDays,
		editable: mcal_config.editable,
		selectable: true,
		selectHelper: true,
		unselectAuto: false,
		forceEventDuration: true,
		eventLimit: true, // allow "more" link when too many events
		minTime: mcal_config.minTime,
		maxTime: mcal_config.maxTime,
		fixedWeekCount: mcal_config.fixedWeekCount,

		/*
		businessHours: {
			start: '10:00',
			end: '18:00',
			dow: [1, 2, 3, 4 ,5]
		},
		*/
		//eventColor: 'red',
		select: function(start, end, jsEvent, view ) {
			var allDay = !$.fullCalendar.moment(end).hasTime();
			//if (moment(end).hasTime()) alert(end);
			if (dayClicked && !allDay) {
				var duration = moment.duration(mcal_config.defaultDuration);
				end = moment(start).add(duration);
				dayClicked = false;
			}
			var _event = {title: mcal_config.str.newEvent, mode: "new", private: false, id: 0, allDay: allDay};
			$.post( mcal_config.actionUrl, {action: 'openDlg',id: 0, start: moment(start).format('YYYY-MM-DD HH:mm'), end: moment(end).format('YYYY-MM-DD HH:mm'), allDay: allDay} ,function( data ) {
				Dialog.html(data);
				if (mcal_config.editable)
					eventDialog.open(_event, view);
			});
		},

		googleCalendarApiKey: mcal_config.googleCalendarApiKey,
		dayClick: function() {
			dayClicked = true;
		},
		eventClick: function (event, jsEvent, view) {
			if (!event.google) {
				if (delClicked) {
					delClicked = false;
					event.mode = "remove";
					if (mcal_config.editable) {
						Event.remove(event);
					}
					return false;
				}
				$.post(mcal_config.actionUrl, {action: 'openDlg', id: event.id}, function (data) {
					event.mode = "edit";
					Dialog.html(data);
					eventDialog.open(event, view);
				});
			}
		},
		eventAfterRender: function(event, element, view) {
			if (!event.google && mcal_config.editable) {
				element.append('<a href="#" class="event-close-btn">&times;</a>');
				event.calendarName = mcal_config.calendarName;
			}
			element.qtip({
				overwrite: true,
				solo: true,
				content: {
					title: event.calendarName,
					text: event.title
				},
				position: {
					my: 'bottom center',  // Position my top left...
					at: 'top center', // at the bottom right of...
					target: element
				},
				show: {
					delay:200,
					target: element,
					solo: true
				},
				style: {
					classes: 'qtip-bootstrap'
				}
			});
		},
		eventDrop: function(event, delta, revertFunc) {
			event.mode = 'move';
			Event.change(event, revertFunc);
		},
		eventResize: function(event, delta, revertFunc) {
			event.mode = 'resize';
			Event.change(event, revertFunc);
		},
		eventRender: function( event, element, view ) {
			event.description = event.description || '';
		},
		eventSources: eventSource
	});
});

eventDialog = {
	open : function(event, view) {
		eventDialog.init(event);
		var buttons = {};
		var dlg_title = '';
		if (event.mode == 'new') {
			buttons = [
				{
					text: mcal_config.buttons.add,
					class:"ui-add-button",
					click: function () {
						Event.save(event,view,this);
					}
				},
				{
					text: mcal_config.buttons.close,
					class:"ui-close-button",
					click: function () {
						$(this).dialog("close");
						Calendar.fullCalendar('unselect');
					}
				}

			];
			dlg_title = event.title;
		} else {
			buttons = [
				{
					text: mcal_config.buttons.save,
					class:"ui-edit-button",
					click: function () {
						Event.save(event,view,this);
					}
				},
				{
					text: mcal_config.buttons.close,
					class:"ui-close-button",
					click: function () {
						$(this).dialog("close");
					}
				}

			];
			dlg_title = mcal_config.str.editEvent;
		}
		$('div.qtip').hide();
		Dialog.dialog({
			resizable: false,
			modal: true,
			title: dlg_title,
			show: {
				effect: "fadeIn",
				duration: 300
			},
			hide: {
				effect: "fadeOut",
				duration: 200
			},
			close: function() {
				$('#calendar').fullCalendar('unselect')
			},
			width:390,
			buttons: buttons
		});
		if (!mcal_config.editable) {
			$('.ui-edit-button').attr('disabled', 'disabled');
		}
	},
	init: function() {
		$('.date').datepicker();
		$('.time').timepicker({
			timeFormat: 'H:i',
			step: 30,
			minTime: mcal_config.minTime,
			maxTime: mcal_config.maxTime
		});
		$('#color').colorpicker({
			showOn:'button',
			displayIndicator: false,
			strings: mcal_config.str.colorString
		});
	}
};

var Event = {
	save: function (event, view, dialog) {
		var event_data = {
			action: 'saveEvent',
			mode: event.mode,
			id: event.id,
			title: $('#title').val(),
			description: $('#description').val(),
			start_date: $('#start_date').val(),
			start_time: $('#start_time').val(),
			end_date: $('#end_date').val(),
			end_time: $('#end_time').val(),
			calendar: $('#calendars').val(),
			allDay: $('#allday').is(':checked'),
			color: $('#color').val()
		};
		$.ajax({
			type: "POST",
			url: mcal_config.actionUrl,
			dataType: 'JSON',
			data: event_data,
			success: function (res) {
				if (res.success) {
					for (var prop in res.data) {
						if (res.data.hasOwnProperty(prop)) {
							event[prop] = res.data[prop];
						}
					}
					//event.end=event.start;
					Calendar.fullCalendar('unselect');
					if (event.mode == 'new') {
						Calendar.fullCalendar('renderEvent', event, true);
					} else {
						Calendar.fullCalendar('updateEvent', event);
					}
					$(dialog).dialog("close");

				} else {
					alert(res.message);
					if (typeof res.field != 'undentified' && res.field) $("#"+res.field).addClass('input-error').focus();
				}
			},
			error: function() {
				alert("Ошибка запроса!");
			}
		});
	},
	change: function (event, revertFunc) {
		$('div.qtip').remove();
		var event_data = {
			action: 'saveEvent',
			mode: event.mode,
			id: event.id,
			start: moment(event.start).format('YYYY-MM-DD HH:mm'),
			allDay: event.allDay
		};
		if (event.end) {
			event_data.end = moment(event.end).format('YYYY-MM-DD HH:mm');
		}
		$.ajax({
			type: "POST",
			url: mcal_config.actionUrl,
			dataType: 'JSON',
			data: event_data,
			success: function (res) {
				if (res.success) {
					Calendar.fullCalendar('updateEvent', event);
				} else {
					alert(res.message);
					revertFunc();
				}
			}
		})
	},
	remove: function(event) {
		$('#remove-dialog').dialog({
			resizable: false,
			modal: true,
			title: mcal_config.str.deleteEvent,
			show: {
				effect: "fadeIn",
				duration: 200
			},
			hide: {
				effect: "fadeOut",
				duration: 200
			},
			width:300,
			buttons: [
				{
					text: mcal_config.buttons.delete,
					class:"ui-remove-button",
					click: function () {
						$(this).dialog("close");
						$.post( mcal_config.actionUrl, {action: 'removeEvent',id: event.id} ,function( res ) {
							if (res.success) {
								Calendar.fullCalendar('removeEvents', event.id);
							} else {
								alert(res.message);
							}
						},'json');
					}
				},
				{
					text: mcal_config.buttons.close,
					class:"ui-close-button",
					click: function () {
						$(this).dialog("close");
					}
				}
			]
		});
	}

};