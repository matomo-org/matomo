<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    @click="this.onClick($event)"
    class="custom_select_all"
  >
    <a
      @click="$event.preventDefault()"
      v-html="$sanitize(allSitesText)"
      tabindex="4"
      :href="href"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    href: String,
    allSitesText: String,
  },
  emits: ['click'],
  methods: {
    onClick(event: MouseEvent) {
      this.$emit('click', event);
    },
  },
});
</script>
