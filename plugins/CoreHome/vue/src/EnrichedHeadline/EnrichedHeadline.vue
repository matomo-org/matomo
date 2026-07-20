<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="enrichedHeadline"
    v-on:mouseenter="showIcons = true"
    v-on:mouseleave="showIcons = false"
    ref="root"
  >
    <div
      v-if="!editUrl"
      class="title"
      tabindex="6"
    >
      <slot />
    </div>
    <a
      v-if="editUrl"
      class="title"
      :href="editUrl"
      :title="translate('CoreHome_ClickToEditX', htmlEntities(actualFeatureName || ''))"
    >
      <slot />
    </a>
    <span
      v-show="showIcons || showInlineHelp"
      class="iconsBar"
    >
      <a
        v-if="helpUrl && !actualInlineHelp"
        rel="noreferrer noopener"
        target="_blank"
        class="helpIcon"
        :href="helpUrl"
        :title="translate('CoreHome_ExternalHelp')"
      ><span class="icon-help" /></a>
      <a
        v-if="actualInlineHelp"
        v-on:click="showInlineHelp = !showInlineHelp"
        class="helpIcon"
        :class="{ 'active': showInlineHelp }"
        :title="translate(reportGenerated ? 'General_HelpReport' : 'General_Help')"
      ><span class="icon-info" /></a>
      <div class="ratingIcons" v-if="showRateFeature">
        <component :title="actualFeatureName" :is="asComponent(rateFeature)"></component>
      </div>
    </span>
    <div
      class="inlineHelp"
      v-show="showInlineHelp"
    >
      <div v-html="$sanitize(actualInlineHelp)"/>
      <span class="helpDate"
            v-if="reportGenerated!=''"
            v-html="$sanitize(reportGenerated)"></span>
      <a
        v-if="helpUrl"
        rel="noreferrer noopener"
        target="_blank"
        class="readMore"
        :href="helpUrl"
      >{{ translate('General_MoreDetails') }}</a>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, Component } from 'vue';
import Matomo from '../Matomo/Matomo';
import Periods from '../Periods/Periods';
import { translateOrDefault } from '../translate';
import useExternalPluginComponent from '../useExternalPluginComponent';

export interface EnrichedHeadlineData {
  showIcons: boolean;
  showInlineHelp: boolean;
  actualFeatureName?: string | null;
  actualInlineHelp?: string | null,
}

/**
 * Usage:
 *
 * <h2><EnrichedHeadline>All Websites Dashboard</EnrichedHeadline></h2>
 * -> uses "All Websites Dashboard" as featurename
 *
 * <h2><EnrichedHeadline feature-name="All Websites Dashboard">All Websites Dashboard (Total:
 * 309 Visits)</EnrichedHeadline></h2>
 * -> custom featurename
 *
 * <h2><EnrichedHeadline help-url="https://matomo.org/guide">All Websites Dashboard</EnrichedHeadline></h2>
 * -> shows help icon and links to external url
 *
 * <h2><EnrichedHeadline edit-url="index.php?module=Foo&action=bar&id=4">All Websites
 * Dashboard</EnrichedHeadline></h2>
 * -> makes the headline clickable linking to the specified url
 *
 * <h2><EnrichedHeadline inline-help="inlineHelp">Pages report</EnrichedHeadline></h2>
 * -> inlineHelp specified via a attribute shows help icon on headline hover
 *
 * <h2><EnrichedHeadline>All Websites Dashboard
 *     <div class="inlineHelp">My <strong>inline help</strong></div>
 * </EnrichedHeadline></h2>
 * -> alternative definition for inline help
 * -> shows help icon to display inline help on click. Note: You can combine inlinehelp and help-url
 *
 * * <h2><EnrichedHeadline report-generated="generated time">Pages report</EnrichedHeadline></h2>
 * -> reportGenerated specified via this attribute shows a clock icon with a tooltip which
 * activated by hover
 * -> the tooltip shows the value of the attribute
 */
export default defineComponent({
  props: {
    helpUrl: {
      type: String,
      default: '',
    },
    editUrl: {
      type: String,
      default: '',
    },
    reportGenerated: String,
    featureName: String,
    inlineHelp: String,
  },
  data(): EnrichedHeadlineData {
    return {
      showIcons: false,
      showInlineHelp: false,
      actualFeatureName: this.featureName,
      actualInlineHelp: this.inlineHelp,
    };
  },
  watch: {
    inlineHelp(newValue: string) {
      this.actualInlineHelp = newValue;
    },
    featureName(newValue: string) {
      this.actualFeatureName = newValue;
    },
  },
  mounted() {
    const root = this.$refs.root as HTMLElement;

    if (!this.actualInlineHelp) {
      const inlineHelpNode = root.querySelector('.title .inlineHelp');

      if (inlineHelpNode) {
        // hackish solution to get binded html of p tag within the help node
        // at this point the ng-bind-html is not yet converted into html when report is not
        // initially loaded. Using $compile doesn't work. So get and set it manually
        const helpDocs = inlineHelpNode.getAttribute('data-content')?.trim();
        if (helpDocs && helpDocs.length) {
          this.actualInlineHelp = `<p>${helpDocs}</p>`;
          // this alternate inline help node is styled visible, so drop it once consumed
          setTimeout(() => inlineHelpNode.remove(), 0);
        }
      } else {
        // hack for reports :( - the documentation is embedded in the adjacent DataTable
        this.actualInlineHelp = this.readReportDocumentation();
      }
    }

    // A related report can be loaded into the adjacent DataTable in place, without
    // re-mounting this headline (see dataTable.js). Re-read the documentation on that
    // event so the inline help does not keep showing the previous report's text.
    root.parentElement?.addEventListener('piwik:reportChanged', this.onReportChanged);

    if (!this.actualFeatureName) {
      this.actualFeatureName = this.readReportFeatureName();
    }

    if (Matomo.period && Matomo.currentDateString) {
      const currentPeriod = Periods.parse(
        Matomo.period as string,
        Matomo.currentDateString as string,
      );

      if (this.reportGenerated
        && currentPeriod.containsToday()
      ) {
        window.$(root.querySelector('.report-generated')!).tooltip({
          track: true,
          content: this.reportGenerated,
          items: 'div',
          show: false,
          hide: false,
        });
      }
    }
  },
  beforeUnmount() {
    const root = this.$refs.root as HTMLElement;
    root?.parentElement?.removeEventListener('piwik:reportChanged', this.onReportChanged);
  },
  methods: {
    // Expose the plugin component to `<component :is>` as a plain Component.
    asComponent(component: unknown): Component {
      return component as Component;
    },
    htmlEntities(v: string) {
      return Matomo.helper.htmlEntities(v);
    },
    onReportChanged() {
      // Re-read the now-current report's documentation after a related report was loaded
      // into the adjacent DataTable in place.
      this.actualInlineHelp = this.readReportDocumentation();

      // Also re-read the feature name, otherwise the rate-feature widget would submit
      // feedback under the previous report's name.
      const featureName = this.readReportFeatureName();
      if (featureName) {
        this.actualFeatureName = featureName;
      }

      // The new report may have no documentation; close the popup instead of leaving it
      // open and blank (the info icon that would reopen it is hidden when there is no help).
      if (!this.actualInlineHelp) {
        this.showInlineHelp = false;
      }
    },
    readReportFeatureName(): string {
      const root = this.$refs.root as HTMLElement;
      return root?.querySelector('.title')?.textContent?.trim() || '';
    },
    readReportDocumentation(): string {
      const root = this.$refs.root as HTMLElement;
      const helpDocs = root?.parentElement?.nextElementSibling
        ?.querySelector('.reportDocumentation')
        ?.getAttribute('data-content')?.trim();

      return helpDocs && helpDocs.length ? `<p>${helpDocs}</p>` : '';
    },
  },
  computed: {
    showRateFeature() {
      return translateOrDefault('Feedback_SendFeedback') !== 'Feedback_SendFeedback';
    },
    rateFeature() {
      if (this.showRateFeature) {
        return useExternalPluginComponent('Feedback', 'RateFeature');
      }
      return '';
    },
  },
});
</script>
