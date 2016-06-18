## myCalendar

myCalendar is a ModX Revolution component for event management using fullCalendar to render events.You can create, move, resize and remove events.  
myCalendar can display events from a public Google Calendar.

## Basic Use
#####For single calling
```
[[!myCalendar]]
```
![myCalendar](https://file.modx.pro/files/a/1/3/a1355b1435283b29c0969d37db272c73s.jpg)

#####For multiple calling 
You can call myCalendar multiple times. To do this you must set an instance parameter. Important, the instance does not show the different calendar, it shows the same events but with the other parameters.
```
[[!myCalendar? &instance=`calendar1`]]
[[!myCalendar? &instance=`calendar2` &right=`` &left=`` &defaultView=`agendaDay`]]
```
![Multiple instance of myCalendar](https://file.modx.pro/files/b/4/4/b4429355714ff7121292321d174a554a.png)

## Settings
- mycalendar.google_calendar_api_key - Google Calendar API key. How to get it read this [instruction](http://fullcalendar.io/docs/google_calendar/).
- mycalendar.default_css - myCalendar css file. By default, default.min.css. It can be replaced by yours or empty.
- mycalendar.default_js - myCalendar js file. By default, default.js. It can be replaced by yours or empty.

## Snippet properties
All properties have detailed description. For more information see [fullCalendar doc](http://fullcalendar.io/docs/).   

You can try it on [demo page](http://modzone.ru/mycalendar.html).  

##Bugs and improvements
Feel free to suggest ideas/improvements/bugs on GitHub:
http://github.com/sergant210/myCalendar/issues
