<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <a
    class="item-help-icon"
    tabindex="5"
    href="javascript:"
    v-if="message!==''"
    @click="showHelp"
  >
    <span class="icon-help"/>
  </a>
</template>
<script lang="ts">

import { defineComponent } from 'vue';
import { NotificationsStore } from '../Notification';

const REPORTING_HELP_NOTIFICATION_ID = 'reportingMenu-help';

export default defineComponent({
  props: {
    message: {
      type: String,
      default: '',
    },
    name: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      helpShown: {
        type: Boolean,
        default: false,
      },
      currentName: {
        type: String,
        default: '',
      },
    };
  },
  methods: {
    showHelp() {
      if (this.helpShown && this.currentName === this.name) {
        NotificationsStore.remove(REPORTING_HELP_NOTIFICATION_ID);
        this.helpShown = false;
        this.currentName = '';
        return;
      }
      NotificationsStore.show({
        context: 'info',
        id: REPORTING_HELP_NOTIFICATION_ID,
        type: 'help',
        noclear: true,
        class: 'help-notification',
        message: this.message,
        placeat: '#notificationContainer',
        prepend: true,
      });
      if (this.name !== '') {
        this.currentName = this.name;
      }
      this.helpShown = true;
    },
  },
});
</script>
