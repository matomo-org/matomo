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
        template: `<piwik-enriched-headline-downgrade
            [helpUrl]="helpUrl"
            [editUrl]="editUrl"
            [reportGenerated]="reportGenerated"
            [featureName]="featureName"
            [inlineHelp]="inlineHelp"
            [showReportGenerated]="showReportGenerated == '1'"
></piwik-enriched-headline-downgrade>`,
        link: function (scope: any, element: any, attrs: any) {
            for (let index in defaults) {
                if (!attrs[index]) {
                    attrs[index] = defaults[index];
                }
            }
        },
    };
}