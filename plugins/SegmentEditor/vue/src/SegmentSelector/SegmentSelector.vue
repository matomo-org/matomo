<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root">
    <div
      v-if="viewModel"
      class="segmentationContainer listHtml"
    >
      <a
        class="title"
        tabindex="4"
        :title="viewModel.currentSegmentTooltip"
        @click.prevent="togglePanel"
      >
        <span class="icon icon-segment" />
        <span
          class="segmentationTitle"
          :class="{ 'segment-clicked': !!viewModel.currentSegmentValue }"
        >
          {{ viewModel.currentSegmentTitle }}
        </span>
      </a>
      <div class="dropdown dropdown-body">
        <div class="segmentFilterContainer">
          <input
            class="segmentFilter browser-default"
            type="text"
            tabindex="4"
            v-model="searchInput"
            :placeholder="translate('General_Search')"
          >
          <span @click.prevent="clearSearch" />
        </div>
        <ul class="submenu">
          <li>
            {{ translate('SegmentEditor_SelectSegmentOfVisits') }}
            <div class="segmentList">
              <ul>
                <template
                  v-for="entry in viewModel.entries"
                  :key="entry.key"
                >
                  <span
                    v-if="entry.type === 'header'"
                    :class="entry.className"
                  >
                    <hr>
                    {{ entry.label }}:
                    <br>
                  </span>
                  <li
                    v-else-if="entry.type === 'no-results'"
                    :class="getEntryClasses(entry)"
                  >
                    {{ entry.label }}
                  </li>
                  <li
                    v-else
                    :class="getEntryClasses(entry)"
                    :data-idsegment="entry.idsegment"
                    :data-definition="entry.definition"
                    @click.prevent="selectSegment(entry)"
                    @animationend="clearStarAnimationClass(entry)"
                  >
                    <span
                      class="segname"
                      tabindex="4"
                      :title="entry.tooltip"
                      @keyup.enter.prevent="selectSegment(entry)"
                    >
                      {{ entry.label }}
                    </span>
                    <template v-if="entry.type === 'segment'">
                      <button
                        v-if="entry.showStarButton"
                        :data-star="entry.idsegment"
                        class="segmentAction starSegment"
                        :title="entry.starTitle"
                        :data-state="entry.starState"
                        @click.stop.prevent="toggleStar(entry)"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="16"
                          height="16"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke="black"
                            stroke-width="3"
                            fill="none"
                            :d="starPath"
                          />
                        </svg>
                      </button>
                      <button
                        v-if="entry.showEditButton"
                        class="segmentAction editSegment"
                        :title="entry.editTitle"
                        :data-state="entry.editState"
                        @click.stop.prevent="openEditSegment(entry)"
                      />
                      <button
                        v-if="entry.showCompareButton"
                        :class="entry.compareButtonClass"
                        :title="entry.compareTitle"
                        :data-state="entry.compareState"
                        @click.stop.prevent="toggleComparison(entry)"
                      />
                    </template>
                  </li>
                </template>
              </ul>
            </div>
          </li>
        </ul>
        <template v-if="viewModel.authorizedToCreateSegments">
          <button
            tabindex="4"
            class="add_new_segment btn"
            @click.stop.prevent="openAddSegment"
          >
            <span class="icon-add" />
            &nbsp; {{ translate('SegmentEditor_AddNewSegment') }}
          </button>
          <a
            :href="viewModel.manageSegmentsUrl"
            tabindex="4"
            class="btn btn-block btn-outline"
          >
            {{ translate('SegmentEditor_ManageSegments') }}
          </a>
        </template>
        <template v-else>
          <hr>
          <ul class="submenu">
            <li>
              <span
                v-if="viewModel.isUserAnonymous"
                class="youMustBeLoggedIn"
              >
                {{ translate('SegmentEditor_YouMustBeLoggedInToCreateSegments') }}
                <br>
                ›
                <a :href="viewModel.loginUrl">{{ translate('Login_LogIn') }}</a>
              </span>
            </li>
          </ul>
          <br>
          <br>
        </template>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from 'CoreHome';
import SegmentSelectorStore from './SegmentSelector.store';
import {
  SegmentSelectorEntry,
  SegmentSelectorViewModel,
} from '../types';

const starPath = 'M9.153 5.408C10.42 3.136 11.053 2 12 2c.947 0 1.58 1.136 2.847 3.408l.328.588c.36.646.54.969.82 1.182.28.213.63.292 1.33.45l.636.144c2.46.557 3.689.835 3.982 1.776.292.94-.546 1.921-2.223 3.882l-.434.507c-.476.557-.715.836-.822 1.18-.107.345-.071.717.001 1.46l.066.677c.253 2.617.38 3.925-.386 4.506-.766.582-1.918.051-4.22-1.009l-.597-.274c-.654-.302-.981-.452-1.328-.452-.347 0-.674.15-1.329.452l-.595.274c-2.303 1.06-3.455 1.59-4.22 1.01-.767-.582-.64-1.89-.387-4.507l.066-.676c.072-.744.108-1.116 0-1.46-.106-.345-.345-.624-.821-1.18l-.434-.508c-1.677-1.96-2.515-2.941-2.223-3.882.293-.941 1.523-1.22 3.983-1.776l.636-.144c.699-.158 1.048-.237 1.329-.45.28-.213.46-.536.82-1.182l.328-.588Z';

export default defineComponent({
  name: 'SegmentSelector',
  data() {
    return {
      filterTimer: null as ReturnType<typeof window.setTimeout> | null,
      panelContainer: null as HTMLElement | null,
      searchInput: '',
      debouncedSearchInput: '',
      starAnimationClasses: {} as Record<string, string>,
      starPath,
      unsubscribeStarChange: null as (() => void) | null,
    };
  },
  computed: {
    viewModel(): SegmentSelectorViewModel | null {
      if (!SegmentSelectorStore.state.value.isInitialized) {
        return null;
      }

      const filterValue = this.debouncedSearchInput.length >= 2 ? this.debouncedSearchInput : '';
      return SegmentSelectorStore.getSelectorViewModel(filterValue) as SegmentSelectorViewModel;
    },
  },
  mounted() {
    const root = this.$refs.root as HTMLElement;
    this.panelContainer = root.closest('.segmentListContainer');
    if (this.panelContainer) {
      this.panelContainer.addEventListener('SegmentEditor.resetFilter', this.clearSearch);
    }

    this.unsubscribeStarChange = SegmentSelectorStore.onStarChange((segment, isError) => {
      const segmentId = `${segment.idsegment || ''}`;
      if (!segmentId) {
        return;
      }

      this.starAnimationClasses = {
        ...this.starAnimationClasses,
        [segmentId]: isError ? 'segmentStarErrorAnimation' : 'segmentStarAnimation',
      };
    });
  },
  beforeUnmount() {
    if (this.panelContainer) {
      this.panelContainer.removeEventListener('SegmentEditor.resetFilter', this.clearSearch);
    }

    if (this.unsubscribeStarChange) {
      this.unsubscribeStarChange();
      this.unsubscribeStarChange = null;
    }

    if (this.filterTimer) {
      window.clearTimeout(this.filterTimer);
      this.filterTimer = null;
    }
  },
  watch: {
    searchInput(newValue: string) {
      this.onSearchInput(newValue);
    },
  },
  methods: {
    translate,
    dispatchPanelEvent(eventName: string, detail?: Record<string, unknown>) {
      if (!this.panelContainer) {
        return;
      }

      this.panelContainer.dispatchEvent(new CustomEvent(eventName, {
        bubbles: true,
        detail,
      }));
    },
    togglePanel() {
      this.dispatchPanelEvent('SegmentEditor:toggle-panel');
    },
    selectSegment(entry: SegmentSelectorEntry) {
      if (entry.type !== 'segment') {
        return;
      }

      if (!entry.definition && entry.definition !== '') {
        return;
      }

      this.dispatchPanelEvent('SegmentEditor:select-segment', { definition: entry.definition });
    },
    toggleStar(entry: SegmentSelectorEntry) {
      if (entry.starState === 'disabled' || !entry.idsegment) {
        return;
      }

      SegmentSelectorStore.toggleStarredSegmentById(entry.idsegment);
    },
    toggleComparison(entry: SegmentSelectorEntry) {
      if (entry.compareState === 'disabled' || typeof entry.definition === 'undefined') {
        return;
      }
      this.dispatchPanelEvent('SegmentEditor:toggle-comparison', { definition: entry.definition });
    },
    openEditSegment(entry: SegmentSelectorEntry) {
      if (entry.editState === 'disabled' || !entry.idsegment) {
        return;
      }
      this.dispatchPanelEvent('SegmentEditor:open-edit-segment', { idSegment: entry.idsegment });
    },
    openAddSegment() {
      this.dispatchPanelEvent('SegmentEditor:open-add-segment');
    },
    getEntryClasses(entry: SegmentSelectorEntry) {
      const baseClasses = Array.isArray(entry.classes)
        ? entry.classes.join(' ')
        : (entry.classes || '');
      const animationClass = entry.idsegment ? this.starAnimationClasses[`${entry.idsegment}`] || '' : '';

      return [baseClasses, animationClass].filter(Boolean).join(' ');
    },
    clearStarAnimationClass(entry: SegmentSelectorEntry) {
      if (!entry.idsegment) {
        return;
      }

      const segmentId = `${entry.idsegment}`;
      if (!this.starAnimationClasses[segmentId]) {
        return;
      }

      const classes = { ...this.starAnimationClasses };
      delete classes[segmentId];
      this.starAnimationClasses = classes;
    },
    onSearchInput(value: string) {
      if (this.filterTimer) {
        window.clearTimeout(this.filterTimer);
      }

      this.filterTimer = window.setTimeout(() => {
        this.debouncedSearchInput = value;
        SegmentSelectorStore.notifyChange();
      }, 500);
    },
    clearSearch() {
      this.searchInput = '';
      this.debouncedSearchInput = '';

      if (this.filterTimer) {
        window.clearTimeout(this.filterTimer);
        this.filterTimer = null;
      }
      SegmentSelectorStore.notifyChange();
    },
  },
});
</script>
