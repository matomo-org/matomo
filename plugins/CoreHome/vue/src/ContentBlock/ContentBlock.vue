<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="card" ref="root">
    <div class="card-content">
      <h2
        v-if="contentTitle && !actualFeature && !helpUrl && !actualHelpText"
        class="card-title"
      >{{ contentTitle }}</h2>
      <h2
        v-if="contentTitle && (actualFeature || helpUrl || actualHelpText)"
        class="card-title"
      >
        <EnrichedHeadline
          :feature-name="actualFeature"
          :help-url="helpUrl"
          :inline-help="actualHelpText"
        >
          {{ contentTitle }}
        </EnrichedHeadline>
      </h2>
      <div ref="content">
        <slot />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import EnrichedHeadline from '../EnrichedHeadline/EnrichedHeadline.vue';

let adminContent: HTMLElement|null = null;

export default defineComponent({
  props: {
    contentTitle: String,
    feature: String,
    helpUrl: String,
    helpText: String,
    anchor: String,
  },
  components: {
    EnrichedHeadline,
  },
  data() {
    return {
      actualFeature: this.feature,
      actualHelpText: this.helpText,
    };
  },
  watch: {
    feature(newValue: string) {
      this.actualFeature = newValue;
    },
    helpText(newValue: string) {
      this.actualHelpText = newValue;
    },
  },
  mounted() {
    const { root, content } = this.$refs;

    if (this.anchor) {
      const anchorElement = document.createElement('a');
      anchorElement.id = this.anchor;
      root.parentElement.prepend(anchorElement);
    }

    setTimeout(() => {
      const inlineHelp = content.querySelector('.contentHelp');
      if (inlineHelp) {
        this.actualHelpText = inlineHelp.innerHTML;
        inlineHelp.remove();
      }
    }, 0);

    if (this.actualFeature && (this.actualFeature === true || this.actualFeature === 'true')) {
      this.actualFeature = this.contentTitle;
    }

    if (adminContent === null) {
      // cache admin node for further content blocks
      adminContent = document.querySelector('#content.admin');
    }

    let contentTopPosition: number;
    if (adminContent) {
      contentTopPosition = adminContent.offsetTop;
    }

    if (contentTopPosition || contentTopPosition === 0) {
      const parents = root.closest('[piwik-widget-loader]');

      // when shown within the widget loader, we need to get the offset of that element
      // as the widget loader might be still shown. Would otherwise not position correctly
      // the widgets on the admin home page
      const topThis = parents ? parents.offsetTop : root.offsetTop;

      if (topThis - contentTopPosition < 17) {
        // we make sure to display the first card with no margin-top to have it on same as line as
        // navigation
        root.style.marginTop = 0;
      }
    }
  },
});
</script>
