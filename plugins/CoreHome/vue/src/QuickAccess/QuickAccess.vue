<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="root"
    class="quickAccessInside"
    v-focus-anywhere-but-here="{ blur: onBlur }"
  >
    <span
      class="icon-search"
      @mouseenter="searchActive = true"
    />
    <input
      class="s"
      @keydown="onKeypress($event)"
      @focus="searchActive = true"
      v-model="searchTerm"
      type="text"
      tabindex="2"
      v-focus-if="{ focused: searchActive }"
      :title="quickAccessTitle"
      :placeholder="translate('General_Search')"
      ref="input"
    />
    <div
      class="dropdown"
      v-show="searchTerm && searchActive"
    >
      <ul v-show="!(numMenuItems > 0 || sites.length)">
        <li class="no-result">{{ translate('General_SearchNoResults') }}</li>
      </ul>
      <ul v-for="subcategory in menuItems" :key="subcategory.title">
        <li
          class="quick-access-category"
          @click="searchTerm = subcategory.title;searchMenu(searchTerm)"
        >
          {{ subcategory.title }}
        </li>
        <li
          class="result"
          :class="{ selected: submenuEntry.menuIndex === searchIndex }"
          @mouseenter="searchIndex = submenuEntry.menuIndex"
          @click="selectMenuItem(submenuEntry.index)"
          v-for="submenuEntry in subcategory.items"
          :key="submenuEntry.index"
        >
          <a>{{ submenuEntry.name.trim() }}</a>
        </li>
      </ul>
      <ul class="quickAccessMatomoSearch">
        <li
          class="quick-access-category websiteCategory"
          v-show="hasSitesSelector && sites.length || isLoading"
        >
          {{ translate('SitesManager_Sites') }}
        </li>
        <li
          class="no-result"
          v-show="hasSitesSelector && isLoading"
        >
          {{ translate('MultiSites_LoadingWebsites') }}
        </li>
        <li
          class="result"
          v-for="(site, index) in sites"
          v-show="hasSitesSelector && !isLoading"
          @mouseenter="searchIndex = numMenuItems + index"
          :class="{ selected: numMenuItems + index === searchIndex }"
          @click="selectSite(site.idsite)"
          :key="site.idsite"
        >
          <a v-text="site.name" />
        </li>
      </ul>
      <ul>
        <li class="quick-access-category helpCategory">{{ translate('General_HelpResources') }}</li>
        <li
          :class="{ selected: searchIndex === 'help' }"
          class="quick-access-help"
          @mouseenter="searchIndex = 'help'"
        >
          <a
            :href="`https://matomo.org?mtm_campaign=App_Help&mtm_source=Matomo_App&mtm_keyword=QuickSearch&s=${encodeURIComponent(searchTerm)}`"
            target="_blank"
          >
            {{ translate('CoreHome_SearchOnMatomo', searchTerm) }}
          </a>
        </li>
      </ul>
    </div>
  </div>
</template>

<script lang="ts">
import { DeepReadonly, defineComponent } from 'vue';
import FocusAnywhereButHere from '../FocusAnywhereButHere/FocusAnywhereButHere';
import FocusIf from '../FocusIf/FocusIf';
import { translate } from '../translate';
import SitesStore from '../SiteSelector/SitesStore';
import Site from '../SiteSelector/Site';
import Matomo from '../Matomo/Matomo';
import debounce from '../debounce';

interface SubMenuItem {
  name: string;
  index: number;
  category: string;
  menuIndex?: number;
}

interface MenuItem {
  title: string;
  items: SubMenuItem[];
}

interface QuickAccessState {
  menuItems: Array<unknown>;
  numMenuItems: number;
  searchActive: boolean;
  searchTerm: string;
  searchIndex: number;

  menuIndexCounter: number;
  topMenuItems: SubMenuItem[]|null;
  leftMenuItems: SubMenuItem[]|null;
  segmentItems: SubMenuItem[]|null;
  hasSegmentSelector: boolean;

  sites: DeepReadonly<Site[]>;
  isLoading: boolean;
}

function isElementInViewport(element: HTMLElement) {
  const rect = element.getBoundingClientRect();
  const $window = window.$(window);

  return rect.top >= 0
    && rect.left >= 0
    && rect.bottom <= $window.height()!
    && rect.right <= $window.width()!;
}

function scrollFirstElementIntoView(element: HTMLElement) {
  if (element && element.scrollIntoView) {
    // make sure search is visible
    element.scrollIntoView();
  }
}

export default defineComponent({
  directives: {
    FocusAnywhereButHere,
    FocusIf,
  },
  watch: {
    searchActive(newValue: boolean) {
      const root = this.$refs.root as HTMLElement;
      if (!root || !root.parentElement) {
        return;
      }

      const classes = root.parentElement.classList;
      classes.toggle('active', newValue);
      classes.toggle('expanded', newValue);
    },
  },
  mounted() {
    const root = this.$refs.root as HTMLElement;

    // TODO: temporary, remove after angularjs is removed.
    // this is currently needed since angularjs will render a div, then vue will render a div
    // within it, but the top controls and CSS expect to have certain CSS classes in the root
    // element.
    // same applies to above watch for searchActive()
    if (root && root.parentElement) {
      root.parentElement.classList.add('quick-access', 'piwikSelector');
    }

    if (typeof window.initTopControls !== 'undefined' && window.initTopControls) {
      window.initTopControls();
    }

    Matomo.helper.registerShortcut('f', translate('CoreHome_ShortcutSearch'), (event) => {
      if (event.altKey) {
        return;
      }

      event.preventDefault();

      scrollFirstElementIntoView(this.$refs.root as HTMLElement);

      this.activateSearch();
    });
  },
  data(): QuickAccessState {
    const hasSegmentSelector = !!document.querySelector('.segmentEditorPanel');

    return {
      menuItems: [],
      numMenuItems: 0,
      searchActive: false,
      searchTerm: '',
      searchIndex: 0,
      menuIndexCounter: -1,
      topMenuItems: null,
      leftMenuItems: null,
      segmentItems: null,
      hasSegmentSelector,
      sites: [],
      isLoading: false,
    };
  },
  created() {
    this.searchMenu = debounce(this.searchMenu.bind(this));
  },
  computed: {
    hasSitesSelector() {
      return !!document.querySelector('.top_controls [piwik-siteselector]');
    },
    quickAccessTitle() {
      let searchAreasTitle = '';
      const searchAreas = [translate('CoreHome_MenuEntries')];

      if (this.hasSegmentSelector) {
        searchAreas.push(translate('CoreHome_Segments'));
      }

      if (this.hasSitesSelector) {
        searchAreas.push(translate('SitesManager_Sites'));
      }

      while (searchAreas.length) {
        searchAreasTitle += searchAreas.shift();
        if (searchAreas.length >= 2) {
          searchAreasTitle += ', ';
        } else if (searchAreas.length === 1) {
          searchAreasTitle += ` ${translate('General_And')} `;
        }
      }

      return translate('CoreHome_QuickAccessTitle', searchAreasTitle);
    },
  },
  emits: ['itemSelected', 'blur'],
  methods: {
    onKeypress(event: KeyboardEvent) {
      const areSearchResultsDisplayed = this.searchTerm && this.searchActive;
      const isTabKey = event.which === 9;
      const isEscKey = event.which === 27;

      if (event.which === 38) {
        this.highlightPreviousItem();
        event.preventDefault();
      } else if (event.which === 40) {
        this.highlightNextItem();
        event.preventDefault();
      } else if (event.which === 13) {
        this.clickQuickAccessMenuItem();
      } else if (isTabKey && areSearchResultsDisplayed) {
        this.deactivateSearch();
      } else if (isEscKey && areSearchResultsDisplayed) {
        this.deactivateSearch();
      } else {
        setTimeout(() => {
          this.searchActive = true;
          this.searchMenu(this.searchTerm);
        });
      }
    },
    highlightPreviousItem() {
      if ((this.searchIndex - 1) < 0) {
        this.searchIndex = 0;
      } else {
        this.searchIndex -= 1;
      }
      this.makeSureSelectedItemIsInViewport();
    },
    highlightNextItem() {
      const numTotal = (this.$refs.root as HTMLElement).querySelectorAll('li.result').length;

      if (numTotal <= (this.searchIndex + 1)) {
        this.searchIndex = numTotal - 1;
      } else {
        this.searchIndex += 1;
      }

      this.makeSureSelectedItemIsInViewport();
    },
    clickQuickAccessMenuItem() {
      const selectedMenuElement = this.getCurrentlySelectedElement();
      if (selectedMenuElement) {
        setTimeout(() => {
          selectedMenuElement.click();
          this.$emit('itemSelected', selectedMenuElement);
        }, 20);
      }
    },
    deactivateSearch() {
      this.searchTerm = '';
      this.searchActive = false;
      if (this.$refs.input) {
        (this.$refs.input as HTMLElement).blur();
      }
    },
    makeSureSelectedItemIsInViewport() {
      const element = this.getCurrentlySelectedElement();

      if (element && !isElementInViewport(element)) {
        scrollFirstElementIntoView(element);
      }
    },
    getCurrentlySelectedElement(): HTMLElement|undefined {
      const results = (this.$refs.root as HTMLElement).querySelectorAll('li.result');
      if (results && results.length && results.item(this.searchIndex)) {
        return results.item(this.searchIndex) as HTMLElement;
      }
      return undefined;
    },
    searchMenu(unprocessedSearchTerm: string) {
      const searchTerm = unprocessedSearchTerm.toLowerCase();

      let index = -1;
      const menuItemsIndex: Record<string, number> = {};
      const menuItems: MenuItem[] = [];

      const moveToCategory = (theSubmenuItem: SubMenuItem) => {
        // force rerender of element to prevent weird side effects
        const submenuItem = { ...theSubmenuItem };
        // needed for proper highlighting with arrow keys
        index += 1;
        submenuItem.menuIndex = index;

        const { category } = submenuItem;
        if (!(category in menuItemsIndex)) {
          menuItems.push({ title: category, items: [] });
          menuItemsIndex[category] = menuItems.length - 1;
        }

        const indexOfCategory = menuItemsIndex[category];
        menuItems[indexOfCategory].items.push(submenuItem);
      };

      this.resetSearchIndex();

      if (this.hasSitesSelector) {
        this.isLoading = true;
        SitesStore.searchSite(searchTerm).then((sites) => {
          if (sites) {
            this.sites = sites;
          }
        }).finally(() => {
          this.isLoading = false;
        });
      }

      const menuItemMatches = (i: SubMenuItem) => i.name.toLowerCase().indexOf(searchTerm) !== -1
        || i.category.toLowerCase().indexOf(searchTerm) !== -1;

      // get the menu items on first search since this component can be mounted
      // before the menus are
      if (this.topMenuItems === null) {
        this.topMenuItems = this.getTopMenuItems();
      }
      if (this.leftMenuItems === null) {
        this.leftMenuItems = this.getLeftMenuItems();
      }
      if (this.segmentItems === null) {
        this.segmentItems = this.getSegmentItems();
      }

      const topMenuItems = this.topMenuItems.filter(menuItemMatches);
      const leftMenuItems = this.leftMenuItems.filter(menuItemMatches);
      const segmentItems = this.segmentItems.filter(menuItemMatches);

      topMenuItems.forEach(moveToCategory);
      leftMenuItems.forEach(moveToCategory);
      segmentItems.forEach(moveToCategory);

      this.numMenuItems = topMenuItems.length + leftMenuItems.length + segmentItems.length;
      this.menuItems = menuItems;
    },
    resetSearchIndex() {
      this.searchIndex = 0;
      this.makeSureSelectedItemIsInViewport();
    },
    selectSite(idSite: string|number) {
      SitesStore.loadSite(idSite);
    },
    selectMenuItem(index: number) {
      const target: HTMLElement|null = document.querySelector(`[quick_access='${index}']`);
      if (target) {
        this.deactivateSearch();

        const href = target.getAttribute('href');
        if (href && href.length > 10 && target && target.click) {
          try {
            target.click();
          } catch (e) {
            window.$(target).click();
          }
        } else {
          // not sure why jquery is used here and above, but only sometimes. keeping for BC.
          window.$(target).click();
        }
      }
    },
    onBlur() {
      this.searchActive = false;
      this.$emit('blur');
    },
    activateSearch() {
      this.searchActive = true;
    },
    getTopMenuItems() {
      const category = translate('CoreHome_Menu');

      const topMenuItems: SubMenuItem[] = [];
      document.querySelectorAll('nav .sidenav li > a').forEach((element) => {
        let text = element.textContent?.trim();

        if (!text) {
          text = element.getAttribute('title')?.trim(); // possibly a icon, use title instead
        }

        if (text) {
          topMenuItems.push({ name: text, index: this.menuIndexCounter += 1, category });
          element.setAttribute('quick_access', `${this.menuIndexCounter}`);
        }
      });

      return topMenuItems;
    },
    getLeftMenuItems() {
      const leftMenuItems: SubMenuItem[] = [];

      document.querySelectorAll('#secondNavBar .menuTab').forEach((element) => {
        const categoryElement = window.$(element).find('> .item');
        let category = categoryElement[0]?.innerText.trim() || '';

        if (category && category.lastIndexOf('\n') !== -1) {
          // remove "\n\nMenu"
          category = category.slice(0, category.lastIndexOf('\n')).trim();
        }

        window.$(element).find('li .item').each((i, subElement) => {
          const text = subElement.textContent?.trim();
          if (text) {
            leftMenuItems.push({ name: text, category, index: this.menuIndexCounter += 1 });
            subElement.setAttribute('quick_access', `${this.menuIndexCounter}`);
          }
        });
      });

      return leftMenuItems;
    },
    getSegmentItems() {
      if (!this.hasSegmentSelector) {
        return [];
      }

      const category = translate('CoreHome_Segments');

      const segmentItems: SubMenuItem[] = [];
      document.querySelectorAll('.segmentList [data-idsegment]').forEach((element) => {
        const text = element.querySelector('.segname')?.textContent?.trim();

        if (text) {
          segmentItems.push({ name: text, category, index: this.menuIndexCounter += 1 });
          element.setAttribute('quick_access', `${this.menuIndexCounter}`);
        }
      });

      return segmentItems;
    },
  },
});
</script>
