<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root" class="glossaryPage">
    <div class="row">
      <div class="col s12">
        <div v-content-intro>
          <h2>{{ translate('API_Glossary') }}</h2>
          <p>
            {{ translate('API_LearnAboutCommonlyUsedTerms2') }}
          </p>
        </div>
      </div>
    </div>

    <div class="row glossary">
      <div class="col s12">
        <ul class="tabs">
          <li
            v-for="(item, keyword, index) in glossaryItems"
            :key="keyword"
            class="tab col s3"
          >
            <a :class="index === 0 ? 'active' : ''" :href="`#${keyword}`">
              {{ item.title }}
            </a>
          </li>
        </ul>
      </div>
      <div
        v-for="(item, keyword) in glossaryItems"
        :key="keyword"
        :id="keyword"
        class="col s12"
      >
        <div class="card">
          <div class="card-content">
            <div style="background:#fff;width:100%" class="pushpin">
              <h2 class="card-title">{{ item.title }}</h2>
              <ul class="pagination">
                <li
                  v-for="(letter, index) in item.letters"
                  :key="index"
                  class="waves-effect"
                  style="margin-right:3.5px"
                >
                  <a :href="`#${keyword}${letter}`">{{ letter }}</a>
                </li>
              </ul>
            </div>

            <div
              v-for="([letter, entries]) in entriesByLetter(item.entries)"
              :key="letter"
              class="scrollspy"
              :id="`${keyword}${letter}`"
            >
              <div v-for="(entry, index) in entries" :key="index">
                <h3 style="color:#4183C4;font-weight: bold;">{{ entry.name }}</h3>
                <p
                  v-if="entry.subtitle"
                  style="color:#999;text-transform:uppercase;font-weight:normal;margin-top:-16px;"
                >
                  {{ translate(entry.subtitle) }}
                </p>
                <p>
                  <span v-html="$sanitize(entry.documentation)"></span>

                  <br v-if="entry.id"/>
                  <span style="color: #bbb;" v-if="entry.id">
                    {{ entry.id }}{{ keyword === 'metrics' || entry.is_metric ? ' (API)' : '' }}
                  </span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentIntro } from 'CoreHome';

const { $ } = window;

interface GlossaryItemEntry {
  letter: string;
}

export default defineComponent({
  props: {
    glossaryItems: {
      type: Object,
      required: true,
    },
  },
  directives: {
    ContentIntro,
  },
  mounted() {
    const root = this.$refs.root as HTMLElement;

    setTimeout(() => {
      $('.scrollspy', root).scrollSpy();
      $('.pushpin', root).pushpin({ top: $('.pushpin', root).offset()!.top });
      $('.tabs', root).tabs();
    });
  },
  methods: {
    entriesByLetter(entries: GlossaryItemEntry[]) {
      const byLetter: Record<string, GlossaryItemEntry[]> = {};
      entries.forEach((entry) => {
        byLetter[entry.letter] = byLetter[entry.letter] || [];
        byLetter[entry.letter].push(entry);
      });

      const byLetterArray = Object.entries(byLetter);
      byLetterArray.sort(([lhsLetter], [rhsLetter]) => {
        if (lhsLetter < rhsLetter) {
          return -1;
        }

        if (lhsLetter > rhsLetter) {
          return 1;
        }

        return 0;
      });
      return byLetterArray;
    },
  },
});
</script>
