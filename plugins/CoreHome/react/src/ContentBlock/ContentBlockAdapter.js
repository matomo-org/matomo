import {ContentBlock} from "./ContentBlock";
import {TranscludeTarget} from "../angularjs/TranscludeTarget";

const { angular, $ } = window;

angular.module('piwikApp').directive('piwikContentBlock', piwikContentBlock);

piwikContentBlock.$inject = ['$timeout'];

function piwikContentBlock($timeout){

    return {
        restrict: 'A',
        replace: true,
        transclude: true,
        scope: {
            contentTitle: '@',
            feature: '@',
            helpUrl: '@',
            helpText: '@',
            anchor: '@?'
        },
        controllerAs: 'contentBlock',
        compile: function (element, attrs) {

            if (attrs.feature === 'true') {
                attrs.feature = true;
            }

            return {
                post: function (scope, element, attrs, ctrl, transclude) {
                    transclude(scope, function (clone) {
                        $timeout(function () { // TODO: not sure if actually need this
                            const transcludeTarget = <TranscludeTarget transclude={clone}/>;
                            ContentBlock.renderTo(element[0], Object.assign({}, scope, {children: transcludeTarget}));
                        });
                    });
                },
            };
        }
    };
}
