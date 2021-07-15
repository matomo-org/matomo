
matomo.VueComponents['matomoActivityIndicator'] = {
    props: ['loading', 'loadingMessage'],
    computed: {
        message: function () {
            if (!this.loadingMessage) {
                return _pk_translate('General_LoadingData');
            }

            return this.loadingMessage;
        }
    },
    template: `<div v-show="loading" class="loadingPiwik">
      <img src="plugins/Morpheus/images/loading-blue.gif" alt=""/> <span>{{ message }}</span>
    </div>`
};
