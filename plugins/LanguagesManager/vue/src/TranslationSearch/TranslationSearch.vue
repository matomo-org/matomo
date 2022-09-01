<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <p>
      This page helps you to find existing translations that you can reuse in your Plugin.
      If you want to know more about translations have a look at our
      <a
        href="https://developer.matomo.org/guides/internationalization"
        rel="noreferrer noopener"
        target="_blank"
      >Internationalization guide</a>.
      Enter a search term to find translations and their corresponding keys:
    </p>
    <div>
      <Field
        uicontrol="text"
        name="alias"
        inline-help="Search for English translation. Max 1000 results will be shown."
        placeholder="Search for English translation"
        v-model="searchTerm"
      >
      </Field>
    </div>
    <div>
      <Field
        uicontrol="select"
        name="translationSearch.compareLanguage"
        inline-help="Optionally select a language to compare the English language with."
        :model-value="compareLanguage"
        @update:model-value="compareLanguage = $event; doCompareLanguage()"
        :options="languages"
      >
      </Field>
    </div>
    <br />
    <br />
    <table
      style="word-break: break-all;"
      v-show="searchTerm"
      v-content-table
    >
      <thead>
        <tr>
          <th style="width:250px;">Key</th>
          <th>English translation</th>
          <th v-show="compareLanguage && compareTranslations">Compare translation</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="translation in filteredTranslations"
          :key="translation.label"
        >
          <td>{{ translation.label }}</td>
          <td>{{ translation.value }}</td>
          <td v-if="compareLanguage && compareTranslations">
            {{ compareTranslations[translation.label] }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { AjaxHelper, ContentTable, useExternalPluginComponent } from 'CoreHome';

interface Option {
  key: string;
  value: string;
}

interface Translation {
  label: string;
  value: string;
}

interface Language {
  code: string;
  name: string;
}

interface TranslationSearchState {
  compareTranslations: Record<string, string>|null;
  existingTranslations: Translation[];
  languages: Option[];
  compareLanguage: string;
  searchTerm: string;
}

// loading a component this way since during Installation we don't want to load CorePluginsAdmin
// just for the language selector directive
const Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');

export default defineComponent({
  components: {
    Field,
  },
  directives: {
    ContentTable,
  },
  data(): TranslationSearchState {
    return {
      compareTranslations: null,
      existingTranslations: [],
      languages: [],
      compareLanguage: '',
      searchTerm: '',
    };
  },
  created() {
    this.fetchTranslations('en');
    this.fetchLanguages();
  },
  methods: {
    fetchTranslations(languageCode: string) {
      AjaxHelper.fetch<Translation[]>({
        method: 'LanguagesManager.getTranslationsForLanguage',
        filter_limit: -1,
        languageCode,
      }).then((response) => {
        if (!response) {
          return;
        }

        if (languageCode === 'en') {
          this.existingTranslations = response;
        } else {
          this.compareTranslations = {};
          response.forEach((translation) => {
            this.compareTranslations![translation.label] = translation.value;
          });
        }
      });
    },
    fetchLanguages() {
      AjaxHelper.fetch<Language[]>({
        method: 'LanguagesManager.getAvailableLanguagesInfo',
        filter_limit: -1,
      }).then((languages) => {
        this.languages = [{
          key: '',
          value: 'None',
        }];

        if (languages) {
          languages.forEach((language) => {
            if (language.code === 'en') {
              return;
            }

            this.languages.push({
              key: language.code,
              value: language.name,
            });
          });
        }
      });
    },
    doCompareLanguage() {
      if (this.compareLanguage) {
        this.compareTranslations = null;
        this.fetchTranslations(this.compareLanguage);
      }
    },
  },
  computed: {
    filteredTranslations(): Translation[] {
      let filtered = this.existingTranslations.filter(
        (t) => t.label.includes(this.searchTerm) || t.value.includes(this.searchTerm),
      );
      filtered = filtered.slice(0, 1000);
      return filtered;
    },
  },
});
</script>
