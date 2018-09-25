/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    angular.module('piwikApp').controller('TransitionSwitcherController', TransitionSwitcherController);

    TransitionSwitcherController.$inject = ['piwikApi', '$filter'];

    function TransitionSwitcherController(piwikApi, $filter) {
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
        this.actionName = '';
        this.isEnabled = true;

        this.isUrlReport = function()
        {
            return this.actionType === 'Actions.getPageUrls';
        }

        this.fetch = function (type) {
            this.isLoading = true;
            this.actionNameOptions = [];

            piwikApi.fetch({
                method: type,
                flat: 1, filter_limit: 100,
                filter_sort_order: 'desc',
                filter_sort_column: 'nb_hits',
                showColumns: 'label,nb_hits,url'
            }).then(function (report) {
                self.isLoading = false;
                self.actionNameOptions = [];
                self.actionName = '';
                if (report && report.length) {
                    self.isEnabled = true;
                    var othersLabel = translate('General_Others');

                    var label;
                    for (var i = 0; i < report.length; i++) {

                        if (report[i].label === othersLabel) {
                            continue;
                        }

                        var key = report[i].url;
                        if (!self.isUrlReport()) {
                            key = report[i].label;
                        }

                        if (key) {
                            label = report[i].label + ' (' + translate('Transitions_NumPageviews', report[i].nb_hits) + ')';
                            self.actionNameOptions.push({key: key, value: label});
                            if (!self.actionName) {
                                self.actionName = key
                            }
                        }
                    }
                    self.onActionNameChange(self.actionName);
                }

                if (!self.actionName || self.actionNameOptions.length === 0) {
                    self.isEnabled = false;
                    self.actionName = '';
                    self.actionNameOptions.push({key: '', value: translate('CoreHome_ThereIsNoDataForThisReport')});
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

        this.fetch(this.actionType);
    }
})();