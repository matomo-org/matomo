<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div id="embedThisWidgetIframe">
    <label
      for="embedThisWidgetIframeInput"
      v-html="$sanitize(translate('Widgetize_EmbedIframe'))"
    ></label>
    <div id="embedThisWidgetIframeInput">
      <pre v-select-on-focus="{}" readonly="true" id="iframeEmbed">{{ widgetIframeHtml }}</pre>
    </div>
  </div>

  <div>
    <label
      for="embedThisWidgetDirectLink"
      v-html="$sanitize(translate('Widgetize_DirectLink'))"
    ></label>
    <div id="embedThisWidgetDirectLink">
      <pre v-select-on-focus="{}" readonly="true" id="directLinkEmbed">{{ urlIframe }}</pre>
      -
      <a :href="urlIframe" rel="noreferrer noopener" target="_blank">
        {{ translate('Widgetize_OpenInNewWindow') }}
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { SelectOnFocus } from 'CoreHome';

export default defineComponent({
  props: {
    urlIframe: {
      type: String,
      required: true,
    },
    widgetIframeHtml: {
      type: String,
      required: true,
    },
  },
  inheritAttrs: false,
  directives: {
    SelectOnFocus,
  },
});
</script>
