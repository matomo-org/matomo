/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * What one compliance policy enforces for a setting, as returned by
 * PolicyManager::getCompliancePoliciesControllingASetting().
 */
export interface CompliancePolicyControl {
  policyTitle: string;
  scope: 'config'|'instance'|'site';
  constraintType: 'exact'|'min'|'max';
}

/** The policies controlling one setting, keyed by policy name. */
export type CompliancePolicyControls = Record<string, CompliancePolicyControl>;

/**
 * Whether the given policies leave no compliant alternative to the value they enforce, so the
 * field they control has to be shown read-only rather than merely restricted to fewer choices.
 *
 * Mirrors PolicyManager::isFieldLockedByPolicies().
 */
export function isFieldLockedByPolicies(controls?: CompliancePolicyControls): boolean {
  return Object.values(controls ?? {}).some((control) => control.constraintType === 'exact');
}

/**
 * The extra metadata a Field needs to render the compliance note for the given policies, or
 * undefined when none applies, so that no empty note is rendered.
 */
export function compliancePolicyMetadata(
  controls?: CompliancePolicyControls,
): Record<string, unknown>|undefined {
  if (!controls || !Object.keys(controls).length) {
    return undefined;
  }

  return { compliancePolicyControlled: controls };
}
