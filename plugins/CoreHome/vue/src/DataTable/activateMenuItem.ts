/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// Menu entries the browser would not follow on its own, so a key press has to click them for the
// delegated handlers in dataTable.js to hear.
export default function activateMenuItem(event: KeyboardEvent): void {
  (event.currentTarget as HTMLElement | null)?.click();
}
