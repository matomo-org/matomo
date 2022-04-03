<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <p>{{ translate('TwoFactorAuth_RecoveryCodesExplanation') }}<br /><br /></p>
    <div class="alert alert-warning">{{ translate('TwoFactorAuth_RecoveryCodesSecurity') }}</div>

    <ul v-select-on-focus="{}" class="twoFactorRecoveryCodes browser-default" v-if="codes?.length">
      <li v-for="(code, index) in codes" :key="index">
        {{ code.toUpperCase().match(/.{1,4}/g).join('-') }}
      </li>
    </ul>
    <div class="alert alert-danger" v-else>
      {{ translate('TwoFactorAuth_RecoveryCodesAllUsed') }}
    </div>

    <p>
      <br />
      <input
        type="button"
        class="btn backupRecoveryCode"
        @click="downloadRecoveryCodes(); $emit('downloaded');"
        :value="translate('General_Download')"
        style="margin-right:3.5px"
      />
      <input
        type="button"
        class="btn backupRecoveryCode"
        @click="print(); $emit('downloaded');"
        :value="translate('General_Print')"
        style="margin-right:3.5px"
      />
      <input
        type="button"
        class="btn backupRecoveryCode"
        @click="copyRecoveryCodesToClipboard(); $emit('downloaded');"
        :value="translate('General_Copy')"
      />
    </p>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Matomo, SelectOnFocus } from 'CoreHome';

export default defineComponent({
  props: {
    codes: {
      type: Array,
      default() { return []; },
    },
  },
  directives: {
    SelectOnFocus,
  },
  emits: ['downloaded'],
  methods: {
    copyRecoveryCodesToClipboard() {
      const textarea = document.createElement('textarea');
      textarea.value = this.codes.join('\n');
      textarea.setAttribute('readonly', '');
      textarea.style.position = 'absolute';
      textarea.style.left = '-9999px';
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
    },
    downloadRecoveryCodes() {
      Matomo.helper.sendContentAsDownload('analytics_recovery_codes.txt', this.codes.join('\n'));
    },
    print() {
      window.print();
    },
  },
});
</script>
