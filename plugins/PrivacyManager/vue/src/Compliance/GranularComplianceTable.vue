<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <table class="card-table dataTable compliance granularCompliance">
    <thead>
      <tr>
        <th class="label">
          {{ translate('PrivacyManager_ComplianceTableSettingName') }}
        </th>
        <th class="label">
          {{ translate('PrivacyManager_ComplianceTableWhatItDoes') }}
        </th>
        <th class="label">
          {{ translate('PrivacyManager_ComplianceTableImpact') }}
        </th>
        <th class="label">
          {{ translate('PrivacyManager_ComplianceTableSettingStatus') }}
        </th>
        <th class="label toggleColumn" v-if="showToggles">
          <span class="sr-only">{{ translate('PrivacyManager_ComplianceStatusEnforced') }}</span>
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="setting in settings" :key="setting.id">
        <td class="settingName">{{ setting.name }}</td>
        <td v-html="$sanitize(setting.whatItDoes)" />
        <td v-html="$sanitize(setting.impact)" />
        <td :class="['status', getStatusClass(setting)]">
          <span
            v-if="getIconClass(setting)"
            :class="['icon', getIconClass(setting)]"
          ></span>
          {{ translate(getStatusText(setting)) }}
        </td>
        <td class="toggleColumn" v-if="showToggles">
          <div
            :class="['switch', 'switch-' + setting.id.replace('.', '-')]"
            v-if="setting.toggleable"
          >
            <label>
              <input
                type="checkbox"
                :id="'toggle-' + setting.id"
                :checked="isToggledOn(setting)"
                :disabled="disabled"
                :aria-label="translate('PrivacyManager_ComplianceStatusEnforced') + ': ' + setting.name"
                @change="$emit('toggle', setting.id)"
              />
              <span class="lever"></span>
            </label>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { PolicySetting } from './GranularCompliance.store';

const statusClassMap: Record<string, string> = {
  compliant: 'compliant',
  enforced: 'enforced',
  non_compliant: 'non-compliant',
  on_by_default: 'on-by-default',
  manual: 'manual',
  applies_on_save: 'applies-on-save',
};

const iconClassMap: Record<string, string> = {
  compliant: 'icon-ok',
  enforced: 'icon-ok',
  non_compliant: '',
  on_by_default: 'icon-locked',
  manual: 'icon-locked',
  applies_on_save: 'icon-warning',
};

const statusTextMap: Record<string, string> = {
  compliant: 'PrivacyManager_ComplianceCompliant',
  enforced: 'PrivacyManager_ComplianceStatusEnforced',
  non_compliant: 'PrivacyManager_ComplianceNonCompliant',
  on_by_default: 'PrivacyManager_ComplianceStatusOnByDefault',
  manual: 'PrivacyManager_ComplianceStatusManual',
  applies_on_save: 'PrivacyManager_ComplianceStatusAppliesOnSave',
};

export default defineComponent({
  props: {
    settings: {
      type: Array as PropType<readonly PolicySetting[]>,
      required: true,
    },
    localEnforced: {
      type: Object as PropType<Readonly<Record<string, boolean>>>,
      default: () => ({}),
    },
    dirtySettingIds: {
      type: Array as PropType<readonly string[]>,
      default: () => [],
    },
    showToggles: {
      type: Boolean,
      default: true,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['toggle'],
  methods: {
    getEffectiveStatus(setting: PolicySetting): string {
      if (setting.toggleable && this.dirtySettingIds.indexOf(setting.id) !== -1) {
        return 'applies_on_save';
      }
      return setting.status;
    },
    getStatusClass(setting: PolicySetting): string {
      return statusClassMap[this.getEffectiveStatus(setting)] || 'unknown';
    },
    getIconClass(setting: PolicySetting): string {
      const status = this.getEffectiveStatus(setting);
      return status in iconClassMap ? iconClassMap[status] : 'icon-circle';
    },
    getStatusText(setting: PolicySetting): string {
      return statusTextMap[this.getEffectiveStatus(setting)]
        || 'PrivacyManager_ComplianceComplianceUnknown';
    },
    isToggledOn(setting: PolicySetting): boolean {
      return !!this.localEnforced[setting.id];
    },
  },
});
</script>
