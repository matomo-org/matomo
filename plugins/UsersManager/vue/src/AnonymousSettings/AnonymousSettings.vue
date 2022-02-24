<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock :content-title="title">
    <div class="alert alert-info" v-if="anonymousSites.length === 0">
      {{ translate('UsersManager_NoteNoAnonymousUserAccessSettingsWontBeUsed2') }}
    </div>

    <div v-form v-if="anonymousSites.length > 0">

      <div>
        <Field
          uicontrol="radio"
          name="anonymousDefaultReport"
          v-model="defaultReport"
          :introduction="translate(
            'UsersManager_WhenUsersAreNotLoggedInAndVisitPiwikTheyShouldAccess',
          )"
          :options="defaultReportOptions"
        />
      </div>

      <div>
        <Field
          uicontrol="select"
          name="anonymousDefaultReportWebsite"
          v-model="defaultReportWebsite"
          :options="anonymousSites"
        />
      </div>

      <div>
        <Field
          uicontrol="radio"
          name="anonymousDefaultDate"
          v-model="defaultDate"
          :introduction="translate('UsersManager_ForAnonymousUsersReportDateToLoadByDefault')"
          :options="availableDefaultDates"
        />
      </div>

      <SaveButton
        :saving="loading"
        @confirm="save()"
      />
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper,
  ContentBlock,
  NotificationsStore,
  translate,
} from 'CoreHome';
import { Form, SaveButton, Field } from 'CorePluginsAdmin';

interface AnonymousSettingsState {
  defaultReport: string;
  loading: boolean;
  defaultReportWebsite: string|number;
  defaultDate: string;
}

export default defineComponent({
  props: {
    title: {
      type: String,
      required: true,
    },
    anonymousSites: {
      type: Array,
      required: true,
    },
    anonymousDefaultReport: {
      type: [String, Number],
      required: true,
    },
    anonymousDefaultSite: {
      type: String,
      required: true,
    },
    anonymousDefaultDate: {
      type: String,
      required: true,
    },
    availableDefaultDates: {
      type: Object,
      required: true,
    },
    defaultReportOptions: {
      type: Object,
      required: true,
    },
  },
  components: {
    ContentBlock,
    SaveButton,
    Field,
  },
  directives: {
    Form,
  },
  data(): AnonymousSettingsState {
    return {
      loading: false,
      defaultReport: `${this.anonymousDefaultReport}`,
      defaultReportWebsite: this.anonymousDefaultSite,
      defaultDate: this.anonymousDefaultDate,
    };
  },
  methods: {
    save() {
      const postParams = {
        anonymousDefaultReport: this.defaultReport === '1'
          ? this.defaultReportWebsite
          : this.defaultReport,
        anonymousDefaultDate: this.defaultDate,
      };

      this.loading = true;

      AjaxHelper.post(
        {
          module: 'UsersManager',
          action: 'recordAnonymousUserSettings',
          format: 'json',
        },
        postParams,
        { withTokenInUrl: true },
      ).then(() => {
        const id = NotificationsStore.show({
          message: translate('CoreAdminHome_SettingsSaveSuccess'),
          id: 'anonymousUserSettings',
          context: 'success',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(id);
      }).finally(() => {
        this.loading = false;
      });
    },
  },
});
</script>
