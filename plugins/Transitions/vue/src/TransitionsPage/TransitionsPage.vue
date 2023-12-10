<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    v-if="!isWidget"
    :help-text="translate('Transitions_FeatureDescription')"
    :help-url="externalRawLink('https://matomo.org/docs/transitions/')"
    :content-title="translate('Transitions_Transitions')"
  >
    <TransitionSwitcher
      :is-widget="isWidget"
    />
  </ContentBlock>

  <TransitionSwitcher
    v-else
    :is-widget="isWidget"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock } from 'CoreHome';
import TransitionSwitcher from '../TransitionSwitcher/TransitionSwitcher.vue';

export default defineComponent({
  props: {
    isWidget: Boolean,
  },
  components: {
    ContentBlock,
    TransitionSwitcher,
  },
});
</script>
