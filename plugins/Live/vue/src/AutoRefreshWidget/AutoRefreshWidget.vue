<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root">
    <slot />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper,
  Matomo,
} from 'CoreHome';
import {
  AutoRefreshController,
} from './AutoRefreshController';

export default defineComponent({
  props: {
    interval: Number,
    maxInterval: Number,
    dataUrlParams: {
      type: Object,
      required: true,
    },
    fadeInSpeed: {
      type: [String, Number],
      default: 600,
    },
  },
  data() {
    return {
      previousResponse: '',
      refreshController: null as AutoRefreshController<string> | null,
    };
  },
  mounted() {
    const root = this.$refs.root as HTMLElement | undefined;
    if (!root || !this.dataUrlParams) {
      return;
    }

    this.previousResponse = root.innerHTML;
    this.refreshController = new AutoRefreshController<string>({
      getBaseInterval: () => this.getBaseInterval(),
      getMaxInterval: () => this.getMaxInterval(),
      shouldRun: () => {
        const element = this.$refs.root as HTMLElement | undefined;
        return Boolean(element && element.isConnected);
      },
      request: () => AjaxHelper.fetch(this.dataUrlParams as Record<string, unknown>, {
        format: 'html',
      }),
      handleResponse: (response) => this.replaceContent(response),
    });

    this.refreshController.schedule(this.getBaseInterval());
  },
  beforeUnmount() {
    if (this.refreshController) {
      this.refreshController.destroy();
      this.refreshController = null;
    }
  },
  methods: {
    getBaseInterval(): number {
      return Number(this.interval);
    },
    getMaxInterval(): number {
      return Number(this.maxInterval);
    },
    highlight(root: HTMLElement) {
      const { fadeInSpeed } = this;
      if (!fadeInSpeed || !window.$ || !window.$.fn || !window.$.fn.effect) {
        return;
      }

      window.$(root).effect('highlight', {}, fadeInSpeed as string);
    },
    replaceContent(response: string) {
      const root = this.$refs.root as HTMLElement | undefined;
      if (!root) {
        return false;
      }

      const updated = response !== this.previousResponse;
      if (!updated) {
        return false;
      }

      root.innerHTML = response;
      Matomo.helper.compileVueEntryComponents(root);
      this.highlight(root);
      this.previousResponse = response;

      return true;
    },
  },
});
</script>
