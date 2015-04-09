<!--suppress ALL -->
<div class="dialog-content">
	<form id="eventForm" role="form">
		<div class="form-group">
			<label class="labelform">[[%mc.title]]</label>
			<input class="form-control" type="text" id="title" name="title" title="[[%mc.title_title]]" maxlength="40" placeholder="[[%mc.title_title]]" value="[[+title]]">
			<input type="checkbox" id="color" name="color" value="[[+color]]">

		</div>
		<div class="form-group" style="display:inline-block;">
			<input class="form-control date" type="text" id="start_date" name="start_date" title="[[%mc.start_date]]" value="[[+start_date]]">
			<input class="form-control time" type="text" id="start_time" name="start_time" title="[[%mc.start_time]]" value="[[+start_time]]" [[+allDay:is=`1`:then=`disabled`:else=``]]>
		</div>
		<div style="display:inline-block;">&ndash;</div>
		<div class="form-group" style="display:inline-block;">
			<input class="form-control date date_end" type="text" id="end_date" name="end_date" title="[[%mc.end_date]]" value="[[+end_date]]">
			<input class="form-control time" type="text" id="end_time" name="end_time" title="[[%mc.end_time]]" value="[[+end_time]]" [[+allDay:is=`1`:then=`disabled`:else=``]]>
		</div>
		<div class="form-group last">
			<label class="labelform">[[%mc.description]]</label>
			<textarea class="form-control" id="description" name="description" title="[[%mc.description_title]]" rows="5" style="resize:vertical;">[[+description]]</textarea>
		</div>
		<div class="form-group last">
			<div style="display:inline-block;">
				<input type="checkbox" id="allday" name="allday" [[+allDay:is=`1`:then=`checked`:else=``]]>
				<label for="allday" style="margin-top: 10px;"><span></span>[[%mc.allday]]</label>
			</div>
		</div>

	</form>
</div>