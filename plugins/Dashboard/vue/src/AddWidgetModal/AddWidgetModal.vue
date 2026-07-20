<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <matomo-modal
    v-model="isOpen"
    classes="add-widget-modal"
    content-class="add-widget-modal-content"
    :aria-label="translate('Dashboard_AddAWidget')"
    @closed="onClosed"
  >
    <button
      type="button"
      class="btn-close modal-close"
      :aria-label="translate('General_Close')"
      @click="close"
    >
      <i class="icon-close"></i>
    </button>
    <h3 class="add-widget-modal-title">{{ translate('Dashboard_AddAWidget') }}</h3>
    <div class="add-widget-modal-body widgetpreview-base">
      <div class="add-widget-modal-categories">
        <category-list
          :categories="categoryNames"
          :chosen-category="chosenCategory"
          @update:chosen-category="onCategoryChosen"
          @confirm="focusWidgetList"
        />
      </div>
      <div class="add-widget-modal-widgets">
        <widgets-list
          ref="widgetsList"
          :widgets="widgetsInCategory"
          :chosen-widget-id="hoveredWidgetId"
          :added-widgets="addedWidgetIds"
          :existing-widget-ids="existingWidgetIds"
          @hover="onWidgetHover"
          @select="onSelect"
        />
      </div>
      <div class="add-widget-modal-preview">
        <widget-preview
          :widget="previewWidget"
          @select="onSelect"
        />
      </div>
    </div>
  </matomo-modal>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  Matomo,
  MatomoModal,
  translate,
  WidgetsStore,
  WidgetType,
} from 'CoreHome';
import CategoryList from './CategoryList.vue';
import WidgetsList from './WidgetsList.vue';
import WidgetPreview from './WidgetPreview.vue';

const OPEN_EVENT = 'Dashboard.AddWidget.open';

interface AddWidgetModalState {
  isOpen: boolean;
  chosenCategory: string | null;
  hoveredWidgetId: string | null;
  addedWidgetIds: Set<string>;
  existingWidgetIds: Set<string>;
}

export default defineComponent({
  name: 'AddWidgetModal',
  components: {
    MatomoModal,
    CategoryList,
    WidgetsList,
    WidgetPreview,
  },
  emits: ['select'],
  data(): AddWidgetModalState {
    return {
      isOpen: false,
      chosenCategory: null,
      hoveredWidgetId: null,
      addedWidgetIds: new Set<string>(),
      existingWidgetIds: new Set<string>(),
    };
  },
  computed: {
    widgets(): Record<string, WidgetType[]> {
      return (WidgetsStore.widgets.value || {}) as Record<string, WidgetType[]>;
    },
    categoryNames(): string[] {
      return Object.keys(this.widgets);
    },
    widgetsInCategory(): WidgetType[] {
      if (!this.chosenCategory) {
        return [];
      }
      return this.widgets[this.chosenCategory] || [];
    },
    widgetsById(): Map<string, WidgetType> {
      // Rebuilds only when `WidgetsStore.widgets` mutates; `previewWidget` and
      // `onSelect` both consult this in O(1) instead of scanning every category.
      return new Map(
        Object.values(this.widgets).flat()
          .filter((w): w is WidgetType & { uniqueId: string } => !!w.uniqueId)
          .map((w) => [w.uniqueId, w]),
      );
    },
    previewWidget(): WidgetType | null {
      if (!this.hoveredWidgetId) {
        return null;
      }
      return this.widgetsById.get(this.hoveredWidgetId) || null;
    },
  },
  methods: {
    translate,

    open() {
      // Snapshot the dashboard's currently-placed widget IDs once per open so
      // WidgetsList can flag them as unavailable without hitting the DOM on
      // every render. Refreshed each open; within-session additions are tracked
      // separately via `addedWidgetIds`.
      const ids = new Set<string>();
      document.querySelectorAll('#dashboardWidgetsArea [widgetId]').forEach((el) => {
        const id = el.getAttribute('widgetId');
        if (id) {
          ids.add(id);
        }
      });
      this.existingWidgetIds = ids;
      this.isOpen = true;
    },
    close() { this.isOpen = false; },

    onClosed() {
      this.chosenCategory = null;
      this.hoveredWidgetId = null;
      this.addedWidgetIds = new Set<string>();
      this.existingWidgetIds = new Set<string>();
    },

    onCategoryChosen(category: string) {
      if (this.chosenCategory === category) {
        return;
      }
      this.chosenCategory = category;
      this.hoveredWidgetId = null;
    },

    async focusWidgetList() {
      // Wait for the widgets list to re-render under the newly chosen category
      // before focusing — the prior li elements may have unmounted.
      await this.$nextTick();
      const widgetsList = this.$refs.widgetsList as
        { focusFirst?: () => void } | undefined;
      if (widgetsList && typeof widgetsList.focusFirst === 'function') {
        widgetsList.focusFirst();
      }
    },

    onWidgetHover(uniqueId: string) {
      this.hoveredWidgetId = uniqueId;
    },

    onSelect(uniqueId: string) {
      const widget = this.widgetsById.get(uniqueId);

      if (widget) {
        // Keep the modal open so the user can add more widgets in one session;
        // the added row is greyed out via the `added-widgets` set on WidgetsList.
        this.addedWidgetIds.add(uniqueId);
        this.$emit('select', widget);
        return;
      }

      // WidgetsStore drives both the list and the lookup; a miss here means the cache
      // was unexpectedly cleared between render and click. Close anyway so the modal
      // cannot block follow-up interactions.
      console.warn(`Could not resolve dashboard widget "${uniqueId}" from cached metadata.`);
      this.close();
    },
  },
  mounted() {
    Matomo.on(OPEN_EVENT, this.open);
  },
  unmounted() {
    Matomo.off(OPEN_EVENT, this.open);
  },
});
</script>
