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
        />
      </div>
      <div class="add-widget-modal-widgets">
        <widgets-list
          :widgets="widgetsInCategory"
          :chosen-widget="hoveredWidget"
          :added-widgets="addedWidgetIds"
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
  hoveredWidget: string | null;
  addedWidgetIds: Set<string>;
}

function findWidget(
  widgets: Record<string, WidgetType[]>,
  uniqueId: string | null,
): WidgetType | null {
  if (!uniqueId) {
    return null;
  }

  const categories = Object.keys(widgets);
  for (let i = 0; i < categories.length; i += 1) {
    const { [categories[i]]: list = [] } = widgets;
    for (let j = 0; j < list.length; j += 1) {
      if (list[j].uniqueId === uniqueId) {
        return list[j];
      }
    }
  }

  return null;
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
      hoveredWidget: null,
      addedWidgetIds: new Set<string>(),
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
    previewWidget(): WidgetType | null {
      return findWidget(this.widgets, this.hoveredWidget);
    },
  },
  methods: {
    translate,

    open() { this.isOpen = true; },
    close() { this.isOpen = false; },

    onClosed() {
      this.chosenCategory = null;
      this.hoveredWidget = null;
      this.addedWidgetIds = new Set<string>();
    },

    onCategoryChosen(category: string) {
      if (this.chosenCategory === category) {
        return;
      }
      this.chosenCategory = category;
      this.hoveredWidget = null;
    },

    onWidgetHover(uniqueId: string) {
      this.hoveredWidget = uniqueId;
    },

    onSelect(uniqueId: string) {
      const widget = findWidget(this.widgets, uniqueId);

      if (widget) {
        // Keep the modal open so the user can add more widgets in one session;
        // the added row is greyed out via the `added-widgets` set on WidgetsList.
        const next = new Set(this.addedWidgetIds);
        next.add(uniqueId);
        this.addedWidgetIds = next;
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
