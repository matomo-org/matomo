<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <PersonalSettings
      :is-users-admin-enabled="isUsersAdminEnabled"
      :title="translate('UsersManager_PersonalSettings')"
      :user-login="userLogin"
      :user-email="userEmail"
      :current-language-code="currentLanguageCode"
      :language-options="languageOptions"
      :current-timeformat="currentTimeformat"
      :time-formats="timeFormats"
      :default-report="defaultReport"
      :default-report-options="defaultReportOptions"
      :default-report-id-site="defaultReportIdSite"
      :default-report-site-name="defaultReportSiteName"
      :default-date="defaultDate"
      :available-default-dates="availableDefaultDates"
    />

    <NewsletterSettings v-if="showNewsletterSignup"></NewsletterSettings>

    <PluginSettings mode="user"></PluginSettings>

    <ContentBlock
      :content-title="translate('UsersManager_ExcludeVisitsViaCookie')"
    >
      <p v-html="$sanitize(yourVisitsAreText)"></p>
      <span style="margin-left:20px;">
        <a :href="setIgnoreCookieLink">
          &rsaquo; {{ ignoreCookieSet
            ? translate('UsersManager_ClickHereToDeleteTheCookie')
            : translate('UsersManager_ClickHereToSetTheCookieOnDomain', piwikHost) }}
          <br/>
        </a>
      </span>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock, translate, MatomoUrl } from 'CoreHome';
import { PluginSettings } from 'CorePluginsAdmin';
import PersonalSettings from '../PersonalSettings/PersonalSettings.vue';
import NewsletterSettings from '../NewsletterSettings/NewsletterSettings.vue';

export default defineComponent({
  props: {
    isUsersAdminEnabled: {
      type: Boolean,
      required: true,
    },
    userLogin: {
      type: String,
      required: true,
    },
    userEmail: {
      type: String,
      required: true,
    },
    currentLanguageCode: {
      type: String,
      required: true,
    },
    languageOptions: {
      type: Object,
      required: true,
    },
    currentTimeformat: {
      type: Number,
      required: true,
    },
    timeFormats: {
      type: Object,
      required: true,
    },
    defaultReport: {
      type: [String, Number],
      required: true,
    },
    defaultReportOptions: {
      type: Object,
      required: true,
    },
    defaultReportIdSite: {
      type: [String, Number],
      required: true,
    },
    defaultReportSiteName: {
      type: String,
      required: true,
    },
    defaultDate: {
      type: String,
      required: true,
    },
    availableDefaultDates: {
      type: Object,
      required: true,
    },
    showNewsletterSignup: Boolean,
    ignoreCookieSet: Boolean,
    ignoreSalt: [String, Number, Boolean],
    piwikHost: {
      type: String,
      required: true,
    },
  },
  components: {
    ContentBlock,
    PersonalSettings,
    NewsletterSettings,
    PluginSettings,
  },
  computed: {
    yourVisitsAreText() {
      if (this.ignoreCookieSet) {
        return translate(
          'UsersManager_YourVisitsAreIgnoredOnDomain',
          '<strong>',
          this.piwikHost,
          '</strong>',
        );
      }

      return translate(
        'UsersManager_YourVisitsAreNotIgnored',
        '<strong>',
        '</strong>',
      );
    },
    setIgnoreCookieLink() {
      return `?${MatomoUrl.stringify({
        ignoreSalt: this.ignoreSalt,
        module: 'UsersManager',
        action: 'setIgnoreCookie',
      })}#excludeCookie`;
    },
  },
});
</script>
