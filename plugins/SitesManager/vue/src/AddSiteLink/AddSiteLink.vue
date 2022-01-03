<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-show="!siteIsBeingEdited" class="sitesButtonBar clearfix">

    <a v-show="hasSuperUserAccess && availableTypes"
       class="btn addSite"
       ng-click="addNewEntity()" tabindex="1">
      {{ availableTypes.length > 1 ? ('SitesManager_AddMeasurable'|translate) : ('SitesManager_AddSite'|translate) }}
    </a>

    <div class="search" ng-show="adminSites.hasPrev || adminSites.hasNext || adminSites.searchTerm">
      <input ng-model="adminSites.search" piwik-onenter="adminSites.searchSite(adminSites.search)"
             placeholder="{{ 'Actions_SubmenuSitesearch' | translate }}" type="text">
      <img ng-click="adminSites.searchSite(adminSites.search)" title="{{ 'General_ClickToSearch' | translate }}"
           class="search_ico" src="plugins/Morpheus/images/search_ico.png"/>
    </div>

    <div class="paging" ng-show="adminSites.hasPrev || adminSites.hasNext">
      <a class="btn prev" ng-disabled="!adminSites.hasPrev" ng-click="adminSites.previousPage()">
        <span style="cursor:pointer;">&#171; {{ 'General_Previous'|translate }}</span>
      </a>
      <span class="counter" ng-show="adminSites.hasPrev || adminSites.hasNext">
            <span ng-if="adminSites.searchTerm">
                {{ 'General_PaginationWithoutTotal'|translate:adminSites.offsetStart:adminSites.offsetEnd }}
            </span>
            <span ng-if="!adminSites.searchTerm">
                {{ 'General_Pagination'|translate:adminSites.offsetStart:adminSites.offsetEnd:totalNumberOfSites }}
            </span>
        </span>
      <a class="btn next" ng-disabled="!adminSites.hasNext" ng-click="adminSites.nextPage()">
        <span style="cursor:pointer;" class="pointer">{{ 'General_Next'|translate }} &#187;</span>
      </a>
    </div>

  </div>

</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    siteIsBeingEdited: {
      type: Boolean,
      required: true,
    },
    // TODO
  },
});
</script>
