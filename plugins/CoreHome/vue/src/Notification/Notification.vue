<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <transition
    :name="type === 'toast' ? 'slow-fade-out' : undefined"
    @after-leave="toastClosed()"
  >
    <div v-if="!deleted">
      <transition :name="type === 'toast' ? 'toast-slide-up' : undefined" appear>
        <div>
          <transition :name="animate ? 'fade-in' : undefined" appear>
            <div
              class="notification system"
              :class="cssClasses"
              :style="style"
              ref="root"
              :data-notification-instance-id="notificationInstanceId"
            >
              <button
                type="button"
                class="close"
                data-dismiss="alert"
                v-if="canClose"
                v-on:click="closeNotification($event)"
              >
                &times;
              </button>
              <strong v-if="title">{{ title }}</strong>
              <!-- ng-transclude causes directive child elements to be added here -->
              <div class="notification-body">
                <div v-if="message" v-html="$sanitize(message)"/>
                <div v-if="!message">
                  <slot />
                </div>
              </div>
            </div>
          </transition>
        </div>
      </transition>
    </div>
  </transition>
</template>

<script lang="ts">
import { defineComponent, nextTick } from 'vue';
import AjaxHelper from '../AjaxHelper/AjaxHelper';

const { $ } = window;

export default defineComponent({
  props: {
    notificationId: String,
    // NOTE: notificationId refers to server side ID for notifications stored in the session.
    // this ID is just so it can be selected outside of this component (just for scrolling).
    notificationInstanceId: String,
    title: String,
    context: String,
    type: String,
    noclear: Boolean,
    toastLength: {
      type: Number,
      default: 12 * 1000,
    },
    style: [String, Object],
    animate: Boolean,
    message: String,
    cssClass: String,
  },
  computed: {
    cssClasses() {
      const result: Record<string, boolean> = {};
      if (this.context) {
        result[`notification-${this.context}`] = true;
      }
      if (this.cssClass) {
        result[this.cssClass] = true;
      }
      return result;
    },
    canClose() {
      if (this.type === 'persistent') {
        // otherwise it is never possible to dismiss the notification
        return true;
      }

      return !this.noclear;
    },
  },
  emits: ['closed'],
  data() {
    return {
      deleted: false,
    };
  },
  mounted() {
    const addToastEvent = () => {
      setTimeout(() => {
        this.deleted = true;
      }, this.toastLength);
    };

    if (this.type === 'toast') {
      addToastEvent();
    }

    if (this.style) {
      $(this.$refs.root as HTMLElement).css(this.style as JQLiteCssProperties);
    }
  },
  methods: {
    toastClosed() {
      nextTick(() => {
        this.$emit('closed');
      });
    },
    closeNotification(event: MouseEvent) {
      if (this.canClose && event && event.target) {
        this.deleted = true;

        nextTick(() => {
          this.$emit('closed');
        });
      }

      this.markNotificationAsRead();
    },
    markNotificationAsRead() {
      if (!this.notificationId) {
        return;
      }
      AjaxHelper.post({ // GET params
        module: 'CoreHome',
        action: 'markNotificationAsRead',
      }, { // POST params
        notificationId: this.notificationId,
      },
      { withTokenInUrl: true });
    },
  },
});
</script>
