<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

// TODO
<todo>
- conversion check (mistakes get fixed in quickmigrate)
- property types
- state types
- look over template
- look over component code
- get to build
- test in UI
- check uses:
  ./plugins/MobileMessaging/templates/macros.twig
  ./plugins/MobileMessaging/MobileMessaging.php
  ./plugins/MobileMessaging/angularjs/sms-provider-credentials.directive.js
  ./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue
- create PR
</todo>

<template>
  <ng-include src="getTemplateUrl()" />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from 'CoreHome';


interface smsProviderCredentialsState {
}

export default defineComponent({
  props: {
    provider: null, // TODO,
    credentials: null, // TODO,
  },
  created() {
    if (!ctrl) {
      return;
    } // view -> model
    // view -> model
    this.$watch('credentials', (val, oldVal) => {
      ctrl.$setViewValue(JSON.stringify(val));
    }, true); // unset credentials when new provider is shoosen
    // unset credentials when new provider is shoosen
    this.$watch('provider', (val, oldVal) => {
      if (val != oldVal) {
        this.credentials = {};
      }
    }, true);
    if (!ctrl) {
      return;
    } // view -> model
    // view -> model
    this.$watch('credentials', (val, oldVal) => {
      ctrl.$setViewValue(JSON.stringify(val));
    }, true); // unset credentials when new provider is shoosen
    // unset credentials when new provider is shoosen
    this.$watch('provider', (val, oldVal) => {
      if (val != oldVal) {
        this.credentials = {};
      }
    }, true);
    return {
      restrict: 'A',
      require: "^ngModel",
      transclude: true,
      scope: {
        provider: '=',
        credentials: '=value'
      },
      template: '<ng-include src="getTemplateUrl()"/>',
      controllerAs: 'ProviderCredentials',
      controller: (this) => {
        this.getTemplateUrl = () => {
          return `?module=MobileMessaging&action=getCredentialFields&provider=${this.provider}`;
        };
      },
      link: (scope, elm, attrs, ctrl) => {
        if (!ctrl) {
          return;
        } // view -> model
    
    
        this.$watch('credentials', (val, oldVal) => {
          ctrl.$setViewValue(JSON.stringify(val));
        }, true); // unset credentials when new provider is shoosen
    
        this.$watch('provider', (val, oldVal) => {
          if (val != oldVal) {
            this.credentials = {};
          }
        }, true);
      }
    };
  },
});
</script>
