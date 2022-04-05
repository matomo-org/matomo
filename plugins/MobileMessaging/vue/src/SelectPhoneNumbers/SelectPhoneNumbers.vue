<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class='mobile'>
    <Field
      uicontrol="checkbox"
      var-type="array"
      name="phoneNumbers"
      :model-value="modelValue"
      @update:model-value="$emit('update:modelValue', $event)"
      :introduction="withIntroduction ? translate('ScheduledReports_SendReportTo') : undefined"
      :title="translate('MobileMessaging_PhoneNumbers')"
      :disabled="phoneNumbers.length === 0"
      :options="phoneNumbers"
    >
      <template v-slot:inline-help>
        <div id="mobilePhoneNumbersHelp" class="inline-help-node">
          <span class="icon-info" style="margin-right:3.5px"></span>

          <span v-if="phoneNumbers.length === 0" style="margin-right:3.5px">
            {{ translate('MobileMessaging_MobileReport_NoPhoneNumbers') }}
          </span>
          <span v-else style="margin-right:3.5px">
            {{ translate('MobileMessaging_MobileReport_AdditionalPhoneNumbers') }}
          </span>
          <a :href="linkTo({ module: 'MobileMessaging', action: 'index', updated: null })">
            {{ translate('MobileMessaging_MobileReport_MobileMessagingSettingsLink') }}
          </a>
        </div>
      </template>
    </Field>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MatomoUrl } from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

export default defineComponent({
  props: {
    modelValue: Array,
    phoneNumbers: {
      type: [Array, Object],
      required: true,
    },
    withIntroduction: Boolean,
  },
  emits: ['update:modelValue'],
  components: {
    Field,
  },
  methods: {
    linkTo(params: QueryParameters) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        ...params,
      })}`;
    },
  },
});
</script>
