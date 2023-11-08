<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="richMarketplaceMenuButton">
    <hr />
    <p class="intro">{{ translate('Marketplace_RichMenuIntro') }}</p>
    <p class="cta">
      <a tabindex="5" class="btn btn-outline" @click.prevent="$emit('action')">
        <span class="icon-heart">&nbsp;</span>
        {{ translate('Marketplace_Marketplace') }}
      </a>
    </p>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({});
</script>
