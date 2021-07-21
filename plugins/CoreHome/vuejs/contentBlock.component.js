
matomo.registerComponent('matomoContentBlock', {
    props: ['contentTitle', 'feature', 'helpUrl', 'helpText', 'anchor'],
    mounted() {
        if (this.feature === 'true') {
            this.feature = true;
        }

        var element = $(this.$el);

        if (this.anchor) {
            var anchor = $('<a></a>').attr('id', this.anchor);
            element.prepend(anchor);
        }

        var inlineHelp = element.find('slot > .contentHelp');
        if (inlineHelp.length) {
            this.helpText = inlineHelp.html();
            inlineHelp.remove();
        }

        if (this.feature === true) {
            this.feature = this.contentTitle;
        }

        var adminContent = $('#content.admin');

        var contentTopPosition = false;

        if (adminContent.length) {
            contentTopPosition = adminContent.offset().top;
        }

        if (contentTopPosition || contentTopPosition === 0) {
            var parents = element.parentsUntil('.col', '[piwik-widget-loader]');
            var topThis;
            if (parents.length) {
                // when shown within the widget loader, we need to get the offset of that element
                // as the widget loader might be still shown. Would otherwise not position correctly
                // the widgets on the admin home page
                topThis = parents.offset().top;
            } else {
                topThis = element.offset().top;
            }

            if ((topThis - contentTopPosition) < 17) {
                // we make sure to display the first card with no margin-top to have it on same as line as
                // navigation
                element.css('marginTop', '0');
            }
        }

        piwikHelper.compileAngularComponents($('.card-content-content', this.$el), {forceNewScope: true})
    },
    template: `<div class="card">
    <div class="card-content">
        <h2 v-if="contentTitle && !feature && !helpUrl && !helpText" class="card-title">{{contentTitle}}</h2>
        <matomo-enriched-headline v-if="contentTitle && (feature || helpUrl || helpText)" class="card-title"
              :feature-name="feature" :help-url="helpUrl" :inline-help="helpText">
            {{contentTitle}}</matomo-enriched-headline>
          <div class="card-content-content"><slot></slot></div>
    </div>
</div>`
});
