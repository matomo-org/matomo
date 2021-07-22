import {EnrichedHeadline} from "./EnrichedHeadline";

const { angular } = window;

angular.module('piwikApp').directive('piwikEnrichedHeadline', piwikEnrichedHeadline);

piwikEnrichedHeadline.$inject = ['$document', 'piwik', '$timeout'];

function piwikEnrichedHeadline($document, piwik, $timeout){
    var defaults = {
        helpUrl: '',
        editUrl: '',
        reportGenerated: '',
        showReportGenerated: '',
    };

    return {
        transclude: true,
        restrict: 'A',
        scope: {
            helpUrl: '@',
            editUrl: '@',
            reportGenerated: '@?',
            featureName: '@',
            inlineHelp: '@?',
            showReportGenerated: '=?'
        },
        compile: function (element, attrs) {
            for (var index in defaults) {
                if (!attrs[index]) { attrs[index] = defaults[index]; }
            }

            return {
                post: function postLink(scope, element, attrs, ctrl, transclude) {
                    transclude(scope, function (clone) {
                        $timeout(function () { // TODO: not sure if actually need this
                            const elements = [];
                            clone.each(function () {
                                if (this.textContent) {
                                    elements.push(this.textContent);
                                } else {
                                    elements.push(this);
                                }
                            });

                            EnrichedHeadline.renderTo(element[0], Object.assign({}, scope, {children: elements}));
                        });
                    });
                },
            };
        }
    };
}
