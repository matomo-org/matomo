<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="sitesButtonBar clearfix">

    <a v-show="hasSuperUserAccess && availableTypes"
       class="btn addSite"
       :class="{ disabled: siteIsBeingEdited }"
       @click="addNewEntity()"
       tabindex="1"
    >
      {{ availableTypes.length > 1
        ? translate('SitesManager_AddMeasurable')
        : translate('SitesManager_AddSite') }}
    </a>

    <div class="search" v-show="hasPrev || hasNext || isSearching">
      <input
        :value="searchTerm"
        @keydown="onKeydown($event)"
        :placeholder="translate('Actions_SubmenuSitesearch')"
        type="text"
        :disabled="siteIsBeingEdited"
      />
      <img
        @click="searchSite()"
        :title="translate('General_ClickToSearch')"
        class="search_ico"
        src="plugins/Morpheus/images/search_ico.png"
      />
    </div>

    <div class="paging" v-show="hasPrev || hasNext">
      <a
        class="btn prev"
        :disabled="(hasPrev && !isLoading) && !siteIsBeingEdited ? undefined : true"
        @click="previousPage()"
      >
        <span style="cursor:pointer;">&#171; {{ translate('General_Previous') }}</span>
      </a>
      <span class="counter" v-show="hasPrev || hasNext">
            <span>
              {{ paginationText }}
            </span>
        </span>
      <a
        class="btn next"
        :disabled="(hasNext && !isLoading) && !siteIsBeingEdited ? undefined : true"
        @click="nextPage()"
      >
        <span style="cursor:pointer;" class="pointer">{{ translate('General_Next') }} &#187;</span>
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Matomo, translate, debounce } from 'CoreHome';
import SiteTypesStore from '../SiteTypesStore/SiteTypesStore';

export default defineComponent({
  props: {
    siteIsBeingEdited: {
      type: Boolean,
      required: true,
    },
    hasPrev: {
      type: Boolean,
      required: true,
    },
    hasNext: {
      type: Boolean,
      required: true,
    },
    offsetStart: {
      type: Number,
      required: true,
    },
    offsetEnd: {
      type: Number,
      required: true,
    },
    totalNumberOfSites: {
      type: Number,
    },
    isLoading: {
      type: Boolean,
      required: true,
    },
    searchTerm: {
      type: String,
      required: true,
    },
    isSearching: {
      type: Boolean,
      required: true,
    },
  },
  emits: ['add', 'search', 'prev', 'next', 'update:searchTerm'],
  created() {
    SiteTypesStore.init();

    this.onKeydown = debounce(this.onKeydown, 50);
  },
  computed: {
    hasSuperUserAccess() {
      return Matomo.hasSuperUserAccess;
    },
    availableTypes() {
      return SiteTypesStore.types.value;
    },
    paginationText() {
      let text: string;
      if (this.isSearching) {
        text = translate(
          'General_PaginationWithoutTotal',
          `${this.offsetStart}`,
          `${this.offsetEnd}`,
        );
      } else {
        text = translate(
          'General_Pagination',
          `${this.offsetStart}`,
          `${this.offsetEnd}`,
          this.totalNumberOfSites === null ? '?' : `${this.totalNumberOfSites}`,
        );
      }
      return ` ${text} `;
    },
  },
  methods: {
    addNewEntity() {
      this.$emit('add');
    },
    searchSite() {
      if (this.siteIsBeingEdited) {
        return;
      }

      this.$emit('search');
    },
    previousPage() {
      this.$emit('prev');
    },
    nextPage() {
      this.$emit('next');
    },
    onKeydown(event: KeyboardEvent) {
      setTimeout(() => {
        if (event.key === 'Enter') {
          this.searchSiteOnEnter(event);
          return;
        }

        this.$emit('update:searchTerm', (event.target as HTMLInputElement).value);
      });
    },
    searchSiteOnEnter(event: KeyboardEvent) {
      event.preventDefault();
      this.searchSite();
    },
  },
});
</script>
