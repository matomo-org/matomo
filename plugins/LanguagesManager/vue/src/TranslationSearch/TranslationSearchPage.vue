<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="translate('LanguagesManager_TranslationSearch')"
    feature="true"
  >
    <TranslationSearch />
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock } from 'CoreHome';
import TranslationSearch from './TranslationSearch.vue';

export default defineComponent({
  components: {
    ContentBlock,
    TranslationSearch,
  },
});
</script>
