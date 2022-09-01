/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

interface Capability {
  id: string;
  name: string;
  description: string;
  helpUrl: string;
  includedInRoles: string[];
  category: string;
}

export default Capability;
