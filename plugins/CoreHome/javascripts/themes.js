/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {

    /** @type {MediaQueryList|null|undefined} */
    var browserThemePreference;
    /** @type {Function|null|undefined} */
    var browserThemePreferenceListener;
    /** @type {{preferredThemeMode: string, resolvedThemeMode: string}} */
    var themeModeState = {
        preferredThemeMode: 'light',
        resolvedThemeMode: 'light'
    };

    /**
     * Returns the current saved theme mode and the resolved active theme mode.
     *
     * @param {string} previousResolvedThemeMode
     * @returns {{preferredThemeMode: string, resolvedThemeMode: string, previousResolvedThemeMode: string}}
     */
    function getThemeModeChangeEventPayload(previousResolvedThemeMode) {
        return {
            preferredThemeMode: themeModeState.preferredThemeMode,
            resolvedThemeMode: themeModeState.resolvedThemeMode,
            previousResolvedThemeMode: previousResolvedThemeMode
        };
    }

    /**
     * Gets the browser color scheme preference media query if supported.
     *
     * @returns {MediaQueryList|null}
     */
    function getBrowserThemePreference() {
        if (typeof window.matchMedia !== 'function') {
            return null;
        }

        if (!browserThemePreference) {
            browserThemePreference = window.matchMedia('(prefers-color-scheme: dark)');
        }

        return browserThemePreference;
    }

    /**
     * Gets the saved theme mode from the current page.
     *
     * @returns {string}
     */
    function getPreferredThemeMode() {
        return document.documentElement.getAttribute('data-theme-mode') || 'light';
    }

    /**
     * Resolves the saved theme mode to the effective active theme.
     *
     * @param {string} preferredThemeMode
     * @returns {string}
     */
    function resolveThemeMode(preferredThemeMode) {
        if (preferredThemeMode === 'dark') {
            return 'dark';
        }

        if (preferredThemeMode === 'auto') {
            var mediaQuery = getBrowserThemePreference();
            return mediaQuery && mediaQuery.matches ? 'dark' : 'light';
        }

        return 'light';
    }

    /**
     * Removes the browser color scheme change listener when one is registered.
     *
     * @returns {void}
     */
    function removeBrowserThemePreferenceListener() {
        var mediaQuery = getBrowserThemePreference();
        if (!mediaQuery || !browserThemePreferenceListener) {
            return;
        }

        if (typeof mediaQuery.removeEventListener === 'function') {
          mediaQuery.removeEventListener('change', browserThemePreferenceListener);
        }
    }

    /**
     * Adds a browser color scheme change listener for auto mode.
     *
     * @returns {void}
     */
    function addBrowserThemePreferenceListener() {
        var mediaQuery = getBrowserThemePreference();
        if (!mediaQuery || browserThemePreferenceListener) {
            return;
        }

        browserThemePreferenceListener = function () {
            piwik.refreshThemeMode();
        };

        if (typeof mediaQuery.addEventListener === 'function') {
            mediaQuery.addEventListener('change', browserThemePreferenceListener);
        }
    }

    /**
     * Emits the theme mode change event with the previous resolved theme.
     *
     * @param {string} previousResolvedThemeMode
     * @returns {void}
     */
    function emitThemeModeChange(previousResolvedThemeMode) {
        var payload = getThemeModeChangeEventPayload(previousResolvedThemeMode);

        if (typeof piwik.postEvent === 'function') {
            piwik.postEvent('themeModeChange', payload);
            return;
        }

        window.dispatchEvent(new CustomEvent('themeModeChange', { detail: [payload] }));
    }

    /**
     * Gets the resolved active theme mode.
     *
     * @returns {string}
     */
    piwik.getThemeMode = function () {
        return themeModeState.resolvedThemeMode;
    };

    /**
     * Refreshes the saved and resolved theme modes and optionally emits a change event.
     *
     * @param {boolean} [emitChangeEvent]
     * @returns {{preferredThemeMode: string, resolvedThemeMode: string, previousResolvedThemeMode: string}}
     */
    piwik.refreshThemeMode = function (emitChangeEvent) {
        var previousResolvedThemeMode = themeModeState.resolvedThemeMode;

        themeModeState.preferredThemeMode = getPreferredThemeMode();
        themeModeState.resolvedThemeMode = resolveThemeMode(themeModeState.preferredThemeMode);

        removeBrowserThemePreferenceListener();
        browserThemePreferenceListener = null;

        // We add a browser theme preference listener so that we can automatically
        // change colours when our browser preferred theme has changed
        if (themeModeState.preferredThemeMode === 'auto') {
            addBrowserThemePreferenceListener();
        }

        if (emitChangeEvent !== false
            && previousResolvedThemeMode !== themeModeState.resolvedThemeMode
        ) {
            emitThemeModeChange(previousResolvedThemeMode);
        }

        return getThemeModeChangeEventPayload(previousResolvedThemeMode);
    };

    /**
     * Sets the saved theme mode on the page and refreshes the active theme.
     *
     * @param {string} preferredThemeMode
     * @returns {{preferredThemeMode: string, resolvedThemeMode: string, previousResolvedThemeMode: string}}
     */
    piwik.setThemeMode = function (preferredThemeMode) {
        document.documentElement.setAttribute('data-theme-mode', preferredThemeMode);
        return piwik.refreshThemeMode();
    };

    piwik.refreshThemeMode(false);

}());
