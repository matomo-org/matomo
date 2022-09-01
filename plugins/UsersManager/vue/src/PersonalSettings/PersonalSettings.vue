<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock :content-title="title" :feature="'true'">
    <form id="userSettingsTable" v-form>

      <div>
        <Field
          uicontrol="text"
          name="username"
          :title="translate('General_Username')"
          :disabled="true"
          v-model="username"
          :inline-help="translate('UsersManager_YourUsernameCannotBeChanged')"
        />
      </div>

      <div v-if="isUsersAdminEnabled">
        <Field
          uicontrol="text"
          name="email"
          :model-value="email"
          @update:model-value="email = $event; doesRequirePasswordConfirmation = true"
          :maxlength="100"
          :title="translate('UsersManager_Email')"
        />
      </div>

      <div id="languageHelp" class="inline-help-node">
        <a target="_blank" rel="noreferrer noopener" href="https://matomo.org/translations/">
          {{ translate('LanguagesManager_AboutPiwikTranslations') }}</a>
      </div>

      <div>
        <Field
          uicontrol="select"
          name="language"
          v-model="language"
          :title="translate('General_Language')"
          :options="languageOptions"
          inline-help="#languageHelp"
        />
      </div>

      <div>
        <Field
          uicontrol="select"
          name="timeformat"
          v-model="timeformat"
          :title="translate('General_TimeFormat')"
          :options="timeFormats"
        />
      </div>

      <div>
        <Field
          uicontrol="radio"
          name="defaultReport"
          v-model="theDefaultReport"
          :introduction="translate('UsersManager_ReportToLoadByDefault')"
          :title="translate('General_AllWebsitesDashboard')"
          :options="defaultReportOptions"
        />
      </div>

      <div
        class="sites_autocomplete"
      >
        <SiteSelector
           v-model="site"
           :show-selected-site="true"
           :switch-site-on-select="false"
           :show-all-sites-item="false"
           :showselectedsite="true"
           id="defaultReportSiteSelector"
        />
      </div>

      <div>
        <Field
          uicontrol="radio"
          name="defaultDate"
          v-model="theDefaultDate"
          :introduction="translate('UsersManager_ReportDateToLoadByDefault')"
          :options="availableDefaultDates"
        />
      </div>

      <SaveButton @confirm="save()" :saving="loading"/>

      <PasswordConfirmation
        v-model="showPasswordConfirmation"
        @confirmed="doSave"
      >
        <h2>{{ translate('UsersManager_ConfirmWithPassword') }}</h2>
      </PasswordConfirmation>
    </form>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper,
  ContentBlock,
  SiteRef,
  SiteSelector,
  NotificationsStore,
  translate,
  Matomo,
} from 'CoreHome';
import {
  SaveButton,
  Field,
  Form,
  PasswordConfirmation,
} from 'CorePluginsAdmin';

interface PersonalSettingsState {
  doesRequirePasswordConfirmation: boolean;
  username: string;
  email: string;
  language: string;
  timeformat: number;
  theDefaultReport: string|number;
  site: SiteRef;
  theDefaultDate: string;
  loading: boolean;
  showPasswordConfirmation: boolean;
}

export default defineComponent({
  props: {
    isUsersAdminEnabled: {
      type: Boolean,
      required: true,
    },
    title: {
      type: String,
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
  },
  components: {
    ContentBlock,
    SaveButton,
    Field,
    SiteSelector,
    PasswordConfirmation,
  },
  directives: {
    Form,
  },
  data(): PersonalSettingsState {
    return {
      doesRequirePasswordConfirmation: false,
      username: this.userLogin,
      email: this.userEmail,
      language: this.currentLanguageCode,
      timeformat: this.currentTimeformat,
      theDefaultReport: this.defaultReport,
      site: {
        id: this.defaultReportIdSite,
        name: Matomo.helper.htmlDecode(this.defaultReportSiteName),
      },
      theDefaultDate: this.defaultDate,
      loading: false,
      showPasswordConfirmation: false,
    };
  },
  methods: {
    save() {
      if (this.doesRequirePasswordConfirmation) {
        this.showPasswordConfirmation = true;
        return;
      }

      this.doSave();
    },
    doSave(password?: string) {
      const postParams: QueryParameters = {
        email: this.email,
        defaultReport: this.theDefaultReport === 'MultiSites'
          ? this.theDefaultReport
          : this.site.id,
        defaultDate: this.theDefaultDate,
        language: this.language,
        timeformat: this.timeformat,
      };

      if (password) {
        postParams.passwordConfirmation = password;
      }

      this.loading = true;

      AjaxHelper.post(
        {
          module: 'UsersManager',
          action: 'recordUserSettings',
          format: 'json',
        },
        postParams,
        {
          withTokenInUrl: true,
        },
      ).then(() => {
        const id = NotificationsStore.show({
          message: translate('CoreAdminHome_SettingsSaveSuccess'),
          id: 'PersonalSettingsSuccess',
          context: 'success',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(id);

        this.doesRequirePasswordConfirmation = false;
        this.loading = false;
      }).catch(() => {
        this.loading = false;
      });
    },
  },
});
</script>
