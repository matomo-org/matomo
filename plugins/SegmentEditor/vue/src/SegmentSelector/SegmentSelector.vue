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
          <SearchInput
            tabindex="4"
            v-model="searchInput"
            :show-clear="true"
            @update:model-value="onSearchInputUpdate"
          />
        </div>
        <ul class="submenu">
          <li>
            <span class="segment-visits-label">
              {{ translate('SegmentEditor_SelectSegmentOfVisits') }}
            </span>
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
                        <StarIcon :filled="!!entry.isStarred" />
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
                      >
                        <CompareIcon :state="entry.compareState" />
                      </button>
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
            class="btn btn-block btn-outline manage_segment_btn"
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
              </span>
              <a
                v-if="viewModel.isUserAnonymous"
                :href="viewModel.loginUrl"
                tabindex="4"
                class="sign_in_segment_btn btn"
              >
                {{ translate('Login_LogIn') }}
              </a>
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
import { SearchInput, translate } from 'CoreHome';
import CompareIcon from './CompareIcon.vue';
import StarIcon from './StarIcon.vue';
import SegmentSelectorStore from './SegmentSelector.store';
import {
  SegmentSelectorEntry,
  SegmentSelectorViewModel,
} from '../types';

export default defineComponent({
  name: 'SegmentSelector',
  components: {
    CompareIcon,
    SearchInput,
    StarIcon,
  },
  data() {
    return {
      filterTimer: null as ReturnType<typeof window.setTimeout> | null,
      panelContainer: null as HTMLElement | null,
      searchInput: '',
      debouncedSearchInput: '',
      starAnimationClasses: {} as Record<string, string>,
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
    onSearchInputUpdate(value: string) {
      if (!value) {
        this.clearSearch();
      }
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
