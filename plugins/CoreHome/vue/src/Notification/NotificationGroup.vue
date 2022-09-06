<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="notification-group">
    <Notification
      v-for="(notification, index) in notifications"
      :key="notification.id || `no-id-${index}`"
      :notification-id="notification.id"
      :title="notification.title"
      :context="notification.context"
      :type="notification.type"
      :noclear="notification.noclear"
      :toast-length="notification.toastLength"
      :style="notification.style"
      :animate="notification.animate"
      :message="notification.message"
      :notification-instance-id="notification.notificationInstanceId"
      :css-class="notification.class"
      @closed="removeNotification(notification.id)"
    >
      <div v-html="$sanitize(notification.message)"/>
    </Notification>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import NotificationsStore from './Notifications.store';
import Notification from './Notification.vue';

export default defineComponent({
  props: {
    group: String,
  },
  components: {
    Notification,
  },
  computed: {
    notifications() {
      return NotificationsStore.state.notifications.filter((n) => {
        if (this.group) {
          return this.group === n.group;
        }

        return !n.group;
      });
    },
  },
  methods: {
    removeNotification(id: string) {
      NotificationsStore.remove(id);
    },
  },
});
</script>
