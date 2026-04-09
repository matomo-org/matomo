/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
  /** @type {Function|null|undefined} */
  let autoListener = null;
  const mediaQuery = typeof window.matchMedia === 'function'
    ? window.matchMedia('(prefers-color-scheme: dark)')
    : null
  let currentPreferredThemeMode = document.documentElement.getAttribute('data-theme-mode') || 'light';

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
        return mediaQuery && mediaQuery.matches ? 'dark' : 'light';
    }

    return 'light';
  }

  /**
   * Removes the browser color scheme change listener when one is registered.
   *
   * @returns {void}
   */
  function removeAutoListener() {
    if (!mediaQuery || !autoListener) {
        return;
    }

    if (typeof mediaQuery.removeEventListener === 'function') {
      mediaQuery.removeEventListener('change', autoListener);
    }
  }

  /**
   * Adds a browser color scheme change listener for auto mode.
   *
   * @returns {void}
   */
  function addAutoListener() {
    removeAutoListener();

    if (!mediaQuery || currentPreferredThemeMode !== 'auto') {
        return;
    }

    autoListener = function () {
      emitThemeModeChange();
    };

    if (typeof mediaQuery.addEventListener === 'function') {
        mediaQuery.addEventListener('change', autoListener);
    }
  }

  /**
   * Emits the theme mode change event with the previous resolved theme.
   *
   * @returns {void}
   */
  function emitThemeModeChange() {
    if (typeof piwik.postEvent === 'function') {
      piwik.postEvent('themeModeChange');
      return;
    }

    window.dispatchEvent(new CustomEvent('themeModeChange'));
  }

  /**
   * Gets the resolved active theme mode.
   *
   * @returns {string}
   */
  piwik.getThemeMode = function () {
    return resolveThemeMode(currentPreferredThemeMode);
  };

  /**
   * Sets the saved theme mode on the page and refreshes the active theme.
   *
   * @param {string} preferredThemeMode
   * @returns {void}
   */
  piwik.setThemeMode = function (preferredThemeMode) {
    document.documentElement.setAttribute('data-theme-mode', preferredThemeMode);
    currentPreferredThemeMode = preferredThemeMode;
    addAutoListener()
    emitThemeModeChange();
  };
  addAutoListener();
}());
