var Piwik_Overlay_Translations = (function () {

    /** Translations strings */
    var translations = [];

    return {

        /**
         * Initialize translations module.
         * Callback is triggered when data is available.
         */
        initialize: function (callback) {
            // Load translation data
            Matomo_Overlay_Client.api('Overlay.getTranslations', function (data) {
                translations = data;
                callback();
            });
        },

        /** Get translation string */
        get: function (identifier) {
            if (typeof translations[identifier] == 'undefined') {
                return identifier;
            }
            return translations[identifier];
        }

    };

})();
