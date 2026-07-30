<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="reportHeader" :class="{ 'reportHeader--flush': isFullPage }">
    <div class="reportHeader__main">
      <!-- The title text lives in a nested <span> to preserve the legacy `.widgetName > span`
           contract other plugins hook into (dataTable related reports, UserCountryMap map
           title, SingleMetricView metric title + series picker). The `widgetName` class is
           kept only as that external hook — styling goes through `.reportHeader__title`.
           The tag is `h3` for a widget and `h2` for a full-page report, which owns the
           page's report heading level. -->
      <component
        :is="titleTag"
        class="reportHeader__title widgetName"
        :class="{ 'reportHeader__title--clickable': titleClickable }"
        :role="titleClickable ? 'button' : undefined"
        :tabindex="titleClickable ? 0 : undefined"
        :title="titleClickable ? titleClickHint : undefined"
        @click="onTitleClick"
        @keydown.enter.prevent="onTitleClick"
        @keydown.space.prevent="onTitleClick"
      >
        <!-- Help icon, inline help, online-guide link and the rate-feature widget all come
             from EnrichedHeadline, which owns that behaviour for every headline in Matomo.
             Unlike the old `h2.card-title` mount it is fed explicit props here instead of
             scraping the adjacent DataTable, so it also works where no DataTable follows. -->
        <EnrichedHeadline
          v-if="isEnriched"
          :feature-name="featureName"
          :inline-help="wrappedInlineHelp"
          :report-generated="reportGenerated"
          :edit-url="editUrl"
          :help-url="helpUrl"
        ><span>{{ reportTitle }}</span></EnrichedHeadline>
        <span v-else>{{ reportTitle }}</span>
      </component>
      <!-- Visually-hidden label so assistive tech announces the region as a widget,
           restoring the old `.widgetNameOffScreen` text. A full-page report is not a
           widget, so it is not announced as one. -->
      <span v-if="!isFullPage" class="u-visuallyHidden">{{ translate('General_Widget') }}</span>
    </div>

    <!-- Widget controls: an inline action row, hidden until the widget is hovered/focused.
         Each action emits an intent that onControl() bridges to the jQuery widget. -->
    <div class="reportHeader__widgetControls">
      <WidgetControls
        v-if="hasControls"
        :can-minimise="controls.minimise"
        :can-maximise="controls.maximise"
        :can-refresh="controls.refresh"
        :can-close="controls.close"
        @minimise="onControl('minimise')"
        @maximise="onControl('maximise')"
        @refresh="onControl('refresh')"
        @close="onControl('close')"
      />
    </div>

    <!-- Anchor for the report actions (visualisation switcher, export, search, ...). Rendered
         empty: on a full-page report dataTable.js moves the already-bound action row in here
         from inside the datatable, and collapses the anchor while it is empty. The widget
         header does not use it yet. -->
    <div class="reportHeader__actions" />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import EnrichedHeadline from '../EnrichedHeadline/EnrichedHeadline.vue';
import WidgetControls from '../WidgetControls/WidgetControls.vue';
import { translate } from '../translate';

export interface ControlVisibility {
  minimise: boolean;
  maximise: boolean;
  refresh: boolean;
  close: boolean;
}

// Which widget controls each context exposes. Kept here so every surface that renders
// the header stays consistent with the redesign spec. `dashboard` is the normal widget state
// (all controls only make sense on a dashboard); `maximised`/`collapsed` are its state
// variants; `widgetized`/`preview`/`fullPage` render no controls.
const CONTROLS_BY_CONTEXT: Record<string, ControlVisibility> = {
  dashboard: {
    minimise: true, maximise: true, refresh: true, close: true,
  },
  maximised: {
    minimise: true, maximise: false, refresh: true, close: false,
  },
  collapsed: {
    minimise: false, maximise: true, refresh: false, close: true,
  },
  widgetized: {
    minimise: false, maximise: false, refresh: false, close: false,
  },
  preview: {
    minimise: false, maximise: false, refresh: false, close: false,
  },
  fullPage: {
    minimise: false, maximise: false, refresh: false, close: false,
  },
};

export default defineComponent({
  props: {
    context: {
      type: String,
      default: 'dashboard',
    },
    // Deliberately not named `title`: a `title` attribute on the twig host element would be
    // picked up by the v-tooltips directive on the surrounding widget and shown as a tooltip
    // repeating the title (see Tooltips.ts, which uses jQuery UI's default `items: '[title]'`).
    reportTitle: {
      type: String,
      default: '',
    },
    titleClickable: Boolean,
    titleClickHint: {
      type: String,
      default: '',
    },
    headingLevel: {
      type: String,
      default: 'h3',
    },
    // Enrichment (help icon, inline help, online guide, rate-feature widget). Passing any of
    // these renders the title through EnrichedHeadline; passing none keeps the plain
    // `.widgetName > span` markup other plugins hook into.
    featureName: {
      type: String,
      default: '',
    },
    inlineHelp: {
      type: String,
      default: '',
    },
    reportGenerated: {
      type: String,
      default: '',
    },
    editUrl: {
      type: String,
      default: '',
    },
    helpUrl: {
      type: String,
      default: '',
    },
  },
  components: {
    EnrichedHeadline,
    WidgetControls,
  },
  emits: ['minimise', 'maximise', 'refresh', 'close', 'titleClick'],
  computed: {
    controls(): ControlVisibility {
      return CONTROLS_BY_CONTEXT[this.context] || CONTROLS_BY_CONTEXT.widgetized;
    },
    hasControls(): boolean {
      const c = this.controls;
      return c.minimise || c.maximise || c.refresh || c.close;
    },
    isFullPage(): boolean {
      return this.context === 'fullPage';
    },
    // Whitelisted so the prop can never inject an arbitrary tag.
    titleTag(): string {
      return this.headingLevel === 'h2' ? 'h2' : 'h3';
    },
    isEnriched(): boolean {
      return !!(this.featureName || this.inlineHelp || this.reportGenerated
        || this.editUrl || this.helpUrl);
    },
    // The documentation arrives as plain text; EnrichedHeadline used to wrap the value it
    // scraped in a paragraph and its help panel is styled for that, so keep the wrapper.
    wrappedInlineHelp(): string {
      return this.inlineHelp ? `<p>${this.inlineHelp}</p>` : '';
    },
  },
  methods: {
    translate,
    onTitleClick() {
      if (this.titleClickable) {
        this.$emit('titleClick');
      }
    },
    onControl(intent: string) {
      // Re-emit for Vue-native consumers...
      this.$emit(intent as 'minimise'|'maximise'|'refresh'|'close');

      // ...and dispatch a bubbling native event so non-Vue owners (the jQuery dashboard
      // widget) can bridge control intents back to their existing handlers.
      this.$el.dispatchEvent(new CustomEvent(`widgetcontrol:${intent}`, { bubbles: true }));
    },
  },
});
</script>
