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
    <!-- `title` carries no styles of ours; it stays in the DOM because third-party code reads
         the report name from `.enrichedHeadline .title`, eg. the ReportSorter plugin. -->
    <div
      v-if="!editUrl"
      class="enrichedHeadline__title title"
      tabindex="6"
    >
      <slot />
    </div>
    <a
      v-if="editUrl"
      class="enrichedHeadline__title enrichedHeadline__title--editable title"
      :href="editUrl"
      :title="translate('CoreHome_ClickToEditX', htmlEntities(actualFeatureName || ''))"
    >
      <slot />
    </a>
    <!-- A class rather than `v-show`: the bar has to keep its box when it is not showing, so the
         title is laid out against the same width either way. -->
    <div
      class="enrichedHeadline__iconsBar"
      :class="{ 'enrichedHeadline__iconsBar--visible': showIcons || showInlineHelp }"
    >
      <a
        v-if="helpUrl && !actualInlineHelp"
        rel="noreferrer noopener"
        target="_blank"
        class="enrichedHeadline__helpIcon"
        :href="helpUrl"
        :title="translate('CoreHome_ExternalHelp')"
      ><span class="icon-help" /></a>
      <a
        v-if="actualInlineHelp"
        v-on:click="showInlineHelp = !showInlineHelp"
        class="enrichedHeadline__helpIcon"
        :class="{ 'enrichedHeadline__helpIcon--active': showInlineHelp }"
        :title="translate(reportGenerated ? 'General_HelpReport' : 'General_Help')"
      ><span class="icon-info" /></a>
      <div class="enrichedHeadline__ratingIcons" v-if="showRateFeature">
        <component :title="actualFeatureName" :is="asComponent(rateFeature)"></component>
      </div>
    </div>
    <!-- A host offering somewhere of its own takes the panel out of here, so a panel this wide
         does not stretch the row the heading sits in. Every caller outside ReportHeader offers
         nothing and the panel stays. -->
    <div class="enrichedHeadline__help">
      <Teleport :to="helpContainer" :disabled="!helpContainer">
        <!-- `v-if`, not `v-show`: a panel merely hidden is still a child, and would keep both
             nest elements from collapsing to nothing while it is closed. -->
        <div v-if="showInlineHelp" class="mtm-helpPanel">
          <div v-html="$sanitize(actualInlineHelp)"/>
          <span class="mtm-helpPanel__date"
                v-if="reportGenerated!=''"
                v-html="$sanitize(reportGenerated)"></span>
          <a
            v-if="helpUrl"
            rel="noreferrer noopener"
            target="_blank"
            class="mtm-helpPanel__readMore"
            :href="helpUrl"
          >{{ translate('General_MoreDetails') }}</a>
        </div>
      </Teleport>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, Component, PropType } from 'vue';
import Matomo from '../Matomo/Matomo';
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
 * <h2><EnrichedHeadline :help-container="element">Pages report</EnrichedHeadline></h2>
 * -> renders the help panel into the given element instead of inside the headline, for a host
 *    that wants it somewhere the heading's own row cannot stretch to
 *
 * <h2><EnrichedHeadline report-generated="generated time">Pages report</EnrichedHeadline></h2>
 * -> reportGenerated specified via this attribute is shown at the foot of the help panel
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
    helpContainer: {
      type: Object as PropType<HTMLElement|null>,
      default: null,
    },
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

      // A related report may have no documentation; close the popup rather than leave it open
      // and blank, since the icon that reopens it is hidden without help.
      if (!newValue) {
        this.showInlineHelp = false;
      }
    },
    featureName(newValue: string) {
      this.actualFeatureName = newValue;
    },
  },
  mounted() {
    if (!this.actualFeatureName) {
      this.actualFeatureName = this.readReportFeatureName();
    }
  },
  methods: {
    // Expose the plugin component to `<component :is>` as a plain Component.
    asComponent(component: unknown): Component {
      return component as Component;
    },
    htmlEntities(v: string) {
      return Matomo.helper.htmlEntities(v);
    },
    readReportFeatureName(): string {
      const root = this.$refs.root as HTMLElement;
      return root?.querySelector('.enrichedHeadline__title')?.textContent?.trim() || '';
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
