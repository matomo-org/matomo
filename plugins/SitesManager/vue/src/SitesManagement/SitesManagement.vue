<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="SitesManager">
    <div class="sites-manager-header">
      <div piwik-content-intro>
        <h2
          ng-show="availableTypes"
          piwik-enriched-headline
          help-url="https://matomo.org/docs/manage-websites/"
          feature-name="{{ 'SitesManager_WebsitesManagement'|translate }}">
          {{ 'SitesManager_XManagement'|translate:(availableTypes.length > 1 ? ('General_Measurables'|translate) : ('SitesManager_Sites'|translate)) }}
        </h2>

        <p>
          {{ 'SitesManager_MainDescription'|translate }}

          <span ng-bind-html="'SitesManager_YouCurrentlyHaveAccessToNWebsites'|translate:'<strong>' + totalNumberOfSites + '</strong>'"></span>

          <span ng-show="hasSuperUserAccess">
            <br/>
            <span ng-bind-html="'SitesManager_SuperUserAccessCan'|translate:'<a href=\'#globalSettings\'>':'</a>'"></span>
        </span>
        </p>
      </div>
    </div>
    <div>
      <div ng-class="{'hide_only': !loading && !adminSites.isLoading}">
        <div class="loadingPiwik">
          <img src="plugins/Morpheus/images/loading-blue.gif" alt="{{ 'General_LoadingData'|translate }}" />
          {{ 'General_LoadingData'|translate }}
        </div>
      </div>
    </div>
    <div ng-include="'plugins/SitesManager/templates/sites-list/add-site-link.html?cb=' + cacheBuster"></div>
    <div ng-include="'plugins/SitesManager/templates/sites-list/add-entity-dialog.html?cb=' + cacheBuster"></div>
    <div ng-include="'plugins/SitesManager/templates/sites-list/sites-list.html?cb=' + cacheBuster"></div>
    <div class="bottomButtonBar" ng-include="'plugins/SitesManager/templates/sites-list/add-site-link.html?cb=' + cacheBuster"></div>
  </div>

</template>

<script>
import { defineComponent } from 'vue';
import translate from '../translate';

export default defineComponent({
  props: {
    // TODO
  },
});
</script>
