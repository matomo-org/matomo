<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="root"
    class="menuDropdown"
    v-focus-anywhere-but-here="{ blur: lostFocus }"
  >
    <span
      class="title"
      @click="showItems = !showItems"
      :title="tooltip"
    >
      <span v-html="$sanitize(this.actualMenuTitle)" />
      <span class="icon-chevron-down" />
    </span>
    <div
      class="items"
      v-show="showItems"
    >
      <div
        class="search"
        v-if="showSearch && showItems"
      >
        <input
          type="text"
          v-model="searchTerm"
          v-focus-if="{ focused: showItems }"
          @keydown="onSearchTermKeydown($event)"
          :placeholder="translate('General_Search')"
        />
        <div
          v-show="!searchTerm"
          class="search_ico icon-search"
          :title="translate('General_Search')"
        />
        <div
          v-show="searchTerm"
          v-on:click="searchTerm = '';searchItems('')"
          class="reset icon-close"
          :title="translate('General_Clear')"
        />
      </div>
      <div v-on:click="selectItem($event)">
        <slot />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import FocusAnywhereButHere from '../FocusAnywhereButHere/FocusAnywhereButHere';
import FocusIf from '../FocusIf/FocusIf';

const { $ } = window;

export default defineComponent({
  props: {
    menuTitle: String,
    tooltip: String,
    showSearch: Boolean,
    menuTitleChangeOnClick: Boolean,
  },
  directives: {
    FocusAnywhereButHere,
    FocusIf,
  },
  emits: ['afterSelect'],
  watch: {
    menuTitle() {
      this.actualMenuTitle = this.menuTitle;
    },
  },
  data() {
    return {
      showItems: false,
      searchTerm: '',
      actualMenuTitle: this.menuTitle,
    };
  },
  methods: {
    lostFocus() {
      this.showItems = false;
    },
    selectItem(event: MouseEvent) {
      const targetClasses = (event.target as HTMLElement).classList;
      if (!targetClasses.contains('item')
        || targetClasses.contains('disabled')
        || targetClasses.contains('separator')
      ) {
        return;
      }

      if (this.menuTitleChangeOnClick) {
        this.actualMenuTitle = ((event.target as HTMLElement).textContent || '')
          .replace(/[\u0000-\u2666]/g, (c) => `&#${c.charCodeAt(0)};`); // eslint-disable-line
      }

      this.showItems = false;

      $(this.$slots.default!()[0]!.el as HTMLElement).find('.item').removeClass('active');
      targetClasses.add('active');

      this.$emit('afterSelect', event.target);
    },
    onSearchTermKeydown() {
      setTimeout(() => {
        this.searchItems(this.searchTerm);
      });
    },
    searchItems(unprocessedSearchTerm: string) {
      const searchTerm = unprocessedSearchTerm.toLowerCase();

      $(this.$refs.root as HTMLElement).find('.item').each((index: number, node: HTMLElement) => {
        const $node = $(node);

        if ($node.text().toLowerCase().indexOf(searchTerm) === -1) {
          $node.hide();
        } else {
          $node.show();
        }
      });
    },
  },
});
</script>
