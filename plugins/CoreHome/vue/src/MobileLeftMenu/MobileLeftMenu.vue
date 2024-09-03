<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ul id="mobile-left-menu" class="sidenav hide-on-large-only">
    <li class="no-padding" v-for="(level2, level1) in menuWithSubmenuItems" :key="level1">
      <ul class="collapsible collapsible-accordion" v-side-nav="{activator: activateLeftMenu}">
        <li>
          <a class="collapsible-header">
            {{ translateOrDefault(level1) }}<i :class="level2._icon || 'icon-chevron-down'"></i>
          </a>

          <div class="collapsible-body">
            <ul>
              <li
                v-for="([name, params]) in Object.entries(level2).filter(([n]) => n[0] !== '_')"
                :key="name"
              >
                <a
                  :title="params._tooltip ? translateIfNecessary(params._tooltip) : ''"
                  target="_self"
                  :href="getMenuUrl(params._url)"
                >
                  {{ translateIfNecessary(name) }}
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
import { translate } from '../translate';

interface UrlParamsInfo {
  _tooltip: string;
  _url: QueryParameters;
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
    translateIfNecessary(name: string) {
      if (name.includes('_')) {
        return translate(name);
      }

      return name;
    },
  },
  computed: {
    menuWithSubmenuItems() {
      const menu = (this.menu || {}) as Menu;
      return Object.fromEntries(
        Object.entries(menu)
          // remove submenus that have no items that do not start w/ '_'
          .filter(([, level2]) => {
            const itemsWithoutUnderscore = Object.entries(level2)
              .filter(([name]) => name[0] !== '_');
            return Object.keys(itemsWithoutUnderscore).length;
          }),
      );
    },
    activateLeftMenu() {
      return $('nav .activateLeftMenu')[0];
    },
  },
});
</script>
