<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="reportingMenu">
    <ul
      class="navbar hide-on-med-and-down"
      role="menu"
      :aria-label="translate('CoreHome_MainNavigation')"
    >
      <li
        class="menuTab"
        role="menuitem"
        v-for="category in menu"
        :class="{ 'active': category.id === activeCategory }"
        :key="category.id"
      >
        <a
          class="item"
          tabindex="5"
          href=""
          @click.prevent="loadCategory(category)"
        >
          <span
            :class="`menu-icon ${category.icon ? category.icon : 'icon-arrow-right'}`"
          />{{ category.name }}
          <span class="hidden">
            {{ translate('CoreHome_Menu') }}
          </span>
        </a>
        <ul role="menu">
          <li
            role="menuitem"
            :class="{
              'active': (subcategory.id === displayedSubcategory
                || (subcategory.isGroup && activeSubsubcategory === displayedSubcategory)
              ) && category.id === displayedCategory,
            }"
            v-for="subcategory in category.subcategories"
            :key="subcategory.id"
          >
            <MenuItemsDropdown
              v-if="subcategory.isGroup"
              :show-search="true"
              :menu-title="htmlEntities(subcategory.name)"
            >
              <a
                class="item"
                tabindex="5"
                :class="{
                  active: subcat.id === activeSubsubcategory
                    && subcategory.id === displayedSubcategory
                    && category.id === displayedCategory,
                }"
                :href="`#?${makeUrl(category, subcat)}`"
                @click="loadSubcategory(category, subcat, $event)"
                v-for="subcat in subcategory.subcategories"
                :title="subcat.tooltip"
                :key="subcat.id"
              >
                {{ subcat.name }}
              </a>
            </MenuItemsDropdown>
            <a
              v-if="!subcategory.isGroup"
              :href="`#?${makeUrl(category, subcategory)}`"
              class="item"
              @click="loadSubcategory(category, subcategory, $event)"
            >
              {{ subcategory.name }}
            </a>
            <a
              class="item-help-icon"
              tabindex="5"
              href="javascript:"
              v-if="subcategory.help"
              @click="showHelp(category, subcategory, $event)"
              :class="{active: helpShownCategory
                && helpShownCategory.subcategory === subcategory.id
                && helpShownCategory.category === category.id
                && subcategory.help}"
            >
              <span class="icon-help" />
            </a>
          </li>
        </ul>
      </li>
    </ul>
    <ul
      id="mobile-left-menu"
      class="sidenav hide-on-large-only"
    >
      <li
        class="no-padding"
        v-for="category in menu"
        :key="category.id"
      >
        <ul
          class="collapsible collapsible-accordion"
          v-side-nav="{ activator: sideNavActivator }"
        >
          <li>
            <a class="collapsible-header">
              <i :class="category.icon ? category.icon : 'icon-arrow-bottom'" />{{ category.name }}
            </a>
            <div class="collapsible-body">
              <ul>
                <li v-for="subcategory in category.subcategories" :key="subcategory.id">
                  <span v-if="subcategory.isGroup">
                    <a
                      @click="loadSubcategory(category, subcat)"
                      :href="`#?${makeUrl(category, subcat)}`"
                      v-for="subcat in subcategory.subcategories"
                      :key="subcat.id"
                    >
                      {{ subcat.name }}
                    </a>
                  </span>
                  <span v-if="!subcategory.isGroup">
                    <a
                      @click="loadSubcategory(category, subcategory)"
                      :href="`#?${makeUrl(category, subcategory)}`"
                    >
                      {{ subcategory.name }}
                    </a>
                  </span>
                </li>
              </ul>
            </div>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { defineComponent, watch } from 'vue';
import MenuItemsDropdown from '../MenuItemsDropdown/MenuItemsDropdown.vue';
import SideNav from '../SideNav/SideNav';
import { NotificationsStore } from '../Notification';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import ReportingMenuStoreInstance from './ReportingMenu.store';
import Matomo from '../Matomo/Matomo';
import { translate } from '../translate';
import WidgetsStoreInstance from '../Widget/Widgets.store';
import { Category, CategoryContainer } from './Category';
import { Subcategory, SubcategoryContainer } from './Subcategory';

const REPORTING_HELP_NOTIFICATION_ID = 'reportingmenu-help';

interface ReportingMenuState {
  showSubcategoryHelpOnLoad: { category: Category, subcategory: Subcategory } | null;
  initialLoad: boolean | null;
  helpShownCategory: { category: string, subcategory: string } | null;
}

export default defineComponent({
  components: {
    MenuItemsDropdown,
  },
  directives: {
    SideNav,
  },
  props: {},
  data(): ReportingMenuState {
    return {
      showSubcategoryHelpOnLoad: null,
      initialLoad: true,
      helpShownCategory: null,
    };
  },
  computed: {
    sideNavActivator() {
      return document.querySelector('nav .activateLeftMenu');
    },
    menu() {
      return ReportingMenuStoreInstance.menu.value;
    },
    activeCategory() {
      return ReportingMenuStoreInstance.activeCategory.value;
    },
    activeSubcategory() {
      return ReportingMenuStoreInstance.activeSubcategory.value;
    },
    activeSubsubcategory() {
      return ReportingMenuStoreInstance.activeSubsubcategory.value;
    },
    displayedCategory() {
      return MatomoUrl.parsed.value.category;
    },
    displayedSubcategory() {
      return MatomoUrl.parsed.value.subcategory;
    },
  },
  created() {
    ReportingMenuStoreInstance.fetchMenuItems().then((menu) => {
      if (!MatomoUrl.parsed.value.subcategory) {
        const categoryToLoad = menu[0];
        const subcategoryToLoad = (categoryToLoad as CategoryContainer).subcategories[0];

        // load first, initial page if no subcategory is present
        ReportingMenuStoreInstance.enterSubcategory(categoryToLoad, subcategoryToLoad);
        this.propagateUrlChange(categoryToLoad, subcategoryToLoad);
      }
    });

    watch(() => MatomoUrl.parsed.value, (query) => {
      const found = ReportingMenuStoreInstance.findSubcategory(
        query.category as string,
        query.subcategory as string,
      );

      ReportingMenuStoreInstance.enterSubcategory(
        found.category,
        found.subcategory,
        found.subsubcategory,
      );
    });

    Matomo.on('piwikPageChange', () => {
      if (!this.initialLoad) {
        window.globalAjaxQueue.abort();
      }

      this.helpShownCategory = null;

      if (this.showSubcategoryHelpOnLoad) {
        this.showHelp(
          this.showSubcategoryHelpOnLoad.category,
          this.showSubcategoryHelpOnLoad.subcategory,
        );
        this.showSubcategoryHelpOnLoad = null;
      }

      window.$('#loadingError,#loadingRateLimitError').hide();

      this.initialLoad = false;
    });

    Matomo.on('updateReportingMenu', () => {
      ReportingMenuStoreInstance.reloadMenuItems().then(() => {
        const category = MatomoUrl.parsed.value.category as string;
        const subcategory = MatomoUrl.parsed.value.subcategory as string;

        // we need to make sure to select same categories again
        if (category && subcategory) {
          const found = ReportingMenuStoreInstance.findSubcategory(category, subcategory);
          if (found.category) {
            ReportingMenuStoreInstance.enterSubcategory(
              found.category,
              found.subcategory,
              found.subsubcategory,
            );
          }
        }
      });

      WidgetsStoreInstance.reloadAvailableWidgets();
    });
  },
  methods: {
    propagateUrlChange(category: Category, subcategory: Subcategory) {
      const queryParams = MatomoUrl.parsed.value;
      if (queryParams.category === category.id && queryParams.subcategory === subcategory.id) {
        // we need to manually trigger change as URL would not change and therefore page would not
        // be reloaded
        this.loadSubcategory(category, subcategory);
      } else {
        MatomoUrl.updateHash({
          ...MatomoUrl.hashParsed.value,
          category: category.id,
          subcategory: subcategory.id,
        });
      }
    },
    loadCategory(category: Category) {
      NotificationsStore.remove(REPORTING_HELP_NOTIFICATION_ID);

      const isActive = ReportingMenuStoreInstance.toggleCategory(category);
      if (isActive
        && (category as SubcategoryContainer).subcategories
        && (category as SubcategoryContainer).subcategories.length === 1
      ) {
        this.helpShownCategory = null;

        const subcategory = (category as SubcategoryContainer).subcategories[0];
        this.propagateUrlChange(category, subcategory);
      }
    },
    loadSubcategory(category: Category, subcategory: Subcategory, event?: MouseEvent) {
      if (event
        && (event.shiftKey || event.ctrlKey || event.metaKey)
      ) {
        return;
      }

      NotificationsStore.remove(REPORTING_HELP_NOTIFICATION_ID);

      if (subcategory && subcategory.id === this.activeSubcategory) {
        this.helpShownCategory = null;

        // this menu item is already active, a location change success would not be triggered,
        // instead trigger an event (after the URL changes)
        setTimeout(() => {
          Matomo.postEvent('loadPage', category.id, subcategory.id);
        });
      }
    },
    makeUrl(category: Category, subcategory: Subcategory) {
      const {
        idSite,
        period,
        date,
        segment,
        comparePeriods,
        compareDates,
        compareSegments,
      } = MatomoUrl.parsed.value;

      return MatomoUrl.stringify({
        idSite,
        period,
        date,
        segment,
        comparePeriods,
        compareDates,
        compareSegments,
        category: category.id,
        subcategory: subcategory.id,
      });
    },
    htmlEntities(v: string) {
      return Matomo.helper.htmlEntities(v);
    },
    showHelp(category: Category, subcategory: Subcategory, event?: Event) {
      const parsedUrl = MatomoUrl.parsed.value;
      const currentCategory = parsedUrl.category;
      const currentSubcategory = parsedUrl.subcategory;

      if ((currentCategory !== category.id
        || currentSubcategory !== subcategory.id)
        && event
      ) {
        this.showSubcategoryHelpOnLoad = { category, subcategory };
        MatomoUrl.updateHash({
          ...MatomoUrl.hashParsed.value,
          category: category.id,
          subcategory: subcategory.id,
        });
        return;
      }

      if (this.helpShownCategory
        && category.id === this.helpShownCategory.category
        && subcategory.id === this.helpShownCategory.subcategory
      ) {
        NotificationsStore.remove(REPORTING_HELP_NOTIFICATION_ID);
        this.helpShownCategory = null;
        return;
      }

      const prefixText = translate('CoreHome_ReportingCategoryHelpPrefix',
        category.name, subcategory.name);
      const prefix = `<strong>${prefixText}</strong><br/>`;

      NotificationsStore.show({
        context: 'info',
        id: REPORTING_HELP_NOTIFICATION_ID,
        type: 'help',
        noclear: true,
        class: 'help-notification',
        message: prefix + subcategory.help,
        placeat: '#notificationContainer',
        prepend: true,
      });

      this.helpShownCategory = {
        category: category.id,
        subcategory: subcategory.id,
      };
    },
  },
});
</script>
