<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<!-- TODO: add translations client side -->
<template>
  <ul id="mobile-left-menu" class="sidenav hide-on-large-only">
    <li class="no-padding" v-for="(level2, level1) in menuWithSubmenuItems" :key="level1">
      <ul class="collapsible collapsible-accordion" v-side-nav="{expander: activateLeftMenu}">
        <li>
          <a class="collapsible-header">
            {{ translate(level1) }}<i :class="level2._icon || 'icon-arrow-down'"></i>
          </a>

          <div class="collapsible-body">
            <ul>
              <li v-for="(name, urlParameters) in level2">
                <a
                  :title="urlParameters._tooltip ? translate(urlParameters._tooltip) : ''"
                  target="_self"
                  :href="getMenuUrl(urlParameters._url)"
                >
                  {{ translate(name) }}
                </a>
              </li>
            </ul>
          </div>
        </li>
      </ul>
    </li>
  </ul>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import SideNav from '../SideNav/SideNav';

interface UrlParamsInfo {

}

type Menu = Record<string, Record<string, UrlParamsInfo>>;

const { $ } = window;

export default defineComponent({
  props: {
    menu: {
      type: Object,
      required: true,
    },
  },
  directives: {
    SideNav,
  },
  methods: {
    getMenuUrl(params: QueryParameters) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        ...params,
      })}`;
    },
  },
  computed: {
    menuWithSubmenuItems() {
      const menu = this.menu as Menu;
      return Object.fromEntries(
        Object.entries(menu)
          // remove submenu items that start with '_'
          .map(([level1, level2]) => [
            level1,
            Object.fromEntries(Object.entries(level2).filter(([name]) => name[0] === '_')),
          ])
          // remove submenus that no longer have items
          .filter(([, level2]) => Object.keys(level2).length),
      );
    },
    activateLeftMenu() {
      return $('nav .activateLeftMenu')[0];
    },
  },
});
</script>
