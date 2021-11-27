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
        v-for="category in menuModel.menu"
        :class="{ 'active': category.active }"
        :key="category.id"
      >
        <a
          class="item"
          tabindex="5"
          href=""
          @click="loadCategory(category)"
        >
          <span
            :class="`menu-icon ${category.icon ? category.icon : 'icon-arrow-right'}`"
          />
          {{ category.name }}
          <span class="hidden">
            {{ translate('CoreHome_Menu') }}
          </span>
        </a>
        <ul role="menu">
          <li
            role="menuitem"
            :class="{'active': subcategory.active}"
            v-for="subcategory in category.subcategories"
            :key="subcategory.id"
          >
            <div
              v-if="subcategory.isGroup"
              piwik-menudropdown=""
              :show-search="true"
              :menu-title="$sanitize(subcategory.name)"
            >
              <a
                class="item"
                tabindex="5"
                :class="{active: subcat.active}"
                :href="`#?${makeUrl(category, subcat)}`"
                @click="loadSubcategory(category, subcat)"
                v-for="subcat in subcategory.subcategories"
                :title="subcat.tooltip"
                :key="subcat.id"
              >
                {{ subcat.name }}
              </a>
            </div>
            <a
              v-if="!subcategory.isGroup"
              :href="`#?${makeUrl(category, subcategory)}`"
              class="item"
              @click="loadSubcategory(category, subcategory)"
            >
              {{ subcategory.name }}
            </a>
            <a
              class="item-help-icon"
              tabindex="5"
              href="javascript:"
              v-if="subcategory.help"
              @click="showHelp(category, subcategory, $event)"
              :class="{active: helpShownCategory === subcategory && subcategory.help}"
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
        v-for="category in menuModel.menu"
        :key="category.id"
      >
        <ul
          class="collapsible collapsible-accordion"
          v-side-nav="{ activator: nav.activateLeftMenu }"
        >
          <li>
            <a class="collapsible-header"><i :class="category.icon ? category.icon : 'icon-arrow-bottom'" />{{ category.name }}</a>
            <div class="collapsible-body">
              <ul>
                <li v-for="subcategory in category.subcategories">
                  <a
                    v-if="subcategory.isGroup"
                    @click="loadSubcategory(category, subcat)"
                    :href="`#?${makeUrl(category, subcat)}`"
                    v-for="subcat in subcategory.subcategories"
                    :key="subcat.id"
                  >
                    {{ subcat.name }}
                  </a>
                  <a
                    v-if="!subcategory.isGroup"
                    @click="loadSubcategory(category, subcategory)"
                    :href="`#?${makeUrl(category, subcategory)}`"
                  >
                    {{ subcategory.name }}
                  </a>
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
import { defineComponent } from 'vue';
import MenuDropdown from '../MenuDropdown/MenuDropdown.vue';
import SideNav from '../SideNav/SideNav';

export default defineComponent({
  props: {},
  data() {
    return {
      showSubcategoryHelpOnLoad: null,
      initialLoad: true,
      helpShownCategory: null,
      // TODO
    };
  },
  methods: {
    loadCategory(category: Category) {
      // TODO
    },
    loadSubcategory(category: Category, subcategory: Subcategory) {
      // TODO
    },
    makeUrl(category: Category, subcategory: Subcategory) {
      // TODO
    },
    showHelp(category: Category, subcategory: Subcategory, event: Event) {
      // TODO
    },
  },
});
</script>
