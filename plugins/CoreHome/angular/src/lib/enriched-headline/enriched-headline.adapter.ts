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
        // NOTE: transcluding into an angularjs directive, that projects content into an angular component doesn't appear to work.
        //       getting around this by getting the transcluded content programmatically, then using ng-bind-html to trigger
        //       content projection.
        //       Also note that the ng-bind-html has to be on a separate child element, or it will just replace the angular component.
        template: `<piwik-enriched-headline-downgrade
            [helpUrl]="helpUrl"
            [editUrl]="editUrl"
            [reportGenerated]="reportGenerated"
            [featureName]="featureName"
            [inlineHelp]="inlineHelp"
            [showReportGenerated]="showReportGenerated == '1'"
        >
            <div ng-bind-html="transcludedContent"></div>
        </piwik-enriched-headline-downgrade>`,
        link: function (scope: any, element: any, attrs: any, ctrl: any, transclude: any) {
            for (let index in defaults) {
                if (!attrs[index]) {
                    attrs[index] = defaults[index];
                }
            }

            // TODO: everything below should be a a helper function
            transclude(scope, function (clone: any) {
                scope.transcludedContent = getTranscludedContent(clone);
            });

            function getTranscludedContent(clone: any) {
                let result = '';
                for (let node of clone) {
                    result += node.innerHTML || node.textContent || '';
                }
                return result;
            }
        },
    };
}