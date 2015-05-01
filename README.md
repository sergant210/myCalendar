## myCalendar

myCalendar is a component for ModX Revolution for event management using fullCalendar to render events.

## Basic Use
#####For single use 
```
[[!myCalendar]]
```
![myCalendar](https://file.modx.pro/files/a/1/3/a1355b1435283b29c0969d37db272c73s.jpg)

#####For multiple use 
If you want to use multiple calendars you must set the instance parameter
```
[[!myCalendar? &instance=`calendar1`]]
[[!myCalendar? &instance=`calendar2` &right=`` &left=`` &defaultView=`agendaDay`]]
```
![Multiple instance of myCalendar](https://file.modx.pro/files/b/4/4/b4429355714ff7121292321d174a554a.png)

## Settings
- mycalendar.google_calendar_api_key - Google Calendar API key. How to get it read this [instruction](http://fullcalendar.io/docs/google_calendar/).
- mycalendar.default_css - myCalendar css file. By default, default.min.css. It can be replaced by yours.
- mycalendar.default_js - myCalendar js file. By default, default.js. It can be replaced by yours.

## Snippet properties
All properties have detailed description. For more information see [fullCalendar doc](http://fullcalendar.io/docs/). 

##Bugs and improvements
Feel free to suggest ideas/improvements/bugs on GitHub:
http://github.com/sergant210/myCalendar/issues
