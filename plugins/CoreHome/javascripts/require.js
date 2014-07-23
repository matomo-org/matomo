/**
 * Piwik - free/libre analytics platform
 *
 * Module creation & inclusion for Piwik.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function (window) {

    var MODULE_SPLIT_REGEX = /[\/.\\]/;

    /**
     * Returns a module for its ID. Empty modules are created if they does not exist.
     *
     * Modules are currently stored in the window object.
     *
     * @param {String} moduleId e.g. 'piwik/UserCountryMap' or 'myPlugin/Widgets/FancySchmancyThing'.
     *                          The following characters can be used to separate individual modules:
     *                          '/', '.' or '\'.
     * @return {Object} The module object.
     */
    window.require = function (moduleId) {
        var parts = moduleId.split(MODULE_SPLIT_REGEX);

        // TODO: we use window objects for backwards compatibility. when rest of Piwik is rewritten to use
        //       require, we can switch simply holding the modules in a private variable.
        var currentModule = window;
        for (var i = 0; i != parts.length; ++i) {
            var part = parts[i];

            currentModule[part] = currentModule[part] || {};
            currentModule = currentModule[part];
        }
        return currentModule;
    };

})(window);