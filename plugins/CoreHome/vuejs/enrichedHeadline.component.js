/**
 * Usage:
 *
 * <enriched-headline>All Websites Dashboard</enriched-headline>
 * -> uses "All Websites Dashboard" as featurename
 *
 * <enriched-headline feature-name="All Websites Dashboard">All Websites Dashboard (Total: 309 Visits)</enriched-headline>
 * -> custom featurename
 *
 * <enriched-headline help-url="http://piwik.org/guide">All Websites Dashboard</enriched-headline>
 * -> shows help icon and links to external url
 *
 * <enriched-headline edit-url="index.php?module=Foo&action=bar&id=4">All Websites Dashboard</enriched-headline>
 * -> makes the headline clickable linking to the specified url
 *
 * <enriched-headline inline-help="inlineHelp">Pages report</enriched-headline>
 * -> inlineHelp specified via a attribute shows help icon on headline hover
 *
 * <enriched-headline>All Websites Dashboard
 *     <div class="inlineHelp">My <strong>inline help</strong></div>
 * </enriched-headline>
 * -> alternative definition for inline help
 * -> shows help icon to display inline help on click. Note: You can combine inlinehelp and help-url
 *
 * * <h2 piwik-enriched-headline report-generated="generated time">Pages report</h2>
 * -> reportGenerated specified via this attribute shows a clock icon with a tooltip which activated by hover
 * -> the tooltip shows the value of the attribute
 */
matomo.registerComponent('matomoEnrichedHeadline', {
    props: ['helpUrl', 'editUrl', 'reportGenerated', 'featureName', 'inlineHelp', 'showReportGenerated'],
    data: function () {
        return {
            showIcons: false,
            showInlineHelp: false
        }
    },
    mounted: function() {
        if (!this.inlineHelp) {

            var helpNode = $('[ng-transclude] .inlineHelp', this.$el);

            if ((!helpNode || !helpNode.length) && $(this.$el).next()) {
                // hack for reports :(
                helpNode = $(this.$el).next().find('.reportDocumentation');
            }

            if (helpNode && helpNode.length) {

                // hackish solution to get binded html of p tag within the help node
                // at this point the ng-bind-html is not yet converted into html when report is not
                // initially loaded. Using $compile doesn't work. So get and set it manually
                var helpParagraph = $('p[ng-bind-html]', helpNode);

                if (helpParagraph.length) {
                    helpParagraph.html($parse(helpParagraph.attr('ng-bind-html')));
                }

                if ($.trim(helpNode.text())) {
                    this.inlineHelp = $.trim(helpNode.html());
                }
                helpNode.remove();
            }
        }

        if (!this.featureName) {
            this.featureName = $.trim($(this.$el).find('.title').first().text());
        }

        var piwikPeriods = piwikHelper.getAngularDependency('piwikPeriods');

        if (this.reportGenerated && piwikPeriods && piwikPeriods.parse(piwik.period, piwik.currentDateString).containsToday()) {
            this.$el.find('.report-generated').first().tooltip({
                track: true,
                content: this.reportGenerated,
                items: 'div',
                show: false,
                hide: false
            });

            this.showReportGenerated = '1';
        }
    },
    template: `
      <h2><div class="enrichedHeadline"
           @mouseenter="showIcons=true" @mouseleave="showIcons=false">
          <div v-show="!editUrl" class="title" tabindex="6"><slot></slot></div>
          <a v-show="editUrl" class="title" :href="editUrl"
             :title="translate('CoreHome_ClickToEditX', escape(featureName))"
             ><slot></slot></a>
    
          <span v-show="showIcons || showInlineHelp" class="iconsBar">
            <a v-if="helpUrl && !inlineHelp"
               rel="noreferrer noopener"
               target="_blank"
               :href="helpUrl"
               :title="translate('CoreHome_ExternalHelp')"
               class="helpIcon"><span class="icon-help"></span></a>
    
            <a v-if="inlineHelp"
               :title="translate('General_Help')"
               @click="showInlineHelp=!showInlineHelp"
               class="helpIcon" :class="{ 'active': showInlineHelp }"><span class="icon-help"></span></a>
    
            <matomo-rate-feature class="ratingIcons" :title="featureName"></matomo-rate-feature>
        </span>
    
        <div v-show="showReportGenerated" class="icon-clock report-generated"></div>
        
        <div class="inlineHelp" v-show="showInlineHelp">
        <div v-html="inlineHelp"></div>
        <a v-if="helpUrl"
           rel="noreferrer noopener"
           target="_blank"
           :href="helpUrl"
           class="readMore">{{ translate('General_MoreDetails') }}</a>
        </div>
      </div></h2>
    `
});
