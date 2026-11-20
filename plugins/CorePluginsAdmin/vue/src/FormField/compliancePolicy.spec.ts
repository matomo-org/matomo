/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  CompliancePolicyControl,
  CompliancePolicyControls,
  compliancePolicyMetadata,
  isFieldLockedByPolicies,
} from './compliancePolicy';

function control(overrides: Partial<CompliancePolicyControl> = {}): CompliancePolicyControl {
  return {
    policyTitle: 'CNIL',
    scope: 'instance',
    constraintType: 'exact',
    ...overrides,
  };
}

function controls(...entries: CompliancePolicyControl[]): CompliancePolicyControls {
  return entries.reduce((all, entry, index) => ({ ...all, [`policy_${index}`]: entry }), {});
}

describe('isFieldLockedByPolicies', () => {
  it('locks the field when a policy leaves no compliant alternative', () => {
    expect(isFieldLockedByPolicies(controls(control({ constraintType: 'exact' })))).toBe(true);
  });

  it('leaves the field editable when a policy only bounds it', () => {
    expect(isFieldLockedByPolicies(controls(control({ constraintType: 'min' })))).toBe(false);
    expect(isFieldLockedByPolicies(controls(control({ constraintType: 'max' })))).toBe(false);
  });

  it('locks the field as soon as one of several policies leaves no alternative', () => {
    const mixed = controls(
      control({ constraintType: 'min' }),
      control({ constraintType: 'exact' }),
    );

    expect(isFieldLockedByPolicies(mixed)).toBe(true);
  });

  it('leaves a field no policy controls editable', () => {
    expect(isFieldLockedByPolicies({})).toBe(false);
    expect(isFieldLockedByPolicies(undefined)).toBe(false);
  });
});

describe('compliancePolicyMetadata', () => {
  it('describes the policies controlling the field', () => {
    const controlling = controls(control());

    expect(compliancePolicyMetadata(controlling)).toEqual({
      compliancePolicyControlled: controlling,
    });
  });

  it('describes nothing when no policy controls the field, so no empty note is rendered', () => {
    expect(compliancePolicyMetadata({})).toBeUndefined();
    expect(compliancePolicyMetadata(undefined)).toBeUndefined();
  });
});
