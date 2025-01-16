<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div style="
    font-size: 15px;
    position: absolute;
    top: 5px;
    right: 0;
    padding: 5px;
    text-align: center;">
    <span v-html="$sanitize(description)"></span>
    <div class="switch" style="zoom: 75%">
      <label>
        <input type="checkbox" :checked="isActive" @change="toggle()">
        <span class="lever"></span>
      </label>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { setCookie, getCookie } from 'CoreHome';

export default defineComponent({
  props: {
    featureName: {
      type: String,
      required: true,
    },
    description: {
      type: String,
      required: true,
    },
    defaultValue: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  methods: {
    toggle() {
      if (this.isActive) {
        this.deactivate();
      } else {
        this.activate();
      }
    },
    activate() {
      setCookie(`feature_${this.featureName}`, '1', 14 * 24 * 60 * 1000);
      window.location.reload();
    },
    deactivate() {
      setCookie(`feature_${this.featureName}`, '0', 14 * 24 * 60 * 1000);
      window.location.reload();
    },
  },
  computed: {
    isActive() {
      const cookieValue = getCookie(`feature_${this.featureName}`);
      return (cookieValue === null && this.defaultValue) || cookieValue === '1';
    },
  },
});
</script>
