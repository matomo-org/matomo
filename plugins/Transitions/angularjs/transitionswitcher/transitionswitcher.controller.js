/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    angular.module('piwikApp').controller('TransitionSwitcherController', TransitionSwitcherController);

    TransitionSwitcherController.$inject = ['piwikApi', '$filter', '$rootScope'];

    function TransitionSwitcherController(piwikApi, $filter, $rootScope) {
        var translate = $filter('translate');

        var self = this;
        this.actionType = 'Actions.getPageUrls';
        this.actionNameOptions = [];
        this.actionTypeOptions = [
            {key: 'Actions.getPageUrls', value: translate('Actions_PageUrls')},
            {key: 'Actions.getPageTitles', value: translate('Actions_WidgetPageTitles')}
        ];
        this.isLoading = false;
        this.transitions = null;
        this.actionName = null;
        this.isEnabled = true;
        var noDataKey = '_____ignore_____';

        this.detectActionName = function (reports)
        {
            var othersLabel = translate('General_Others');

            var label, report;
            for (var i = 0; i < reports.length; i++) {
                if (!reports[i]) {
                    continue;
                }

                report = reports[i];

                if (report.label === othersLabel) {
                    continue;
                }

                var key = null;
                if (self.isUrlReport()) {
                    key = report.url
                } else {
                    key = report.label;
                }

                if (key) {
                    label = report.label + ' (' + translate('Transitions_NumPageviews', report.nb_hits) + ')';
                    self.actionNameOptions.push({key: key, value: label, url: report.url});
                    if (!self.actionName) {
                        self.actionName = key
                    }
                }
            }
        }

        this.isUrlReport = function()
        {
            return this.actionType === 'Actions.getPageUrls';
        }

        this.fetch = function (type) {
            this.isLoading = true;
            this.actionNameOptions = [];
            this.actionName = null;

            piwikApi.fetch({
                method: type,
                flat: 1, filter_limit: 100,
                filter_sort_order: 'desc',
                filter_sort_column: 'nb_hits',
                showColumns: 'label,nb_hits,url'
            }).then(function (report) {
                self.isLoading = false;
                self.actionNameOptions = [];
                self.actionName = null;

                if (report && report.length) {
                    self.isEnabled = true;
                    self.detectActionName(report);
                    self.onActionNameChange(self.actionName);
                }

                if (null === self.actionName || self.actionNameOptions.length === 0) {
                    self.isEnabled = false;
                    self.actionName = noDataKey;
                    self.actionNameOptions.push({key: noDataKey, value: translate('CoreHome_ThereIsNoDataForThisReport')});
                }
            }, function () {
                self.isLoading = false;
                self.isEnabled = false;
            });
        }

        this.onActionTypeChange = function (actionName) {
            this.fetch(actionName);
        };

        this.onActionNameChange = function (actionName) {
            if (actionName === null || actionName === noDataKey) {
                return;
            }

            var type = 'url';
            if (!this.isUrlReport()) {
                type = 'title';
            }
            if (!this.transitions) {
                this.transitions = new Piwik_Transitions(type, actionName, null, '');
            } else {
                this.transitions.reset(type, actionName, '');
            }
            this.transitions.showPopover(true);
        };

        $rootScope.$on('Transitions.switchTransitionsUrl', function (event, params) {
            if (params && params.url) {
                if (self.isUrlReport()) {
                    params.url = params.url.replace('https://', '').replace('http://', '');
                }

                var found = false, option, optionUrl;
                for (var i = 0; i < self.actionNameOptions.length; i++) {
                    option = self.actionNameOptions[i];
                    optionUrl = option.url;
                    if (optionUrl && self.isUrlReport()) {
                        optionUrl = String(optionUrl).replace('https://', '').replace('http://', '');
                    } else {
                        optionUrl = null;
                    }

                    if (!found && (option.key === params.url || (params.url === optionUrl && optionUrl))) {
                        found = true;
                        self.actionName = option.key;
                    }
                }
                if (!found) {
                    // we only fetch top 100 in the report... so the entry the user clicked on, might not be in the top 100
                    var options = angular.copy(self.actionNameOptions); // somehow needed to force angular to render it
                    options.push({key: params.url, value: params.url});
                    self.actionNameOptions = options;
                    self.actionName = params.url;
                }
                self.onActionNameChange(self.actionName);
            }
        });

        this.fetch(this.actionType);
    }
})();