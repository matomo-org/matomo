<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="optOutCustomizer">
    <p>
      {{ translate('CoreAdminHome_OptOutExplanation') }}
      <span
        v-html="$sanitize(readThisToLearnMore)"
      />
    </p>
    <h3>{{ translate('PrivacyManager_OptOutCustomize') }}</h3>
    <div>
      <p>
        <span>
          {{ translate('PrivacyManager_FontColor') }}:
          <input
            type="color"
            :value="fontColor"
            @keydown="onFontColorChange($event)"
            @change="onFontColorChange($event)"
          />
        </span>
        <span>
          {{ translate('PrivacyManager_BackgroundColor') }}:
          <input
            type="color"
            :value="backgroundColor"
            @keydown="onBgColorChange($event)"
            @change="onBgColorChange($event)"
          />
        </span>
        <span>
          {{ translate('PrivacyManager_FontSize') }}:
          <input
            id="FontSizeInput"
            type="number"
            min="1"
            max="100"
            @keydown="onFontSizeChange($event)"
            @change="onFontSizeChange($event)"
          />
        </span>
        <span>
          <select
            class="browser-default"
            @keydown="onFontSizeUnitChange($event)"
            @change="onFontSizeUnitChange($event)"
          >
            <option value="px">px</option>
            <option value="pt">pt</option>
            <option value="em">em</option>
            <option value="rem">rem</option>
            <option value="%">%</option>
          </select>
        </span>
        <span>
          {{ translate('PrivacyManager_FontFamily') }}:
          <input
            id="FontFamilyInput"
            type="text"
            @keydown="onFontFamilyChange($event)"
            @change="onFontFamilyChange($event)"
          />
        </span>
      </p>
    </div>
    <h3>{{ translate('PrivacyManager_OptOutHtmlCode') }}</h3>
    <pre v-select-on-focus="{}" ref="pre">&lt;iframe
      style=&quot;border: 0; height: 200px; width: 600px;&quot;
      src=&quot;{{ iframeUrl }}&quot;
      &gt;&lt;/iframe&gt;</pre>
    <p
      v-html="$sanitize(optOutExplanationIntro)">
    </p>
    <h3>{{ translate('PrivacyManager_OptOutPreview') }}</h3>
    <iframe
      id="previewIframe"
      style="border: 1px solid #333; height: 200px; width: 600px;"
      :src="iframeUrl"
      :class="{ withBg }"
    />
  </div>
</template>

<script lang="ts">
/* eslint-disable no-mixed-operators */
/* eslint-disable no-bitwise */

import { defineComponent } from 'vue';
import {
  translate,
  SelectOnFocus,
  MatomoUrl,
  debounce,
} from 'CoreHome';

interface OptOutCustomizerState {
  fontSizeUnit: string;
  backgroundColor: string;
  fontColor: string;
  fontSize: string;
  fontFamily: string;
}

function nearlyWhite(hex: string) {
  const bigint = parseInt(hex, 16);
  const r = bigint >> 16 & 255;
  const g = bigint >> 8 & 255;
  const b = bigint & 255;
  return r >= 225 && g >= 225 && b >= 225;
}

const { $ } = window;

export default defineComponent({
  props: {
    language: {
      type: String,
      required: true,
    },
    piwikurl: String,
  },
  directives: {
    SelectOnFocus,
  },
  data(): OptOutCustomizerState {
    return {
      fontSizeUnit: 'px',
      backgroundColor: '',
      fontColor: '',
      fontSize: '',
      fontFamily: '',
    };
  },
  created() {
    this.onFontColorChange = debounce(this.onFontColorChange, 50);
    this.onBgColorChange = debounce(this.onBgColorChange, 50);
    this.onFontSizeChange = debounce(this.onFontSizeChange, 50);
    this.onFontSizeUnitChange = debounce(this.onFontSizeUnitChange, 50);
    this.onFontFamilyChange = debounce(this.onFontFamilyChange, 50);
  },
  methods: {
    onFontColorChange(event: Event) {
      this.fontColor = (event.target as HTMLInputElement).value;
    },
    onBgColorChange(event: Event) {
      this.backgroundColor = (event.target as HTMLInputElement).value;
    },
    onFontSizeChange(event: Event) {
      this.fontSize = (event.target as HTMLInputElement).value;
    },
    onFontSizeUnitChange(event: Event) {
      this.fontSizeUnit = (event.target as HTMLInputElement).value;
    },
    onFontFamilyChange(event: Event) {
      this.fontFamily = (event.target as HTMLInputElement).value;
    },
  },
  watch: {
    iframeUrl() {
      const pre = this.$refs.pre as HTMLElement;
      const isAnimationAlreadyRunning = $(pre).queue('fx').length > 0;
      if (!isAnimationAlreadyRunning) {
        $(pre).effect('highlight', {}, 1500);
      }
    },
  },
  computed: {
    fontSizeWithUnit(): string {
      if (this.fontSize) {
        return `${this.fontSize}${this.fontSizeUnit}`;
      }

      return '';
    },
    withBg(): boolean {
      return !!this.piwikurl
        && this.backgroundColor === ''
        && this.fontColor !== ''
        && nearlyWhite(this.fontColor.slice(1));
    },
    iframeUrl(): string {
      if (this.piwikurl) {
        const query = MatomoUrl.stringify({
          module: 'CoreAdminHome',
          action: 'optOut',
          language: this.language,
          backgroundColor: this.backgroundColor.slice(1),
          fontColor: this.fontColor.slice(1),
          fontSize: this.fontSizeWithUnit,
          fontFamily: this.fontFamily,
        });

        return `${this.piwikurl}index.php?${query}`;
      }

      return '';
    },
    readThisToLearnMore() {
      const link = 'https://matomo.org/faq/how-to/faq_25918/';
      return translate(
        'General_ReadThisToLearnMore',
        `<a rel='noreferrer noopener' target='_blank' href='${link}'>`,
        '</a>',
      );
    },
    optOutExplanationIntro() {
      return translate(
        'CoreAdminHome_OptOutExplanationIntro',
        `<a href="${this.iframeUrl}" rel="noreferrer noopener" target="_blank">`,
        '</a>',
      );
    },
  },
});
</script>
