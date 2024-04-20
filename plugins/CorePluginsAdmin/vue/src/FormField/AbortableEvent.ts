/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

interface AbortableEvent<T> {
  value: T;
  abort(): void;
}

export default AbortableEvent;
