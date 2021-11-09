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
              :class="{[context ? `notification-${context}` : '']: !!context}"
              ref="root"
            >
              <button
                type="button"
                class="close"
                data-dismiss="alert"
                v-if="!noclear"
                v-on:click="closeNotification($event)"
              >
                &times;
              </button>
              <strong v-if="title">{{ title }}</strong>
              <!-- ng-transclude causes directive child elements to be added here -->
              <div class="notification-body">
                <slot />
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
    // TODO: shouldn't need this since the title can be specified within
    //       HTML of the node that uses the directive.
    title: String,
    context: String,
    type: String,
    noclear: Boolean,
    toastLength: {
      type: Number,
      default: 12 * 1000,
    },
    style: String,
    animate: Boolean,
  },
  computed: {
    canClose() {
      if (this.type === 'persistent') {
        // otherwise it is never possible to dismiss the notification
        return false;
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
      $(this.$refs.root).css(this.style);
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

      AjaxHelper.fetch({ // GET params
        module: 'CoreHome',
        action: 'markNotificationAsRead',
      }, { // POST params
        postParams: { notificationId: this.notificationId },
      });
    },
  },
});
</script>
