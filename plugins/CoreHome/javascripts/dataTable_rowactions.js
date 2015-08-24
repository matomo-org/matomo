/**
 * Registry for row actions
 *
 * Plugins can call DataTable_RowActions_Registry.register() from their JS
 * files in order to add new actions to arbitrary data tables. The register()
 * method takes an object containing:
 * - name: string identifying the action. must be short, no spaces.
 * - dataTableIcon: path to the icon for the action
 * - createInstance: a factory method to create an instance of the appropriate
 *                   subclass of DataTable_RowAction
 * - isAvailable: a method to determine whether the action is available in a
 *                given row of a data table
 */
var DataTable_RowActions_Registry = {

    registry: [],

    register: function (action) {
        var createInstance = action.createInstance;
        action.createInstance = function (dataTable, param) {
            var instance = createInstance(dataTable, param);
            instance.actionName = action.name;
            return instance;
        };

        this.registry.push(action);
    },

    getAvailableActionsForReport: function (dataTableParams, tr) {
        if (dataTableParams.disable_row_actions == '1') {
            return [];
        }

        var available = [];
        for (var i = 0; i < this.registry.length; i++) {
            if (this.registry[i].isAvailableOnReport(dataTableParams, tr)) {
                available.push(this.registry[i]);
            }
        }
        available.sort(function (a, b) {
            return b.order - a.order;
        });
        return available;
    },

    getActionByName: function (name) {
        for (var i = 0; i < this.registry.length; i++) {
            if (this.registry[i].name == name) {
                return this.registry[i];
            }
        }
        return false;
    }

};

// Register Row Evolution (also servers as example)
DataTable_RowActions_Registry.register({

    name: 'RowEvolution',

    dataTableIcon: 'plugins/Morpheus/images/row_evolution.png',
    dataTableIconHover: 'plugins/Morpheus/images/row_evolution_hover.png',

    order: 50,

    dataTableIconTooltip: [
        _pk_translate('General_RowEvolutionRowActionTooltipTitle'),
        _pk_translate('General_RowEvolutionRowActionTooltip')
    ],

    createInstance: function (dataTable, param) {
        if (dataTable !== null && typeof dataTable.rowEvolutionActionInstance != 'undefined') {
            return dataTable.rowEvolutionActionInstance;
        }

        if (dataTable === null && param) {
            // when row evolution is triggered from the url (not a click on the data table)
            // we look for the data table instance in the dom
            var report = param.split(':')[0];
            var div = $(require('piwik/UI').DataTable.getDataTableByReport(report));
            if (div.size() > 0 && div.data('uiControlObject')) {
                dataTable = div.data('uiControlObject');
                if (typeof dataTable.rowEvolutionActionInstance != 'undefined') {
                    return dataTable.rowEvolutionActionInstance;
                }
            }
        }

        var instance = new DataTable_RowActions_RowEvolution(dataTable);
        if (dataTable !== null) {
            dataTable.rowEvolutionActionInstance = instance;
        }
        return instance;
    },

    isAvailableOnReport: function (dataTableParams) {
        return (
            typeof dataTableParams.disable_row_evolution == 'undefined'
                || dataTableParams.disable_row_evolution == "0"
            ) && (
            typeof dataTableParams.flat == 'undefined'
                || dataTableParams.flat == "0"
            );
    },

    isAvailableOnRow: function (dataTableParams, tr) {
        return true;
    }

});

/**
 * DataTable Row Actions
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

//
// BASE CLASS
//

function DataTable_RowAction(dataTable) {
    this.dataTable = dataTable;

    // has to be overridden in subclasses
    this.trEventName = 'piwikTriggerRowAction';

    // set in registry
    this.actionName = 'RowAction';
}

/** Initialize a row when the table is loaded */
DataTable_RowAction.prototype.initTr = function (tr) {
    var self = this;

    // For subtables, we need to make sure that the actions are always triggered on the
    // action instance connected to the root table. Otherwise sharing data (e.g. for
    // for multi-row evolution) wouldn't be possible. Also, sub-tables might have different
    // API actions. For the label filter to work, we need to use the parent action.
    // We use jQuery events to let subtables access their parents.
    tr.bind(self.trEventName, function (e, params) {
        self.trigger($(this), params.originalEvent, params.label);
    });
};

/**
 * This method is called from the click event and the tr event (see this.trEventName).
 * It derives the label and calls performAction.
 */
DataTable_RowAction.prototype.trigger = function (tr, e, subTableLabel) {
    var label = this.getLabelFromTr(tr);

    // if we have received the event from the sub table, add the label
    if (subTableLabel) {
        var separator = ' > '; // LabelFilter::SEPARATOR_RECURSIVE_LABEL
        label += separator + subTableLabel;
    }

    // handle sub tables in nested reports: forward to parent
    var subtable = tr.closest('table');
    if (subtable.is('.subDataTable')) {
        subtable.closest('tr').prev().trigger(this.trEventName, {
            label: label,
            originalEvent: e
        });
        return;
    }

    // ascend in action reports
    if (subtable.closest('div.dataTableActions').length) {
        var allClasses = tr.attr('class');
        var matches = allClasses.match(/level[0-9]+/);
        var level = parseInt(matches[0].substring(5, matches[0].length), 10);
        if (level > 0) {
            // .prev(.levelX) does not work for some reason => do it "by hand"
            var findLevel = 'level' + (level - 1);
            var ptr = tr;
            while ((ptr = ptr.prev()).size() > 0) {
                if (!ptr.hasClass(findLevel) || ptr.hasClass('nodata')) {
                    continue;
                }
                ptr.trigger(this.trEventName, {
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
DataTable_RowAction.prototype.getLabelFromTr = function (tr) {
    var label = tr.find('span.label');

    // handle truncation
    var value = label.data('originalText');

    if (!value) {
        value = label.text();
    }
    value = value.trim();
    value = encodeURIComponent(value);

    // if tr is a terminal node, we use the @ operator to distinguish it from branch nodes w/ the same name
    if (!tr.hasClass('subDataTable')) {
        value = '@' + value;
    }

    return value;
};

/**
 * Base method for opening popovers.
 * This method will remember the parameter in the url and call doOpenPopover().
 */
DataTable_RowAction.prototype.openPopover = function (parameter) {
    broadcast.propagateNewPopoverParameter('RowAction', this.actionName + ':' + parameter);
};

broadcast.addPopoverHandler('RowAction', function (param) {
    var paramParts = param.split(':');
    var rowActionName = paramParts[0];
    paramParts.shift();
    param = paramParts.join(':');

    var rowAction = DataTable_RowActions_Registry.getActionByName(rowActionName);
    if (rowAction) {
        rowAction.createInstance(null, param).doOpenPopover(param);
    }
});

/** To be overridden */
DataTable_RowAction.prototype.performAction = function (label, tr, e) {
};
DataTable_RowAction.prototype.doOpenPopover = function (parameter) {
};

//
// ROW EVOLUTION
//

function DataTable_RowActions_RowEvolution(dataTable) {
    this.dataTable = dataTable;
    this.trEventName = 'piwikTriggerRowEvolution';

    /** The rows to be compared in multi row evolution */
    this.multiEvolutionRows = [];
}

/** Static helper method to launch row evolution from anywhere */
DataTable_RowActions_RowEvolution.launch = function (apiMethod, label) {
    var param = 'RowEvolution:' + apiMethod + ':0:' + label;
    broadcast.propagateNewPopoverParameter('RowAction', param);
};

DataTable_RowActions_RowEvolution.prototype = new DataTable_RowAction;

DataTable_RowActions_RowEvolution.prototype.performAction = function (label, tr, e) {
    if (e.shiftKey) {
        // only mark for multi row evolution if shift key is pressed
        this.addMultiEvolutionRow(label);
        return;
    }

    this.addMultiEvolutionRow(label);

    // check whether we have rows marked for multi row evolution
    var extraParams = {};
    if (this.multiEvolutionRows.length > 1) {
        extraParams.action = 'getMultiRowEvolutionPopover';
        label = this.multiEvolutionRows.join(',');
    }

    // check if abandonedCarts is in the dataTable params and if so, propagate to row evolution request
    if (this.dataTable.param.abandonedCarts !== undefined) {
        extraParams['abandonedCarts'] = this.dataTable.param.abandonedCarts;
    }

    var apiMethod = this.dataTable.param.module + '.' + this.dataTable.param.action;
    this.openPopover(apiMethod, extraParams, label);
};

DataTable_RowActions_RowEvolution.prototype.addMultiEvolutionRow = function (label) {
    if ($.inArray(label, this.multiEvolutionRows) == -1) {
        this.multiEvolutionRows.push(label);
    }
};

DataTable_RowActions_RowEvolution.prototype.openPopover = function (apiMethod, extraParams, label) {
    var urlParam = apiMethod + ':' + encodeURIComponent(JSON.stringify(extraParams)) + ':' + label;
    DataTable_RowAction.prototype.openPopover.apply(this, [urlParam]);
};

DataTable_RowActions_RowEvolution.prototype.doOpenPopover = function (urlParam) {
    var urlParamParts = urlParam.split(':');

    var apiMethod = urlParamParts.shift();

    var extraParamsString = urlParamParts.shift(),
        extraParams = {}; // 0/1 or "0"/"1"
    try {
        extraParams = JSON.parse(decodeURIComponent(extraParamsString));
    } catch (e) {
        // assume the parameter is an int/string describing whether to use multi row evolution
        if (extraParamsString == '1') {
            extraParams.action = 'getMultiRowEvolutionPopover';
        } else if (extraParamsString != '0') {
            extraParams.action = 'getMultiRowEvolutionPopover';
            extraParams.column = extraParamsString;
        }
    }

    var label = urlParamParts.join(':');

    this.showRowEvolution(apiMethod, label, extraParams);
};

/** Open the row evolution popover */
DataTable_RowActions_RowEvolution.prototype.showRowEvolution = function (apiMethod, label, extraParams) {

    var self = this;

    // open the popover
    var box = Piwik_Popover.showLoading('Row Evolution');
    box.addClass('rowEvolutionPopover');

    // prepare loading the popover contents
    var requestParams = {
        apiMethod: apiMethod,
        label: label,
        disableLink: 1
    };

    var callback = function (html) {
        Piwik_Popover.setContent(html);

        // use the popover title returned from the server
        var title = box.find('div.popover-title');
        if (title.size() > 0) {
            Piwik_Popover.setTitle(title.html());
            title.remove();
        }

        Piwik_Popover.onClose(function () {
            // reset rows marked for multi row evolution on close
            self.multiEvolutionRows = [];
        });

        if (self.dataTable !== null) {
            // remember label for multi row evolution
            box.find('.rowevolution-startmulti').click(function () {
                Piwik_Popover.onClose(false); // unbind listener that resets multiEvolutionRows
                Piwik_Popover.close();
                return false;
            });
        } else {
            // when the popover is launched by copy&pasting a url, we don't have the data table.
            // in this case, we can't remember the row marked for multi row evolution so
            // we disable the picker.
            box.find('.compare-container, .rowevolution-startmulti').remove();
        }

        // switch metric in multi row evolution
        box.find('select.multirowevoltion-metric').change(function () {
            var metric = $(this).val();
            Piwik_Popover.onClose(false); // unbind listener that resets multiEvolutionRows
            var extraParams = {action: 'getMultiRowEvolutionPopover', column: metric};
            self.openPopover(apiMethod, extraParams, label);
            return true;
        });
    };

    requestParams.module = 'CoreHome';
    requestParams.action = 'getRowEvolutionPopover';
    requestParams.colors = JSON.stringify(piwik.getSparklineColors());

    $.extend(requestParams, extraParams);

    var ajaxRequest = new ajaxHelper();
    ajaxRequest.addParams(requestParams, 'get');
    ajaxRequest.setCallback(callback);
    ajaxRequest.setFormat('html');
    ajaxRequest.send(false);
};
