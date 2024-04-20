<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="languageSelection">
    <MenuItemsDropdown
      :menu-title="currentLanguageName"
      @after-select="onSelect($event)"
    >
      <a
        class="item"
        target="_blank"
        rel="noreferrer noopener"
        :href="externalRawLink('https://matomo.org/translations/')"
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

      <form
        action="index.php?module=LanguagesManager&amp;action=saveLanguage"
        method="post"
        ref="form"
      >
        <input type="hidden" name="language" id="language" :value="selectedLanguage" />
        <input type="hidden" name="nonce" id="nonce" :value="formNonce" />
        <!-- During installation token_auth is not set -->
        <input v-if="tokenAuth" type="hidden" name="token_auth" :value="tokenAuth"/>
      </form>
    </MenuItemsDropdown>
  </div>
</template>

<script lang="ts">
import { defineComponent, nextTick } from 'vue';
import { MenuItemsDropdown } from 'CoreHome';

interface LanguagesDropdownState {
  selectedLanguage: string;
}

export default defineComponent({
  props: {
    tokenAuth: String,
    formNonce: {
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
  data(): LanguagesDropdownState {
    return {
      selectedLanguage: this.currentLanguageCode,
    };
  },
  methods: {
    onSelect(selected: HTMLElement) {
      this.selectedLanguage = selected.getAttribute('value')!;
      nextTick().then(() => {
        (this.$refs.form as HTMLFormElement).submit();
      });
    },
  },
});
</script>
