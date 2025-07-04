/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface PasswordRule {
  validationRegex: string;
  ruleText: string;
  passed: boolean | undefined;
}
