/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * How long a Marketplace request may hang before the page calls it a failure.
 *
 * `AjaxHelper.send()` neither resolves nor rejects when the request never reaches the server
 * (`xhr.status === 0`), so without this a skeleton would sit there for good.
 */
export const FETCH_TIMEOUT_MS = 30000;

/**
 * The one stand-in `Plugins::addPluginCoverImage()` falls back to for a plugin with no screenshot,
 * and the one a cover that fails to load is swapped for. It is line art on a white ground, so on a
 * dark page it needs the same inversion every other Matomo illustration gets - a real screenshot
 * must not be touched.
 */
export const PLACEHOLDER_COVER = 'plugins/Marketplace/images/categories/uncategorised.png';
