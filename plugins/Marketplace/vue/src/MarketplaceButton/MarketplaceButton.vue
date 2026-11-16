<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <a
    class="marketplaceButton"
    :class="{
      'marketplaceButton--outline': outline,
      'marketplaceButton--fullWidth': fullWidth,
    }"
    :href="href"
  ><slot /></a>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

/**
 * A Marketplace link drawn as a button, in the brand colour or, with `outline`, as the outline the
 * plugin card's secondary actions use. Its own block rather than Materialize's .btn, so nothing has
 * to override .btn's uppercase and colours to make it match the redesign.
 */
export default defineComponent({
  props: {
    href: {
      type: String,
      required: true,
    },
    outline: {
      type: Boolean,
      default: false,
    },
    fullWidth: {
      type: Boolean,
      default: false,
    },
  },
});
</script>
