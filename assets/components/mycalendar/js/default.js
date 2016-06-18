var myCalendar = {
		Calendars: $('.mycalendar'),
		Dialog: $('#dialog'),
		dayClicked: false,
		delClicked: false,
		Event: {},
		eventDialog: {}
		//multiple: this.Calendars.length > 1
};

$(document)
	.on('mousedown', '.event-close-btn', function() {
		myCalendar.delClicked = true;
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
	myCalendar.Calendars.each(function(i) {
		var instance = $(this).attr('id'),
			eventSource = [{
				url: mcal_config['base'].actionUrl,
				type: 'POST',
				dataType: 'JSON',
				data: {
					action: 'getEvents',
					instance: instance
				},
				error: function() {
					alert('There was an error while fetching events!');
				}
			}];

		if (typeof mcal_config[instance].googleCalendars != 'undefined' && mcal_config[instance].googleCalendars.length > 0)
			eventSource = eventSource.concat(mcal_config[instance].googleCalendars);

		$(this).fullCalendar({
			customButtons: {
				events: {
					text: 'Events',
					click: function() {
						alert('clicked the custom button!');
					}
				}
			},
			header: {
				left: mcal_config[instance].left,
				center: mcal_config[instance].center,
				right: mcal_config[instance].right
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
			defaultView: mcal_config[instance].defaultView,
			defaultTimedEventDuration: mcal_config[instance].defaultDuration,
			axisFormat: mcal_config[instance].axisFormat,
			allDaySlot: mcal_config[instance].allDaySlot,
			weekNumbers: mcal_config[instance].showWeekNumber,
			weekends: mcal_config[instance].showWeekends,
			height: mcal_config[instance].height,
			hiddenDays: mcal_config[instance].hiddenDays,
			editable: mcal_config[instance].editable,
			selectable: mcal_config[instance].editable,
			selectHelper: true,
			unselectAuto: false,
			forceEventDuration: true,
			eventLimit: true, // allow "more" link when too many events
			minTime: mcal_config[instance].minTime,
			maxTime: mcal_config[instance].maxTime,
			fixedWeekCount: mcal_config[instance].fixedWeekCount,
			businessHours:  mcal_config[instance].businessHours,
			select: function(start, end, jsEvent, view ) {
				if (!mcal_config[instance].editable) return false;
				var allDay = !$.fullCalendar.moment(end).hasTime();
				if (myCalendar.dayClicked && !allDay) {
					var duration = moment.duration(mcal_config[instance].defaultDuration);
					end = moment(start).add(duration);
					myCalendar.dayClicked = false;
				}
				var _event = {title: mcal_config['base'].str.newEvent, mode: "new", private: false, id: 0, allDay: allDay};
				$.post( mcal_config['base'].actionUrl, {action: 'openDlg',id: 0, start: moment(start).format('YYYY-MM-DD HH:mm'), end: moment(end).format('YYYY-MM-DD HH:mm'), allDay: allDay, instance: instance} ,function( data ) {
					myCalendar.Dialog.html(data);
					myCalendar.eventDialog.open(_event, view, $(jsEvent.target).parents('.mycalendar'));
				});
			},

			googleCalendarApiKey: mcal_config['base'].googleCalendarApiKey,
			dayClick: function() {
				myCalendar.dayClicked = true;
			},
			eventClick: function (event, jsEvent, view) {
				if (!event.google) {
					if (myCalendar.delClicked) {
						myCalendar.delClicked = false;
						event.mode = "remove";
						if (mcal_config[instance].editable) {
							myCalendar.Event.remove(event,instance);
						}
						return false;
					}
					if (mcal_config[instance].editable || (!mcal_config[instance].editable && mcal_config[instance].showDialog)) {
						$.post(mcal_config['base'].actionUrl, {action: 'openDlg', id: event.id, instance: instance}, function (data) {
							event.mode = "edit";
							myCalendar.Dialog.html(data);
							myCalendar.eventDialog.open(event, view, $(jsEvent.target).parents('.mycalendar'));
						});
					}
				}
			},
			eventAfterRender: function(event, element, view) {
				if (!event.google && mcal_config[instance].editable) {
					element.append('<a href="#" class="event-close-btn">&times;</a>').addClass('fc-editable');
				}
				if (!event.google) event.calendarName = mcal_config['base'].calendarName;
				element.qtip({
					overwrite: true,
					solo: true,
					content: {
						title: event.calendarName,
						text: '<p>'+event.title+'</p>'+event.description
					},
					position: {
						my: 'bottom center',
						at: 'top center',
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
				myCalendar.Event.change(event, revertFunc, instance);
			},
			eventResize: function(event, delta, revertFunc) {
				event.mode = 'resize';
				myCalendar.Event.change(event, revertFunc, instance);
			},
			eventRender: function( event, element, view ) {
				event.description = event.description || '';
			},
			eventSources: eventSource
		});
	});
});

myCalendar.eventDialog = {
	open : function(event, view, Calendar) {
		var instance = Calendar.attr('id'),
			buttons = {},
			dlg_title = '';
		myCalendar.eventDialog.init(instance);

		if (event.mode == 'new') {
			buttons = [
				{
					text: mcal_config['base'].buttons.add,
					class:"ui-add-button ui-state-hover",
					click: function () {
						myCalendar.Event.save(event,view,this,instance);
					}
				},
				{
					text: mcal_config['base'].buttons.close,
					class:"ui-close-button",
					click: function () {
						$(this).dialog("close");
						Calendar.fullCalendar('unselect');
					}
				}
			];
			dlg_title = event.title;
		} else {
			if (!mcal_config[instance].editable) {
				buttons = [
					{
						text: mcal_config['base'].buttons.close,
						class: "ui-close-button",
						click: function () {
							$(this).dialog("close");
						}
					}
				];
			} else {
				buttons = [
					{
						text: mcal_config['base'].buttons.delete,
						class: "ui-remove-event-button",
						click: function () {
							event.mode = "remove";
							if (mcal_config[instance].editable) {
								$(this).dialog("close");
								myCalendar.Event.remove(event, instance);
							}
						}
					},
					{
						text: mcal_config['base'].buttons.save,
						class: "ui-edit-button  ui-state-hover ui-primary-button",
						click: function () {
							myCalendar.Event.save(event, view, this, instance);
						}
					},
					{
						text: mcal_config['base'].buttons.close,
						class: "ui-close-button",
						click: function () {
							$(this).dialog("close");
						}
					}
				];
			}
			dlg_title = mcal_config['base'].str.editEvent;
		}
		$('div.qtip').hide();
		myCalendar.Dialog.dialog({
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
				Calendar.fullCalendar('unselect')
			},
			width:390,
			buttons: buttons
		});
		if (!mcal_config[instance].editable) {
			$('.ui-edit-button').attr('disabled', 'disabled');
			$('.ui-remove-event-button').attr('disabled', 'disabled');
		}
	},
	init: function(instance) {
		$('.date').datepicker();
		$('.time').timepicker({
			timeFormat: 'H:i',
			step: 30,
			minTime: mcal_config[instance].minTime,
			maxTime: mcal_config[instance].maxTime
		});
		$('#color').colorpicker({
			showOn:'button',
			displayIndicator: false,
			strings: mcal_config['base'].str.colorString
		});
	}
};

myCalendar.Event = {
	save: function (event, view, dialog, instance) {
		var event_data = {
			action: 'saveEvent',
			instance: instance,
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
			url: mcal_config['base'].actionUrl,
			dataType: 'JSON',
			data: event_data,
			success: function (res) {
				if (res.success) {
					for (var prop in res.data) {
						if (res.data.hasOwnProperty(prop)) {
							event[prop] = res.data[prop];
						}
					}
					myCalendar.Calendars.fullCalendar('unselect');
					if (event.mode == 'new') {
						myCalendar.Calendars.fullCalendar('renderEvent', event);
					} else {
						$('#'+instance).fullCalendar('updateEvent', event);
						var _event;
						myCalendar.Calendars.not('#'+instance).each(function(){
							_event = $(this).fullCalendar( 'clientEvents' ,event.id )[0];
							if (typeof _event != 'undefined') {
								for (var prop in event) {
									if (event.hasOwnProperty(prop)) {
										_event[prop] = event[prop];
									}
								}
								$(this).fullCalendar('updateEvent', _event);
							} else {
								_event = myCalendar.Event.clone(event);
								$(this).fullCalendar('renderEvent', _event);
							}
						});
					}
					$(dialog).dialog("close");

				} else {
					alert(res.message);
					if (typeof res.field != 'undefined' && res.field) $("#"+res.field).addClass('input-error').focus();
				}
			},
			error: function() {
				alert("Request error!");
			}
		});
	},
	change: function (event, revertFunc,instance) {
		$('div.qtip').remove();
		var event_data = {
			action: 'saveEvent',
			instance: instance,
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
			url: mcal_config['base'].actionUrl,
			dataType: 'JSON',
			data: event_data,
			success: function (res) {
				if (res.success) {
					$('#'+instance).fullCalendar('updateEvent', event);
					var _event;
					myCalendar.Calendars.not('#'+instance).each(function(){
						_event = $(this).fullCalendar( 'clientEvents' ,event.id )[0];
						if (typeof _event != 'undefined') {
							for (var prop in event) {
								if (event.hasOwnProperty(prop)) {
									_event[prop] = event[prop];
								}
							}
							$(this).fullCalendar('updateEvent', _event);
						} else {
							_event = myCalendar.Event.clone(event);
							$(this).fullCalendar('renderEvent', _event);
						}
					});
				} else {
					alert(res.message);
					revertFunc();
				}
			}
		})
	},
	remove: function(event,instance) {
		$('#remove-dialog').dialog({
			resizable: false,
			modal: true,
			title: mcal_config['base'].str.deleteEvent,
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
					text: mcal_config['base'].buttons.delete,
					class:"ui-remove-button",
					click: function () {
						$(this).dialog("close");
						$.post( mcal_config['base'].actionUrl, {action: 'removeEvent',id: event.id, instance: instance} ,function( res ) {
							if (res.success) {
								myCalendar.Calendars.fullCalendar('removeEvents', event.id);
							} else {
								alert(res.message);
							}
						},'json');
					}
				},
				{
					text: mcal_config['base'].buttons.close,
					class:"ui-close-button ui-state-hover ui-primary-button",
					click: function () {
						$(this).dialog("close");
					}
				}
			]
		});
	},
	clone: function(event) {
		return {id:event.id,start:event.start,end:event.end,title:event.title,description:event.description,calendarName:event.calendarName,allDay:event.allDay,className:event.className,color:event.color,backgroundColor:event.backgroundColor,borderColor:event.borderColor,textColor:event.textColor,google:event.google}
	}
};