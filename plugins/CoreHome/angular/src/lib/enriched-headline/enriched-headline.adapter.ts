piwikEnrichedHeadlineAdapter.$inject = Array<string>();

export function piwikEnrichedHeadlineAdapter() {
    const defaults : {[key: string]: string} = {
        helpUrl: '',
        editUrl: '',
        reportGenerated: '',
        showReportGenerated: '',
    };

    return {
        restrict: 'A',
        scope: {
            helpUrl: '@',
            editUrl: '@',
            reportGenerated: '@?',
            featureName: '@',
            inlineHelp: '@?',
            showReportGenerated: '=?'
        },
        transclude: true,
        template: `<piwik-enriched-headline-downgrade
            [helpUrl]="helpUrl"
            [editUrl]="editUrl"
            [reportGenerated]="reportGenerated"
            [featureName]="featureName"
            [inlineHelp]="inlineHelp"
            [showReportGenerated]="showReportGenerated == '1'"
        >
            <div class="hackTranscludeTarget"></div>
        </piwik-enriched-headline-downgrade>`,
        link: function (scope: any, element: any, attrs: any, ctrl: any, transclude: any) {
            for (let index in defaults) {
                if (!attrs[index]) {
                    attrs[index] = defaults[index];
                }
            }

            transclude(scope, function (clone: any) {
                setTimeout(function () {
                    element.find('.hackTranscludeTarget').append(clone);
                });
            });
        },
    };
}