/* jQuery Calendar v2.7
   Written by Marc Grabanski (m@marcgrabanski.com) and enhanced by Keith Wood (kbwood@iprimus.com.au).

   Copyright (c) 2007 Marc Grabanski (http://marcgrabanski.com/code/jquery-calendar)
   Dual licensed under the GPL (http://www.gnu.org/licenses/gpl-3.0.txt) and 
   CC (http://creativecommons.org/licenses/by/3.0/) licenses. "Share or Remix it but please Attribute the authors."
   Date: 09-03-2007  */

/* PopUp Calendar manager.
   Use the singleton instance of this class, popUpCal, to interact with the calendar.
   Settings for (groups of) calendars are maintained in an instance object
   (PopUpCalInstance), allowing multiple different settings on the same page. */
function PopUpCal() {
	this._nextId = 0; // Next ID for a calendar instance
	this._inst = []; // List of instances indexed by ID
	this._curInst = null; // The current instance in use
	this._disabledInputs = []; // List of calendar inputs that have been disabled
	this._popUpShowing = false; // True if the popup calendar is showing , false if not
	this._inDialog = false; // True if showing within a "dialog", false if not
	this.regional = []; // Available regional settings, indexed by language code
	this.regional[''] = { // Default regional settings
		clearText: 'Clear', // Display text for clear link
		closeText: 'Close', // Display text for close link
		prevText: '&lt;Prev', // Display text for previous month link
		nextText: 'Next&gt;', // Display text for next month link
		currentText: 'Today', // Display text for current month link
		dayNames: ['Su','Mo','Tu','We','Th','Fr','Sa'], // Names of days starting at Sunday
		monthNames: ['January','February','March','April','May','June',
			'July','August','September','October','November','December'], // Names of months
		dateFormat: 'DMY/' // First three are day, month, year in the required order,
			// fourth (optional) is the separator, e.g. US would be 'MDY/', ISO would be 'YMD-'
	};
	this._defaults = { // Global defaults for all the calendar instances
		autoPopUp: 'focus', // 'focus' for popup on focus,
			// 'button' for trigger button, or 'both' for either
		appendText: '', // Display text following the input box, e.g. showing the format
		buttonText: '...', // Text for trigger button
		buttonImage: '', // URL for trigger button image
		buttonImageOnly: false, // True if the image appears alone, false if it appears on a button
		closeAtTop: true, // True to have the clear/close at the top,
			// false to have them at the bottom
		hideIfNoPrevNext: false, // True to hide next/previous month links
			// if not applicable, false to just disable them
		changeMonth: true, // True if month can be selected directly, false if only prev/next
		changeYear: true, // True if year can be selected directly, false if only prev/next
		yearRange: '-10:+10', // Range of years to display in drop-down,
			// either relative to current year (-nn:+nn) or absolute (nnnn:nnnn)
		firstDay: 0, // The first day of the week, Sun = 0, Mon = 1, ...
		changeFirstDay: true, // True to click on day name to change, false to remain as set
		showOtherMonths: false, // True to show dates in other months, false to leave blank
		minDate: null, // The earliest selectable date, or null for no limit
		maxDate: null, // The latest selectable date, or null for no limit
		speed: 'medium', // Speed of display/closure
		customDate: null, // Function that takes a date and returns an array with
			// [0] = true if selectable, false if not,
			// [1] = custom CSS class name(s) or '', e.g. popUpCal.noWeekends
		fieldSettings: null, // Function that takes an input field and
			// returns a set of custom settings for the calendar
		onSelect: null // Define a callback function when a date is selected
	};
	$.extend(this._defaults, this.regional['']);
	this._calendarDiv = $('<div id="calendar_div"></div>');
	$(document.body).append(this._calendarDiv);
	$(document.body).mousedown(this._checkExternalClick);
}

$.extend(PopUpCal.prototype, {
	/* Register a new calendar instance - with custom settings. */
	_register: function(inst) {
		var id = this._nextId++;
		this._inst[id] = inst;
		return id;
	},

	/* Retrieve a particular calendar instance based on its ID. */
	_getInst: function(id) {
		return this._inst[id] || id;
	},

	/* Override the default settings for all instances of the calendar. 
	   @param  settings  object - the new settings to use as defaults (anonymous object)
	   @return void */
	setDefaults: function(settings) {
		$.extend(this._defaults, settings || {});
	},

	/* Handle keystrokes. */
	_doKeyDown: function(e) {
		var inst = popUpCal._getInst(this._calId);
		if (popUpCal._popUpShowing) {
			switch (e.keyCode) {
				case 9:  popUpCal.hideCalendar(inst, '');
						break; // hide on tab out
				case 13: popUpCal._selectDate(inst);
						break; // select the value on enter
				case 27: popUpCal.hideCalendar(inst, inst._get('speed'));
						break; // hide on escape
				case 33: popUpCal._adjustDate(inst, -1, (e.ctrlKey ? 'Y' : 'M'));
						break; // previous month/year on page up/+ ctrl
				case 34: popUpCal._adjustDate(inst, +1, (e.ctrlKey ? 'Y' : 'M'));
						break; // next month/year on page down/+ ctrl
				case 35: if (e.ctrlKey) popUpCal._clearDate(inst);
						break; // clear on ctrl+end
				case 36: if (e.ctrlKey) popUpCal._gotoToday(inst);
						break; // current on ctrl+home
				case 37: if (e.ctrlKey) popUpCal._adjustDate(inst, -1, 'D');
						break; // -1 day on ctrl+left
				case 38: if (e.ctrlKey) popUpCal._adjustDate(inst, -7, 'D');
						break; // -1 week on ctrl+up
				case 39: if (e.ctrlKey) popUpCal._adjustDate(inst, +1, 'D');
						break; // +1 day on ctrl+right
				case 40: if (e.ctrlKey) popUpCal._adjustDate(inst, +7, 'D');
						break; // +1 week on ctrl+down
			}
		}
		else if (e.keyCode == 36 && e.ctrlKey) { // display the calendar on ctrl+home
			popUpCal.showFor(this);
		}
	},

	/* Filter entered characters. */
	_doKeyPress: function(e) {
		var inst = popUpCal._getInst(this._calId);
		var chr = String.fromCharCode(e.charCode == undefined ? e.keyCode : e.charCode);
		return (chr < ' ' || chr == inst._get('dateFormat').charAt(3) ||
			(chr >= '0' && chr <= '9')); // only allow numbers and separator
	},

	/* Attach the calendar to an input field. */
	_connectCalendar: function(target, inst) {
		var $input = $(target);
		var appendText = inst._get('appendText');
		if (appendText) {
			$input.after('<span class="calendar_append">' + appendText + '</span>');
		}
		var autoPopUp = inst._get('autoPopUp');
		if (autoPopUp == 'focus' || autoPopUp == 'both') { // pop-up calendar when in the marked field
			$input.focus(this.showFor);
		}
		if (autoPopUp == 'button' || autoPopUp == 'both') { // pop-up calendar when button clicked
			var buttonText = inst._get('buttonText');
			var buttonImage = inst._get('buttonImage');
			var buttonImageOnly = inst._get('buttonImageOnly');
			var trigger = $(buttonImageOnly ? '<img class="calendar_trigger" src="' +
				buttonImage + '" alt="' + buttonText + '" title="' + buttonText + '"/>' :
				'<button type="button" class="calendar_trigger">' + (buttonImage != '' ?
				'<img src="' + buttonImage + '" alt="' + buttonText + '" title="' + buttonText + '"/>' :
				buttonText) + '</button>');
			$input.wrap('<span class="calendar_wrap"></span>').after(trigger);
			trigger.click(this.showFor);
		}
		$input.keydown(this._doKeyDown).keypress(this._doKeyPress);
		$input[0]._calId = inst._id;
	},

	/* Attach an inline calendar to a div. */
	_inlineCalendar: function(target, inst, defaultDate) {
		$(target).append(inst._calendarDiv);
		target._calId = inst._id;
		var date = defaultDate;
		inst._selectedDay = date.getDate();
		inst._selectedMonth = date.getMonth();
		inst._selectedYear = date.getFullYear();
		popUpCal._adjustDate(inst);
	},

	/* Pop-up the calendar in a "dialog" box.
	   @param  dateText  string - the initial date to display (in the current format)
	   @param  onSelect  function - the function(dateText) to call when a date is selected
	   @param  settings  object - update the dialog calendar instance's settings (anonymous object)
	   @param  pos       int[2] - coordinates for the dialog's position within the screen
			leave empty for default (screen centre)
	   @return void */
	dialogCalendar: function(dateText, onSelect, settings, pos) {
		var inst = this._dialogInst; // internal instance
		if (!inst) {
			inst = this._dialogInst = new PopUpCalInstance({}, false);
			this._dialogInput = $('<input type="text" size="1" style="position: absolute; top: -100px;"/>');
			this._dialogInput.keydown(this._doKeyDown);
			$('body').append(this._dialogInput);
			this._dialogInput[0]._calId = inst._id;
		}
		$.extend(inst._settings, settings || {});
		this._dialogInput.val(dateText);
		
		/*	Cross Browser Positioning */
		if (self.innerHeight) { // all except Explorer
			windowWidth = self.innerWidth;
			windowHeight = self.innerHeight;
		} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
			windowWidth = document.documentElement.clientWidth;
			windowHeight = document.documentElement.clientHeight;
		} else if (document.body) { // other Explorers
			windowWidth = document.body.clientWidth;
			windowHeight = document.body.clientHeight;
		} 
		this._pos = pos || // should use actual width/height below
			[(windowWidth / 2) - 100, (windowHeight / 2) - 100];

		// move input on screen for focus, but hidden behind dialog
		this._dialogInput.css('left', this._pos[0] + 'px').css('top', this._pos[1] + 'px');
		inst._settings.onSelect = onSelect;
		this._inDialog = true;
		this._calendarDiv.addClass('calendar_dialog');
		this.showFor(this._dialogInput[0]);
		if ($.blockUI) {
			$.blockUI(this._calendarDiv);
		}
	},

	/* Enable the input field(s) for entry.
	   @param  inputs  element/object - single input field or jQuery collection of input fields
	   @return void */
	enableFor: function(inputs) {
		inputs = (inputs.jquery ? inputs : $(inputs));
		inputs.each(function() {
			this.disabled = false;
			$('../button.calendar_trigger', this).each(function() { this.disabled = false; });
			$('../img.calendar_trigger',
this).css({opacity:'1.0',cursor:''});
			var $this = this;
			popUpCal._disabledInputs = $.map(popUpCal._disabledInputs,
				function(value) { return (value == $this ? null : value); }); // delete entry
		});
	},

	/* Disable the input field(s) from entry.
	   @param  inputs  element/object - single input field or jQuery collection of input fields
	   @return void */
	disableFor: function(inputs) {
		inputs = (inputs.jquery ? inputs : $(inputs));
		inputs.each(function() {
			this.disabled = true;
			$('../button.calendar_trigger', this).each(function() { this.disabled = true; });
			$('../img.calendar_trigger', this).css({opacity:'0.5',cursor:'default'});
			var $this = this;
			popUpCal._disabledInputs = $.map(popUpCal._disabledInputs,
				function(value) { return (value == $this ? null : value); }); // delete entry
			popUpCal._disabledInputs[popUpCal._disabledInputs.length] = this;
		});
	},

	/* Update the settings for a calendar attached to an input field or division.
	   @param  control   element - the input field or div/span attached to the calendar
	   @param  settings  object - the new settings to update
	   @return void */
	reconfigureFor: function(control, settings) {
		var inst = this._getInst(control._calId);
		if (inst) {
			$.extend(inst._settings, settings || {});
			this._updateCalendar(inst);
		}
	},

	/* Set the date for a calendar attached to an input field or division.
	   @param  control  element - the input field or div/span attached to the calendar
	   @param  date     Date - the new date
	   @return void */
	setDateFor: function(control, date) {
		var inst = this._getInst(control._calId);
		if (inst) {
			inst._setDate(date);
		}
	},

	/* Retrieve the date for a calendar attached to an input field or division.
	   @param  control  element - the input field or div/span attached to the calendar
	   @return Date - the current date */
	getDateFor: function(control) {
		var inst = this._getInst(control._calId);
		return (inst ? inst._getDate() : null);
	},

	/* Pop-up the calendar for a given input field.
	   @param  target  element - the input field attached to the calendar
	   @return void */
	showFor: function(target) {
		var input = (target.nodeName && target.nodeName.toLowerCase() == 'input' ? target : this);
		if (input.nodeName.toLowerCase() != 'input') { // find from button/image trigger
			input = $('input', input.parentNode)[0];
		}
		if (popUpCal._lastInput == input) { // already here
			return;
		}
		for (var i = 0; i < popUpCal._disabledInputs.length; i++) {  // check not disabled
			if (popUpCal._disabledInputs[i] == input) {
				return;
			}
		}
		var inst = popUpCal._getInst(input._calId);
		popUpCal.hideCalendar(inst, '');
		popUpCal._lastInput = input;
		inst._setDateFromField(input);
		if (popUpCal._inDialog) { // hide cursor
			input.value = '';
		}
		if (!popUpCal._pos) { // position below input
			popUpCal._pos = popUpCal._findPos(input);
			popUpCal._pos[1] += input.offsetHeight;
		}
		inst._calendarDiv.css('position', (popUpCal._inDialog && $.blockUI ? 'static' : 'absolute')).
			css('left', popUpCal._pos[0] + 'px').css('top', popUpCal._pos[1] + 'px');
		popUpCal._pos = null;
		var fieldSettings = inst._get('fieldSettings');
		$.extend(inst._settings, (fieldSettings ? fieldSettings(input) : {}));
		popUpCal._showCalendar(inst);
	},

	/* Construct and display the calendar. */
	_showCalendar: function(id) {
		var inst = this._getInst(id);
		popUpCal._updateCalendar(inst);
		if (!inst._inline) {
			var speed = inst._get('speed');
			inst._calendarDiv.show(speed, function() {
				popUpCal._popUpShowing = true;
				popUpCal._afterShow(inst);
			});
			if (speed == '') {
				popUpCal._popUpShowing = true;
				popUpCal._afterShow(inst);
			}
			if (inst._input[0].type != 'hidden') {
				inst._input[0].focus();
			}
			this._curInst = inst;
		}
	},

	/* Generate the calendar content. */
	_updateCalendar: function(inst) {
		inst._calendarDiv.empty().append(inst._generateCalendar());
		if (inst._input && inst._input != 'hidden') {
			inst._input[0].focus();
		}
	},

	/* Tidy up after displaying the calendar. */
	_afterShow: function(inst) {
		if ($.browser.msie) { // fix IE < 7 select problems
			$('#calendar_cover').css({width: inst._calendarDiv[0].offsetWidth + 4,
				height: inst._calendarDiv[0].offsetHeight + 4});
		}
		/*// re-position on screen if necessary
		var calDiv = inst._calendarDiv[0];
		var pos = popUpCal._findPos(inst._input[0]);
		if ((calDiv.offsetLeft + calDiv.offsetWidth) >
				(document.body.clientWidth + document.body.scrollLeft)) {
			inst._calendarDiv.css('left', (pos[0] + inst._input[0].offsetWidth - calDiv.offsetWidth) + 'px');
		}
		if ((calDiv.offsetTop + calDiv.offsetHeight) >
				(document.body.clientHeight + document.body.scrollTop)) {
			inst._calendarDiv.css('top', (pos[1] - calDiv.offsetHeight) + 'px');
		}*/
	},

	/* Hide the calendar from view.
	   @param  id     string/object - the ID of the current calendar instance,
			or the instance itself
	   @param  speed  string - the speed at which to close the calendar
	   @return void */
	hideCalendar: function(id, speed) {
		var inst = this._getInst(id);
		if (popUpCal._popUpShowing) {
			speed = (speed != null ? speed : inst._get('speed'));
			inst._calendarDiv.hide(speed, function() {
				popUpCal._tidyDialog(inst);
			});
			if (speed == '') {
				popUpCal._tidyDialog(inst);
			}
			popUpCal._popUpShowing = false;
			popUpCal._lastInput = null;
			inst._settings.prompt = null;
			if (popUpCal._inDialog) {
				popUpCal._dialogInput.css('position', 'absolute').
					css('left', '0px').css('top', '-100px');
				if ($.blockUI) {
					$.unblockUI();
					$('body').append(this._calendarDiv);
				}
			}
			popUpCal._inDialog = false;
		}
		popUpCal._curInst = null;
	},

	/* Tidy up after a dialog display. */
	_tidyDialog: function(inst) {
		inst._calendarDiv.removeClass('calendar_dialog');
		$('.calendar_prompt', inst._calendarDiv).remove();
	},

	/* Close calendar if clicked elsewhere. */
	_checkExternalClick: function(event) {
		if (!popUpCal._curInst) {
			return;
		}
		var target = $(event.target);
		if( (target.parents("#calendar_div").length == 0)
			&& (target.attr('class') != 'calendar_trigger')
			&& popUpCal._popUpShowing 
			&& !(popUpCal._inDialog && $.blockUI) )
		{
			popUpCal.hideCalendar(popUpCal._curInst, '');
		}
	},

	/* Adjust one of the date sub-fields. */
	_adjustDate: function(id, offset, period) {
		var inst = this._getInst(id);
		inst._adjustDate(offset, period);
		this._updateCalendar(inst);
	},

	/* Action for current link. */
	_gotoToday: function(id) {
		var date = new Date();
		var inst = this._getInst(id);
		inst._selectedDay = date.getDate();
		inst._selectedMonth = date.getMonth();
		inst._selectedYear = date.getFullYear();
		this._adjustDate(inst);
	},

	/* Action for selecting a new month/year. */
	_selectMonthYear: function(id, select, period) {
		var inst = this._getInst(id);
		inst._selectingMonthYear = false;
		inst[period == 'M' ? '_selectedMonth' : '_selectedYear'] =
			select.options[select.selectedIndex].value - 0;
		this._adjustDate(inst);
	},

	/* Restore input focus after not changing month/year. */
	_clickMonthYear: function(id) {
		var inst = this._getInst(id);
		if (inst._input && inst._selectingMonthYear && !$.browser.msie) {
			inst._input[0].focus();
		}
		inst._selectingMonthYear = !inst._selectingMonthYear;
	},

	/* Action for changing the first week day. */
	_changeFirstDay: function(id, a) {
		var inst = this._getInst(id);
		var dayNames = inst._get('dayNames');
		var value = a.firstChild.nodeValue;
		for (var i = 0; i < 7; i++) {
			if (dayNames[i] == value) {
				inst._settings.firstDay = i;
				break;
			}
		}
		this._updateCalendar(inst);
	},

	/* Action for selecting a day. */
	_selectDay: function(id, td) {
		var inst = this._getInst(id);
		inst._selectedDay = $("a", td).html();
		this._selectDate(id);
	},

	/* Erase the input field and hide the calendar. */
	_clearDate: function(id) {
		this._selectDate(id, '');
	},

	/* Update the input field with the selected date. */
	_selectDate: function(id, dateStr) {
		var inst = this._getInst(id);
		dateStr = (dateStr != null ? dateStr : inst._formatDate());
		if (inst._input) {
			inst._input.val(dateStr);
		}
		var onSelect = inst._get('onSelect');
		if (onSelect) {
			onSelect(dateStr);  // trigger custom callback
		}
		else {
			inst._input.trigger('change'); // fire the change event
		}
		if (inst._inline) {
			this._updateCalendar(inst);
		}
		else {
			this.hideCalendar(inst, inst._get('speed'));
		}
	},

	/* Set as customDate function to prevent selection of weekends.
	   @param  date  Date - the date to customise
	   @return [boolean, string] - is this date selectable?, what is its CSS class? */
	noWeekends: function(date) {
		var day = date.getDay();
		return [(day > 0 && day < 6), ''];
	},

	/* Find an object's position on the screen. */
	_findPos: function(obj) {
		if (obj.type == 'hidden') {
			obj = obj.nextSibling;
		}
		var curleft = curtop = 0;
		if (obj.offsetParent) {
			curleft = obj.offsetLeft;
			curtop = obj.offsetTop;
			while (obj = obj.offsetParent) {
				var origcurleft = curleft;
				curleft += obj.offsetLeft;
				if (curleft < 0) {
					curleft = origcurleft;
				}
				curtop += obj.offsetTop;
			}
		}
		return [curleft,curtop];
	}
});

/* Individualised settings for calendars applied to one or more related inputs.
   Instances are managed and manipulated through the PopUpCal manager. */
function PopUpCalInstance(settings, inline, defaultDate) {
	this._id = popUpCal._register(this);
	this._selectedDay = 0;
	this._selectedMonth = 0; // 0-11
	this._selectedYear = 0; // 4-digit year
	this._input = null; // The attached input field
	this._inline = inline; // True if showing inline, false if used in a popup
	this._calendarDiv = (!inline ? popUpCal._calendarDiv :
		$('<div id="calendar_div_' + this._id + '" class="calendar_inline"></div>'));
	if (inline) {
		var date = defaultDate;
		this._currentDate = defaultDate;
		this._currentDay = date.getDate();
		this._currentMonth = date.getMonth();
		this._currentYear = date.getFullYear();
	}
	// customise the calendar object - uses manager defaults if not overridden
	this._settings = $.extend({}, settings || {}); // clone
}

$.extend(PopUpCalInstance.prototype, {
	/* Get a setting value, defaulting if necessary. */
	_get: function(name) {
		return (this._settings[name] != null ? this._settings[name] : popUpCal._defaults[name]);
	},

	/* Parse existing date and initialise calendar. */
	_setDateFromField: function(input) {
		this._input = $(input);
		var dateFormat = this._get('dateFormat');
		var currentDate = this._input.val().split(dateFormat.charAt(3));
		if (currentDate.length == 3) {
			this._currentDay = parseInt(currentDate[dateFormat.indexOf('D')], 10);
			this._currentMonth = parseInt(currentDate[dateFormat.indexOf('M')], 10) - 1;
			this._currentYear = parseInt(currentDate[dateFormat.indexOf('Y')], 10);
		}
		else {
			var date = new Date();
			this._currentDay = date.getDate();
			this._currentMonth = date.getMonth();
			this._currentYear = date.getFullYear();
		}
		this._selectedDay = this._currentDay;
		this._selectedMonth = this._currentMonth;
		this._selectedYear = this._currentYear;
		this._adjustDate();
	},

	/* Set the date directly. */
	_setDate: function(date) {
		this._selectedDay = this._currentDay = date.getDate();
		this._selectedMonth = this._currentMonth = date.getMonth();
		this._selectedYear = this._currentYear = date.getFullYear();
		this._adjustDate();
	},

	/* Retrieve the date directly. */
	_getDate: function() {
		return new Date(this._currentYear, this._currentMonth, this._currentDay);
	},

	/* Generate the HTML for the current state of the calendar. */
	_generateCalendar: function() {
		var today = this._currentDate;
		today = new Date(today.getFullYear(), today.getMonth(), today.getDate()); // clear time
		// build the calendar HTML
		var controls = '<div class="calendar_control">' +
			'<a class="calendar_clear" onclick="popUpCal._clearDate(' + this._id + ');">' +
			this._get('clearText') + '</a>' +
			'<a class="calendar_close" onclick="popUpCal.hideCalendar(' + this._id + ');">' +
			this._get('closeText') + '</a></div>';
		var prompt = this._get('prompt');
		var closeAtTop = this._get('closeAtTop');
		var hideIfNoPrevNext = this._get('hideIfNoPrevNext');
		// controls and links
		var html = (prompt ? '<div class="calendar_prompt">' + prompt + '</div>' : '') +
			(closeAtTop && !this._inline ? controls : '') + '<div class="calendar_links">' +
			(this._canAdjustMonth(-1) ? '<a class="calendar_prev" ' +
			'onclick="popUpCal._adjustDate(' + this._id + ', -1, \'M\');">' + this._get('prevText') + '</a>' :
			(hideIfNoPrevNext ? '' : '<label class="calendar_prev">' + this._get('prevText') + '</label>')) +
			(this._isInRange(today) ? '<a class="calendar_current" ' +
			'onclick="popUpCal._gotoToday(' + this._id + ');">' + this._get('currentText') + '</a>' : '') +
			(this._canAdjustMonth(+1) ? '<a class="calendar_next" ' +
			'onclick="popUpCal._adjustDate(' + this._id + ', +1, \'M\');">' + this._get('nextText') + '</a>' :
			(hideIfNoPrevNext ? '' : '<label class="calendar_next">' + this._get('nextText') + '</label>')) +
			'</div><div class="calendar_header">';
		var minDate = this._get('minDate');
		var maxDate = this._get('maxDate');
		// month selection
		var monthNames = this._get('monthNames');
		if (!this._get('changeMonth')) {
			html += monthNames[this._selectedMonth] + '&nbsp;';
		}
		else {
			var inMinYear = (minDate && minDate.getFullYear() == this._selectedYear);
			var inMaxYear = (maxDate && maxDate.getFullYear() == this._selectedYear);
			html += '<select class="calendar_newMonth" ' +
				'onchange="popUpCal._selectMonthYear(' + this._id + ', this, \'M\');" ' +
				'onclick="popUpCal._clickMonthYear(' + this._id + ');">';
			for (var month = 0; month < 12; month++) {
				if ((!inMinYear || month >= minDate.getMonth()) &&
						(!inMaxYear || month <= maxDate.getMonth())) {
					html += '<option value="' + month + '"' +
						(month == this._selectedMonth ? ' selected="selected"' : '') +
						'>' + monthNames[month] + '</option>';
				}
			}
			html += '</select>';
		}
		// year selection
		if (!this._get('changeYear')) {
			html += this._selectedYear;
		}
		else {
			// determine range of years to display
			var years = this._get('yearRange').split(':');
			var year = 0;
			var endYear = 0;
			if (years.length != 2) {
				year = this._selectedYear - 10;
				endYear = this._selectedYear + 10;
			}
			else if (years[0].charAt(0) == '+' || years[0].charAt(0) == '-') {
				year = this._selectedYear + parseInt(years[0], 10);
				endYear = this._selectedYear + parseInt(years[1], 10);
			}
			else {
				year = parseInt(years[0], 10);
				endYear = parseInt(years[1], 10);
			}
			year = (minDate ? Math.max(year, minDate.getFullYear()) : year);
			endYear = (maxDate ? Math.min(endYear, maxDate.getFullYear()) : endYear);
			html += '<select class="calendar_newYear" onchange="popUpCal._selectMonthYear(' +
				this._id + ', this, \'Y\');" ' + 'onclick="popUpCal._clickMonthYear(' +
				this._id + ');">';
			for (; year <= endYear; year++) {
				html += '<option value="' + year + '"' +
					(year == this._selectedYear ? ' selected="selected"' : '') +
					'>' + year + '</option>';
			}
			html += '</select>';
		}
		html += '</div><table class="calendar" cellpadding="0" cellspacing="0"><thead>' +
			'<tr class="calendar_titleRow">';
		var firstDay = this._get('firstDay');
		var changeFirstDay = this._get('changeFirstDay');
		var dayNames = this._get('dayNames');
		for (var dow = 0; dow < 7; dow++) { // days of the week
			html += '<td>' + (!changeFirstDay ? '' : '<a onclick="popUpCal._changeFirstDay(' +
				this._id + ', this);">') + dayNames[(dow + firstDay) % 7] +
				(changeFirstDay ? '</a>' : '') + '</td>';
		}
		html += '</tr></thead><tbody>';
		var daysInMonth = this._getDaysInMonth(this._selectedYear, this._selectedMonth);
		this._selectedDay = Math.min(this._selectedDay, daysInMonth);
		var leadDays = (this._getFirstDayOfMonth(this._selectedYear, this._selectedMonth) - firstDay + 7) % 7;
		var currentDate = new Date(this._currentYear, this._currentMonth, this._currentDay);
		var selectedDate = new Date(this._selectedYear, this._selectedMonth, this._selectedDay);
		var printDate = new Date(this._selectedYear, this._selectedMonth, 1 - leadDays);
		var numRows = Math.ceil((leadDays + daysInMonth) / 7); // calculate the number of rows to generate
		var customDate = this._get('customDate');
		var showOtherMonths = this._get('showOtherMonths');
		for (var row = 0; row < numRows; row++) { // create calendar rows
			html += '<tr class="calendar_daysRow">';
			for (var dow = 0; dow < 7; dow++) { // create calendar days
				var customSettings = (customDate ? customDate(printDate) : [true, '']);
				var otherMonth = (printDate.getMonth() != this._selectedMonth);
				var unselectable = otherMonth || !customSettings[0] ||
					(minDate && printDate < minDate) || (maxDate && printDate > maxDate);
				html += '<td class="calendar_daysCell' +
					((dow + firstDay + 6) % 7 >= 5 ? ' calendar_weekEndCell' : '') + // highlight weekends
					(otherMonth ? ' calendar_otherMonth' : '') + // highlight days from other months
					//(printDate.getTime() == selectedDate.getTime() ? ' calendar_daysCellOver' : '') + // highlight selected day
					(unselectable ? ' calendar_unselectable' : '') +  // highlight unselectable days
					(!otherMonth || showOtherMonths ? ' ' + customSettings[1] : '') + // highlight custom dates
					(printDate.getTime() == currentDate.getTime() ? ' calendar_currentDay' : // highlight current day
					(printDate.getTime() == today.getTime() ? ' calendar_today' : '')) + '"' + // highlight today (if different)
					(unselectable ? '' : ' onmouseover="$(this).addClass(\'calendar_daysCellOver\');"' +
					' onmouseout="$(this).removeClass(\'calendar_daysCellOver\');"' +
					' onclick="popUpCal._selectDay(' + this._id + ', this);"') + '>' + // actions
					(otherMonth ? (showOtherMonths ? printDate.getDate() : '&nbsp;') : // display for other months
					(unselectable ? printDate.getDate() : '<a>' + printDate.getDate() + '</a>')) + '</td>'; // display for this month
				printDate.setDate(printDate.getDate() + 1);
			}
			html += '</tr>';
		}
		html += '</tbody></table>' + (!closeAtTop && !this._inline ? controls : '') +
			'<div style="clear: both;"></div>' + (!$.browser.msie ? '' :
			'<!--[if lte IE 6.5]><iframe src="javascript:false;" class="calendar_cover"></iframe><![endif]-->');
		return html;
	},

	/* Adjust one of the date sub-fields. */
	_adjustDate: function(offset, period) {
		var date = new Date(this._selectedYear + (period == 'Y' ? offset : 0),
			this._selectedMonth + (period == 'M' ? offset : 0),
			this._selectedDay + (period == 'D' ? offset : 0));
		// ensure it is within the bounds set
		var minDate = this._get('minDate');
		var maxDate = this._get('maxDate');
		date = (minDate && date < minDate ? minDate : date);
		date = (maxDate && date > maxDate ? maxDate : date);
		this._selectedDay = date.getDate();
		this._selectedMonth = date.getMonth();
		this._selectedYear = date.getFullYear();
	},

	/* Find the number of days in a given month. */
	_getDaysInMonth: function(year, month) {
		return 32 - new Date(year, month, 32).getDate();
	},

	/* Find the day of the week of the first of a month. */
	_getFirstDayOfMonth: function(year, month) {
		return new Date(year, month, 1).getDay();
	},

	/* Determines if we should allow a "next/prev" month display change. */
	_canAdjustMonth: function(offset) {
		var date = new Date(this._selectedYear, this._selectedMonth + offset, 1);
		if (offset < 0) {
			date.setDate(this._getDaysInMonth(date.getFullYear(), date.getMonth()));
		}
		return this._isInRange(date);
	},

	/* Is the given date in the accepted range? */
	_isInRange: function(date) {
		var minDate = this._get('minDate');
		var maxDate = this._get('maxDate');
		return ((!minDate || date >= minDate) && (!maxDate || date <= maxDate));
	},

	/* Format the given date for display. */
	_formatDate: function() {
		var day = this._currentDay = this._selectedDay;
		var month = this._currentMonth = this._selectedMonth;
		var year = this._currentYear = this._selectedYear;
		month++; // adjust javascript month
		var dateFormat = this._get('dateFormat');
		var dateString = '';
		for (var i = 0; i < 3; i++) {
			dateString += dateFormat.charAt(3) +
				(dateFormat.charAt(i) == 'D' ? (day < 10 ? '0' : '') + day :
				(dateFormat.charAt(i) == 'M' ? (month < 10 ? '0' : '') + month :
				(dateFormat.charAt(i) == 'Y' ? year : '?')));
		}
		return dateString.substring(dateFormat.charAt(3) ? 1 : 0);
	}
});

/* Attach the calendar to a jQuery selection.
   @param  settings  object - the new settings to use for this calendar instance (anonymous)
   @return jQuery object - for chaining further calls */
$.fn.calendar = function(settings, defaultDate) {
	return this.each(function() {
		// check for settings on the control itself - in namespace 'cal:'
		var inlineSettings = null;
		for (attrName in popUpCal._defaults) {
			var attrValue = this.getAttribute('cal:' + attrName);
			if (attrValue) {
				inlineSettings = inlineSettings || {};
				try {
					inlineSettings[attrName] = eval(attrValue);
				}
				catch (err) {
					inlineSettings[attrName] = attrValue;
				}
			}
		}
		var nodeName = this.nodeName.toLowerCase();
		if (nodeName == 'input') {
			var instSettings = (inlineSettings ? $.extend($.extend({}, settings || {}),
				inlineSettings || {}) : settings); // clone and customise
			var inst = (inst && !inlineSettings ? inst :
				new PopUpCalInstance(instSettings, false, defaultDate));
			popUpCal._connectCalendar(this, inst);
		} 
		else if (nodeName == 'div' || nodeName == 'span') {
			var instSettings = $.extend($.extend({}, settings || {}),
				inlineSettings || {}); // clone and customise
			var inst = new PopUpCalInstance(instSettings, true, defaultDate);
			popUpCal._inlineCalendar(this, inst, defaultDate);
		}
	});
};

/* Initialise the calendar. */
$(document).ready(function() {
   popUpCal = new PopUpCal(); // singleton instance
});
