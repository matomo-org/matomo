/**
 * DataTable RowActions
 * 
 * The lifecycle of an action is as follows:
 * - for each data table, a new instance of the action is created using the factory
 * - when the table is loaded, initTr is called for each tr
 * - when the action icon is clicked, onActionClick is called
 */


/**
 * Factory function for creating actions instances dynamically.
 * It's designed to decouple the row actions from the data table code.
 * Also, custom actions can be added more easily this way.
 */
function DataTable_RowActions_Factory(actionName, dataTable) {
	var className = 'DataTable_RowActions_' + actionName;
	eval('if (typeof '+className+' == "undefined") alert("Invalid action: '+className+'");'+
			'var instance = new '+className+'(dataTable)');
	return instance;
}


//
// ROW EVOLUTION
//

function DataTable_RowActions_RowEvolution(dataTable) {
	this.dataTable = dataTable;
	this.multiEvolutionRows = [];
}

DataTable_RowActions_RowEvolution.prototype = {
	
	/** The separator for putting together recursive labels */
	recursiveLabelSeparator: '->>-',
	
	/** Initialize the action for a row when the table is loaded */
	initTr: function(tr) {
		var self = this;
		// callback to show row evolution for sub tables
		tr.bind('rowEvolutionOfSubTable', function(e, params) {
			self.showRowEvolution($(this), params.label, null, params.onlyMarkForMulti);
		});
	},
	
	/** The user clicked the row evolution icon on a datatable row */
	onActionClick: function(tr, e) {
		this.showRowEvolution(tr, null, null, e.shiftKey);
	},
	
	/** Open the row evolution popup */
	showRowEvolution: function(tr, subTableLabel, metric, onlyMarkForMulti) {
		var self = this;
		
		var getLabel = function(tr) {
			var label = tr.find('span.label');
			var value = label.data('originalText'); // handle truncation
			if (!value) {
				value = label.text();
			}
			return value;
		};
		
		var label = getLabel(tr);
		
		// put together recursive label
		if (tr.attr('parent')) {
			var parent = $.trim(tr.attr('parent'));
			var parents = parent.split(' ');
			var labels = [];
			for (var i = 0; i < parents.length; i++) {
				var el = $('#' + parseInt(parents[i], 10));
				var plabel = getLabel(el);
				if (!plabel) {
					break;
				}
				labels.push(plabel);
			}
			labels.push(label);
			label = labels.join(this.recursiveLabelSeparator);
		}
		
		// handle sub tables in nested reports:
		// sub tables have different api methods (e.g. search engines for keywords)
		// => pass the label to the appropriate row of the parent table
		// the request is made by the parent, since it knows the right api method to call
		var subtable = tr.closest('table');
		if (subtable.is('.subDataTable')) {
			subtable.closest('tr').prev().trigger('rowEvolutionOfSubTable', {
				label: label,
				onlyMarkForMulti: onlyMarkForMulti
			});
			return;
		}
		
		// if we have received the event from the sub table, add the label
		if (subTableLabel) {
			label += this.recursiveLabelSeparator + subTableLabel;
		}
		
		// handle sub tables in action reports
		if (tr.hasClass('actionsDataTable') || tr.hasClass('subActionsDataTable')) {
			var allClasses = tr.attr('class');
			var matches = allClasses.match(/level[0-9]+/);
			var level = parseInt(matches[0].substring(5, matches[0].length), 10);
			if (level > 0) {
				// .prev(.levelX) does not work for some reason => do it "by hand"
				var findLevel = 'level' + (level-1);
				var ptr = tr;
				while ((ptr = ptr.prev()).size() > 0) {
					if (!ptr.hasClass(findLevel)) {
						continue;
					}
					ptr.trigger('rowEvolutionOfSubTable', {
						label: label,
						onlyMarkForMulti: onlyMarkForMulti
					});
					return;
				}
			}
		}
		
		// this happens when shift-clicking a row
		if (onlyMarkForMulti) {
			self.multiEvolutionRows.push(label);
			return;
		}
		
		// open the popup
		var icon = tr.find('td:first > img');
		var title = subTableLabel || tr.find('td:first span.label').clone();
		if (icon.size() > 0) {
			title = $(document.createElement('span'))
				.append(icon.clone())
				.append('&nbsp;')
				.append(title);
		}
		
		var loading = $('div.loadingPiwik:first').clone();
		var box = $(document.createElement('div')).addClass('rowEvolutionPopup').html(loading);
		box.dialog({
			title: title,
			modal: true,
			width: '900px',
			position: ['center', 'center'],
			resizable: false,
			autoOpen: true,
			close: function(event, ui) {
				// reset multi evolution if regular close button has been used
				if (typeof event.originalEvent != 'undefined') {
					self.multiEvolutionRows = [];
				}
				box.find('div.jqplot-target').trigger('piwikDestroyPlot');
				box.dialog('destroy').remove();
			}
		});
		
		// load the popup contents
		var request = this.dataTable.buildAjaxRequest(function(html) {
			box.html(html);
			box.dialog({position: ['center', 'center']});
			
			var title = box.find('div.popup-title');
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
				self.showRowEvolution(tr, subTableLabel, metric);
				return true;
			});
		});
		
		var requestLabel = label;
		var action = 'getRowEvolutionPopup';
		
		if (self.multiEvolutionRows.length > 0) {
			if ($.inArray(label, self.multiEvolutionRows) == -1) {
				self.multiEvolutionRows.push(label);
			}
			if (self.multiEvolutionRows.length > 1) {
				box.dialog({title: ''});
				action = 'getMultiRowEvolutionPopup';
				requestLabel = self.multiEvolutionRows.join('<+MultiRow+>');
			}
		}
		
		request.data = {
			apiMethod: request.data.module+'.'+request.data.action,
			module: 'CoreHome',
			action: action,
			date: request.data.date,
			idSite: request.data.idSite,
			period: request.data.period,
			label: requestLabel
		};
		
		if (metric) {
			request.data.metric = metric;
		}
		
		piwikHelper.queueAjaxRequest($.ajax(request));
	}
	
};