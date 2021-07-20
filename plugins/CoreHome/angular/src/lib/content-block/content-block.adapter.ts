piwikContentBlockAdapter.$inject = ['$timeout'];

export function piwikContentBlockAdapter($timeout: any) {
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
        template: `<piwik-content-block-downgrade
            [contentTitle]="contentTitle"
            [feature]="feature"
            [helpUrl]="helpUrl"
            [helpText]="helpText"
            [anchor]="anchor == 'true' || anchor == '1'"
        >
            <div class="hackTranscludeTarget"></div>
        </piwik-content-block-downgrade>`,
        link: function (scope: any, element: any, attrs: any, ctrl: any, transclude: any) {
            transclude(scope, function (clone: any) {
                setTimeout(function () {
                    element.find('.hackTranscludeTarget').replaceWith(clone);
                });
            });
        },
    };
}
