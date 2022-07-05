<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="languageSelection">
    <MenuItemsDropdown
      :menu-title="currentLanguageName"
    >
      <a
        class="item"
        target="_blank"
        rel="noreferrer noopener"
        href="https://matomo.org/translations/"
      >
        {{ translate('LanguagesManager_AboutPiwikTranslations') }}
      </a>
      <a
        v-for="language in languages"
        :key="language.code"
        :class="`item ${language.code === currentLanguageCode ? 'active' : ''}`"
        :value="language.code"
        :title="`${language.name} (${language.english_name})`"
      >
        {{ language.name }}
      </a>

      <form action="index.php?module=LanguagesManager&amp;action=saveLanguage" method="post">
        <input type="hidden" name="language" id="language" />
        <input type="hidden" name="nonce" id="nonce" :value="nonce" />
        <!-- During installation token_auth is not set -->
        <input v-if="tokenAuth" type="hidden" name="token_auth" :value="tokenAuth"/>
      </form>
    </MenuItemsDropdown>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MenuItemsDropdown } from 'CoreHome';

export default defineComponent({
  props: {
    tokenAuth: String,
    nonce: {
      type: String,
      required: true,
    },
    languages: {
      type: Array,
      required: true,
    },
    currentLanguageCode: {
      type: String,
      required: true,
    },
    currentLanguageName: {
      type: String,
      required: true,
    },
  },
  components: {
    MenuItemsDropdown,
  },
});
</script>
