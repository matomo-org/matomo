/**
 * DataTable RowActions
 *
 * The lifecycle of an action is as follows:
 * - for each data table, a new instance of the action is created using the factory
 * - when the table is loaded, initTr is called for each tr
 * - when the action icon is clicked, trigger is called
 * - the label is put together and performAction is called
 * - performAction must call openPopover on the base class
 * - openPopover calls back doOpenPopover after doing general stuff
 *
 * The two template methods are performAction and doOpenPopover
 */


/**
 * Factory function for creating action instances dynamically.
 * It's designed to decouple the row actions from the data table code.
 * Also, custom actions can be added more easily this way.
 */
function DataTable_RowActions_Factory(actionName, dataTable) {
	var className = 'DataTable_RowActions_' + actionName;
	eval('if (typeof ' + className + ' == "undefined") alert("Invalid action: ' + className + '");' +
		'var instance = new ' + className + '(dataTable)');
	return instance;
}


//
// BASE CLASS
//

function DataTable_RowAction(dataTable) {
	this.dataTable = dataTable;
}

/** Initialize a row when the table is loaded */
DataTable_RowAction.prototype.initTr = function(tr) {
	var self = this;

	// For subtables, we need to make sure that the actions are always triggered on the
	// action instance connected to the root table. Otherwise sharing data (e.g. for
	// for multi-row evolution) wouldn't be possible. Also, sub-tables might have different
	// API actions. For the label filter to work, we need to use the parent action.
	// We use jQuery events to let subtables access their parents.
	tr.bind('piwikTriggerRowAction', function(e, params) {
		self.trigger($(this), params.originalEvent, params.label);
	});
};

/**
 * This method is called from the click event and the piwikTriggerRowAction event.
 * It derives the label and calls performAction.
 */
DataTable_RowAction.prototype.trigger = function(tr, e, subTableLabel) {
	var label = this.getLabelFromTr(tr);

	// if we have received the event from the sub table, add the label
	if (subTableLabel) {
		label += '>' + subTableLabel;
	}

	// handle sub tables in nested reports: forward to parent
	var subtable = tr.closest('table');
	if (subtable.is('.subDataTable')) {
		subtable.closest('tr').prev().trigger('piwikTriggerRowAction', {
			label: label,
			originalEvent: e
		});
		return;
	}

	// ascend in action reports
	if (tr.hasClass('actionsDataTable') || tr.hasClass('subActionsDataTable')) {
		var allClasses = tr.attr('class');
		var matches = allClasses.match(/level[0-9]+/);
		var level = parseInt(matches[0].substring(5, matches[0].length), 10);
		if (level > 0) {
			// .prev(.levelX) does not work for some reason => do it "by hand"
			var findLevel = 'level' + (level - 1);
			var ptr = tr;
			while ((ptr = ptr.prev()).size() > 0) {
				if (!ptr.hasClass(findLevel)) {
					continue;
				}
				ptr.trigger('piwikTriggerRowAction', {
					label: label,
					originalEvent: e
				});
				return;
			}
		}
	}

	this.performAction(label, tr, e);
};

/** Get the label string from a tr dom element */
DataTable_RowAction.prototype.getLabelFromTr = function(tr) {
	var label = tr.find('span.label');

	// handle truncation
	var value = label.data('originalText');

	if (!value) {
		value = label.text();
	}

	return encodeURIComponent(value);
};

/**
 * Base method for opening popovers.
 * TODO: In the future, this method will remember the parameter in the url.
 * After doing general things, doOpenPopover is called.
 */
DataTable_RowAction.prototype.openPopover = function(parameter) {
	// maybe add popover name / param after a second hash?
	//var currentHashStr = broadcast.getHashFromUrl().replace(/^#/, '');
	//currentHashStr = broadcast.updateParamValue('foo=bar', currentHashStr);
	//$.history.load(currentHashStr);

	this.doOpenPopover(parameter);
};

/** To be overridden */
DataTable_RowAction.prototype.performAction = function(label, tr, e) {
};
DataTable_RowAction.prototype.doOpenPopover = function(parameter) {
};


//
// ROW EVOLUTION
//
// TODO: use openPopover of base class. when opening a row evolution popover from
// the url, omit "compare records" because we don't know which data table to use.
//

function DataTable_RowActions_RowEvolution(dataTable) {
	this.dataTable = dataTable;

	/** The rows to be compared in multi row evolution */
	this.multiEvolutionRows = [];
}

DataTable_RowActions_RowEvolution.prototype = new DataTable_RowAction;

DataTable_RowActions_RowEvolution.prototype.performAction = function(label, tr, e) {
	this.showRowEvolution(tr, label, null, e.shiftKey);
};

/** Open the row evolution popover */
DataTable_RowActions_RowEvolution.prototype.showRowEvolution = function(tr, label, metric, onlyMarkForMulti) {
	var self = this;

	// this happens when shift-clicking a row
	if (onlyMarkForMulti) {
		self.multiEvolutionRows.push(label);
		return;
	}

	// open the popover
	var loading = $('div.loadingPiwik:first').clone();
	var box = $(document.createElement('div')).addClass('rowEvolutionPopover').html(loading);
	box.dialog({
		title: '',
		modal: true,
		width: '900px',
		position: ['center', 'center'],
		resizable: false,
		autoOpen: true,
		open: function(event, ui) {
			$('.ui-widget-overlay').on('click.rowEvolution',function(){
				$('.rowEvolutionPopover').dialog('close');
			})
		},
		close: function(event, ui) {
			// reset multi evolution if regular close button has been used
			if (typeof event.originalEvent != 'undefined') {
				self.multiEvolutionRows = [];
			}
			piwikHelper.abortQueueAjax();
			$('.ui-widget-overlay').off('click.rowEvolution');
			box.find('div.jqplot-target').trigger('piwikDestroyPlot');
			box.dialog('destroy').remove();
		}
	});

	// load the popover contents
	var request = this.dataTable.buildAjaxRequest(function(html) {
		box.html(html);
		box.dialog({position: ['center', 'center']});

		var title = box.find('div.popover-title');
		if (title.size() > 0) {
			box.dialog({title: title.html()});
			title.remove();
		}

		// remember label for multi row evolution
		box.find('a.rowevolution-startmulti').click(function() {
			box.dialog('close');
			if ($.inArray(label, self.multiEvolutionRows) == -1) {
				self.multiEvolutionRows.push(label);
			}
			return false;
		});

		// switch metric in multi row evolution
		box.find('select.multirowevoltion-metric').change(function() {
			var metric = $(this).val();
			box.dialog('close');
			self.showRowEvolution(tr, label, metric);
			return true;
		});
	});

	var requestLabel = label;
	var action = 'getRowEvolutionPopover';

	if (self.multiEvolutionRows.length > 0) {
		if ($.inArray(label, self.multiEvolutionRows) == -1) {
			self.multiEvolutionRows.push(label);
		}
		if (self.multiEvolutionRows.length > 1) {
			box.dialog({title: ''});
			action = 'getMultiRowEvolutionPopover';
			requestLabel = self.multiEvolutionRows.join(',');
		}
	}

	request.data = {
		apiMethod: request.data.module + '.' + request.data.action,
		module: 'CoreHome',
		action: action,
		date: request.data.date,
		idSite: request.data.idSite,
		period: request.data.period,
		label: requestLabel,
		segment: request.data.segment,
		disableLink: 1
	};

	if (metric) {
		request.data.column = metric;
	}
	var token_auth = broadcast.getValueFromUrl('token_auth');
	if (token_auth.length && token_auth != 'anonymous') {
		request.data.token_auth = token_auth;
	}

	piwikHelper.queueAjaxRequest($.ajax(request));
};