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
    <h3>{{ translate('PrivacyManager_OptOutAppearance') }}</h3>
    <div>
      <span>
         <label>
          <input
            id="applyStyling"
            type="checkbox"
            name="applyStyling"
            v-model="applyStyling"
            @keydown="updateCode()"
            @change="updateCode()"
          />
           <span>
             {{ translate('PrivacyManager_ApplyStyling') }}
           </span>
         </label>
      </span>
    </div>
    <div v-if="applyStyling" id="opt-out-styling">
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
            :value="fontSize"
            @keydown="onFontSizeChange($event)"
            @change="onFontSizeChange($event)"
          />
        </span>
        <span>
          <select
            class="browser-default"
            :value="fontSizeUnit"
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
            :value="fontFamily"
            @keydown="onFontFamilyChange($event)"
            @change="onFontFamilyChange($event)"
          />
        </span>
      </p>
    </div>
    <div>
      <span>
         <label>
          <input
            id="showIntro"
            type="checkbox"
            name="showIntro"
            v-model="showIntro"
            @keydown="updateCode()"
            @change="updateCode()"
          />
           <span>
             {{ translate('PrivacyManager_ShowIntro') }}
           </span>
         </label>
      </span>
    </div>
    <h3>{{ translate('PrivacyManager_OptOutPreview') }}</h3>
    <iframe
      id="previewIframe"
      style="border: 1px solid #333; height: 200px; width: 600px;"
      :src="iframeUrl"
      :class="{ withBg }"
    />
  </div>
  <div>
    <div class="form-group row">
      <div class="col s12 m6">
        <h3>{{ translate('PrivacyManager_OptOutHtmlCode') }}</h3>
        <p>
          <label for="codeType1">
            <input
              type="radio"
              id="codeType1"
              name="codeType"
              value="tracker"
              v-model="codeType"
              @keydown="updateCode()"
              @change="updateCode()"
            />
            <span>{{ translate('PrivacyManager_OptOutUseTracker') }}</span>
          </label>
        </p>

        <p>
          <label for="codeType2">
            <input
              type="radio"
              id="codeType2"
              name="codeType"
              value="selfContained"
              v-model="codeType"
              @keydown="updateCode()"
              @change="updateCode()"
            />
            <span>{{ translate('PrivacyManager_OptOutUseStandalone') }}</span>
          </label>
        </p>

        <div v-if="codeType === 'selfContained'">
          <div>
              <Field
                uicontrol="select"
                name="language"
                v-model="language"
                :title="translate('General_Language')"
                :options="languageOptions"
                @keydown="updateCode()"
                @change="updateCode()"
              />
          </div>
        </div>

      </div>
      <div class="col s12 m6">
        <div class="form-help" v-html="$sanitize(codeTypeHelp)">
        </div>
      </div>
    </div>
  </div>

  <div>
    <div>
      <pre v-copy-to-clipboard="{}" ref="pre">
{{ codeBox }}
      </pre>
    </div>
    <p
      v-html="$sanitize(optOutExplanationIntro)">
    </p>
    <div class="system notification notification-info optOutTestReminder">
      <p>
      <strong>{{ translate('PrivacyManager_OptOutRememberToTest') }}</strong>
      </p>
      <p>
      {{ translate('PrivacyManager_OptOutRememberToTestBody') }}
      </p>
      <p>
        <ul>
          <li>{{ translate('PrivacyManager_OptOutRememberToTestStep1') }}</li>
          <li>{{ translate('PrivacyManager_OptOutRememberToTestStep2') }}</li>
          <li>{{ translate('PrivacyManager_OptOutRememberToTestStep3') }}</li>
          <li>{{ translate('PrivacyManager_OptOutRememberToTestStep4') }}</li>
        </ul>
      </p>
    </div>
    <h3>{{ translate('PrivacyManager_BuildYourOwn') }}</h3>
    <p
      v-html="$sanitize(optOutCustomOptOutLink)">
    </p>

  </div>
</template>

<script lang="ts">
/* eslint-disable no-mixed-operators */
/* eslint-disable no-bitwise */

import { defineComponent } from 'vue';
import {
  translate,
  CopyToClipboard,
  debounce,
  MatomoUrl,
  AjaxHelper,
} from 'CoreHome';
import {
  Field,
} from 'CorePluginsAdmin';

interface OptOutCustomizerState {
  fontSizeUnit: string;
  backgroundColor: string;
  fontColor: string;
  fontSize: string;
  fontFamily: string;
  showIntro: null|boolean;
  applyStyling: boolean;
  codeType: string;
  code: string;
  language: string;
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
    currentLanguageCode: {
      type: String,
      required: true,
    },
    languageOptions: {
      type: Object,
      required: true,
    },
    matomoUrl: String,
  },
  components: {
    Field,
  },
  directives: {
    CopyToClipboard,
  },
  data(): OptOutCustomizerState {
    return {
      fontSizeUnit: 'px',
      backgroundColor: '#FFFFFF',
      fontColor: '#000000',
      fontSize: '12',
      fontFamily: 'Arial',
      showIntro: true,
      applyStyling: false,
      codeType: 'tracker',
      code: '',
      language: this.currentLanguageCode,
    };
  },
  created() {
    this.onFontColorChange = debounce(this.onFontColorChange, 50);
    this.onBgColorChange = debounce(this.onBgColorChange, 50);
    this.onFontSizeChange = debounce(this.onFontSizeChange, 50);
    this.onFontSizeUnitChange = debounce(this.onFontSizeUnitChange, 50);
    this.onFontFamilyChange = debounce(this.onFontFamilyChange, 50);

    if (this.matomoUrl) {
      this.updateCode();
    }
  },
  methods: {
    onFontColorChange(event: Event) {
      this.fontColor = (event.target as HTMLInputElement).value;
      this.updateCode();
    },
    onBgColorChange(event: Event) {
      this.backgroundColor = (event.target as HTMLInputElement).value;
      this.updateCode();
    },
    onFontSizeChange(event: Event) {
      this.fontSize = (event.target as HTMLInputElement).value;
      this.updateCode();
    },
    onFontSizeUnitChange(event: Event) {
      this.fontSizeUnit = (event.target as HTMLInputElement).value;
      this.updateCode();
    },
    onFontFamilyChange(event: Event) {
      this.fontFamily = (event.target as HTMLInputElement).value;
      this.updateCode();
    },
    updateCode() {
      let methodName = 'CoreAdminHome.getOptOutJSEmbedCode';
      if (this.codeType === 'selfContained') {
        methodName = 'CoreAdminHome.getOptOutSelfContainedEmbedCode';
      }
      AjaxHelper.fetch({
        method: methodName,
        backgroundColor: this.backgroundColor.substr(1),
        fontColor: this.fontColor.substr(1),
        fontSize: this.fontSizeWithUnit,
        fontFamily: this.fontFamily,
        showIntro: (this.showIntro === true ? 1 : 0),
        applyStyling: (this.applyStyling === true ? 1 : 0),
        matomoUrl: this.matomoUrl,
        language: (this.codeType === 'selfContained' ? this.language : 'auto'),
      }).then((data) => {
        this.code = data.value || '';
      });
    },
  },
  watch: {
    codeBox() {
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
      return !!this.matomoUrl
        && this.backgroundColor === ''
        && this.fontColor !== ''
        && nearlyWhite(this.fontColor.slice(1));
    },
    codeBox(): string {
      if (this.matomoUrl) {
        return this.code;
      }
      return '';
    },
    iframeUrl(): string {
      const query = MatomoUrl.stringify({
        module: 'CoreAdminHome',
        action: 'optOut',
        language: this.language,
        backgroundColor: this.backgroundColor.substr(1),
        fontColor: this.fontColor.substr(1),
        fontSize: this.fontSizeWithUnit,
        fontFamily: this.fontFamily,
        applyStyling: (this.applyStyling === true ? 1 : 0),
        showIntro: (this.showIntro === true ? 1 : 0),
      });
      return `${this.matomoUrl}index.php?${query}`;
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
    optOutCustomOptOutLink() {
      const link = 'https://developer.matomo.org/guides/tracking-javascript-guide#optional-creating-a-custom-opt-out-form';
      return translate(
        'CoreAdminHome_OptOutCustomOptOutLink',
        `<a href="${link}" rel="noreferrer noopener" target="_blank">`,
        '</a>',
      );
    },
    codeTypeHelp() {
      return translate('PrivacyManager_OptOutCodeTypeExplanation');
    },
  },
});
</script>
