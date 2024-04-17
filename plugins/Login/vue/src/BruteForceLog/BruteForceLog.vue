<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock :content-title="translate('Login_CurrentlyBlockedIPs')">
    <p v-if="!blockedIps.length">{{ translate('UserCountryMap_None') }}</p>
    <ul style="margin-left: 20px;" v-else>
      <li style="list-style: disc;" v-for="(blockedIp, index) in blockedIps" :key="index">
        {{ blockedIp }}
      </li>
    </ul>

    <div v-if="blockedIps.length">
      <p>
        <br />{{ translate('Login_CurrentlyBlockedIPsUnblockInfo') }}
      </p>

      <div>
        <input
          type="button"
          class="btn"
          :value="translate('Login_UnblockAllIPs')"
          @click="unblockAllIps()"
        />
      </div>

      <div id="confirmUnblockAllIps" class="ui-confirm">
        <h2>{{ translate('Login_CurrentlyBlockedIPsUnblockConfirm') }}</h2>
        <input role="yes" type="button" :value="translate('General_Yes')"/>
        <input role="no" type="button" :value="translate('General_No')"/>
      </div>
    </div>

    <div v-if="disallowedIps.length">
      <h3>{{ translate('Login_IPsAlwaysBlocked') }}</h3>
      <ul style="margin-left: 20px;">
        <li
          style="list-style: disc;"
          v-for="(ip, index) in disallowedIps"
          :key="index"
        >
          {{ ip }}
        </li>
      </ul>
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock } from 'CoreHome';

declare global {
  interface Window {
    readonly bruteForceLog: {
      unblockAllIps(): void;
    };
  }
}

export default defineComponent({
  props: {
    blockedIps: {
      type: Array,
      required: true,
    },
    disallowedIps: {
      type: Array,
      required: true,
    },
  },
  components: {
    ContentBlock,
  },
  methods: {
    unblockAllIps() {
      window.bruteForceLog.unblockAllIps();
    },
  },
});
</script>
