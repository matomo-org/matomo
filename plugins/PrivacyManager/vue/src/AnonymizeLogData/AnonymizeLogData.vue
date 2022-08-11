<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="anonymizeLogData">
    <div class="form-group row">
      <div class="col s12 input-field">
        <div>
          <label
            for="anonymizeSite"
            class="siteSelectorLabel"
          >{{ translate('PrivacyManager_AnonymizeSites') }}</label>
          <div class="sites_autocomplete">
            <SiteSelector
              id="anonymizeSite"
              v-model="site"
              :show-all-sites-item="true"
              :switch-site-on-select="false"
              :show-selected-site="true"
            />
          </div>
        </div>
      </div>
    </div>
    <div class="form-group row">
      <div class="col s6 input-field">
        <div>
          <label
            for="anonymizeStartDate"
            class="active"
          >{{ translate('PrivacyManager_AnonymizeRowDataFrom') }}</label>
          <input
            type="text"
            id="anonymizeStartDate"
            class="anonymizeStartDate"
            ref="anonymizeStartDate"
            name="anonymizeStartDate"
            :value="startDate"
            @keydown="onKeydownStartDate($event)"
            @change="onKeydownStartDate($event)"
          />
        </div>
      </div>
      <div class="col s6 input-field">
        <div>
          <label
            for="anonymizeEndDate"
            class="active"
          >{{ translate('PrivacyManager_AnonymizeRowDataTo') }}</label>
          <input
            type="text"
            class="anonymizeEndDate"
            id="anonymizeEndDate"
            ref="anonymizeEndDate"
            name="anonymizeEndDate"
            :value="endDate"
            @keydown="onKeydownEndDate($event)"
            @change="onKeydownEndDate($event)"
          />
        </div>
      </div>
    </div>
    <div name="anonymizeIp">
      <Field
        uicontrol="checkbox"
        name="anonymizeIp"
        :title="translate('PrivacyManager_AnonymizeIp')"
        v-model="anonymizeIp"
        :introduction="translate('General_Visit')"
        :inline-help="translate('PrivacyManager_AnonymizeIpHelp')"
      >
      </Field>
    </div>
    <div name="anonymizeLocation">
      <Field
        uicontrol="checkbox"
        name="anonymizeLocation"
        :title="translate('PrivacyManager_AnonymizeLocation')"
        v-model="anonymizeLocation"
        :inline-help="translate('PrivacyManager_AnonymizeLocationHelp')"
      >
      </Field>
    </div>
    <div name="anonymizeTheUserId">
      <Field
        uicontrol="checkbox"
        name="anonymizeTheUserId"
        :title="translate('PrivacyManager_AnonymizeUserId')"
        v-model="anonymizeUserId"
        :inline-help="translate('PrivacyManager_AnonymizeUserIdHelp')"
      >
      </Field>
    </div>
    <div class="form-group row">
      <div class="col s12 m6">
        <div>
          <label for="visit_columns">{{ translate('PrivacyManager_UnsetVisitColumns') }}</label>
          <div
            :class="`selectedVisitColumns selectedVisitColumns${index} multiple valign-wrapper`"
            v-for="(visitColumn, index) in selectedVisitColumns"
            :key="index"
          >
            <div class="innerFormField" name="visit_columns">
              <Field
                uicontrol="select"
                name="visit_columns"
                :model-value="visitColumn.column"
                @update:model-value="visitColumn.column = $event; onVisitColumnChange()"
                :full-width="true"
                :options="availableVisitColumns"
              >
              </Field>
            </div>
            <span
              class="icon-minus valign"
              @click="removeVisitColumn(index)"
              v-show="index + 1 !== selectedVisitColumns.length"
              :title="translate('General_Remove')"
            />
          </div>
        </div>
      </div>
      <div class="col s12 m6">
        <div class="form-help">
          <span class="inline-help">{{ translate('PrivacyManager_UnsetVisitColumnsHelp') }}</span>
        </div>
      </div>
    </div>
    <div class="form-group row">
      <div class="col s12">
        <h3>{{ translate('General_Action') }}</h3>
      </div>
    </div>
    <div class="form-group row">
      <div class="col s12 m6">
        <div>
          <label for="action_columns">{{ translate('PrivacyManager_UnsetActionColumns') }}</label>
          <div
            :class="`selectedActionColumns selectedActionColumns${index} multiple valign-wrapper`"
            v-for="(actionColumn, index) in selectedActionColumns"
            :key="index"
          >
            <div class="innerFormField" name="action_columns">
              <Field
                uicontrol="select"
                name="action_columns"
                :model-value="actionColumn.column"
                @update:model-value="actionColumn.column = $event; onActionColumnChange()"
                :full-width="true"
                :options="availableActionColumns"
              >
              </Field>
            </div>
            <span
              class="icon-minus valign"
              @click="removeActionColumn(index)"
              v-show="index + 1 !== selectedActionColumns.length"
              :title="translate('General_Remove')"
            />
          </div>
        </div>
      </div>
      <div class="col s12 m6">
        <div class="form-help">
          <span class="inline-help">{{ translate('PrivacyManager_UnsetActionColumnsHelp') }}</span>
        </div>
      </div>
    </div>
    <p><span class="icon-info" /> {{ translate('PrivacyManager_AnonymizeProcessInfo') }}</p>
    <SaveButton
      class="anonymizePastData"
      @confirm="showPasswordConfirmModal = true"
      :disabled="isAnonymizePastDataDisabled"
      :value="translate('PrivacyManager_AnonymizeDataNow')"
    >
    </SaveButton>
    <PasswordConfirmation
      v-model="showPasswordConfirmModal"
      @confirmed="scheduleAnonymization"
    >
      <h2>{{ translate('PrivacyManager_AnonymizeDataConfirm') }}</h2>
      <div>{{ translate('UsersManager_ConfirmWithPassword') }}</div>
    </PasswordConfirmation>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  Matomo,
  AjaxHelper,
  SiteSelector,
  debounce,
} from 'CoreHome';
import { PasswordConfirmation, Field, SaveButton } from 'CorePluginsAdmin';

interface Option {
  key: string;
  value: string;
}

interface AnonymizeLogDataState {
  isLoading: boolean;
  isDeleting: boolean;
  anonymizeIp: boolean;
  anonymizeLocation: boolean;
  anonymizeUserId: boolean;
  site: Record<string, string>;
  availableVisitColumns: Option[];
  availableActionColumns: Option[];
  selectedVisitColumns: Record<string, string>[];
  selectedActionColumns: Record<string, string>[];
  startDate: string;
  endDate: string;
  showPasswordConfirmModal: boolean;
}

function sub(value: number) {
  if (value < 10) {
    return `0${value}`;
  }
  return value;
}

export default defineComponent({
  components: {
    PasswordConfirmation,
    SiteSelector,
    Field,
    SaveButton,
  },
  data(): AnonymizeLogDataState {
    const now = new Date();
    const startDate = `${now.getFullYear()}-${sub(now.getMonth() + 1)}-${sub(now.getDay() + 1)}`;
    return {
      isLoading: false,
      isDeleting: false,
      anonymizeIp: false,
      anonymizeLocation: false,
      anonymizeUserId: false,
      site: {
        id: 'all',
        name: 'All Websites',
      },
      availableVisitColumns: [],
      availableActionColumns: [],
      selectedVisitColumns: [{
        column: '',
      }],
      selectedActionColumns: [{
        column: '',
      }],
      startDate,
      endDate: startDate,
      showPasswordConfirmModal: false,
    };
  },
  created() {
    this.onKeydownStartDate = debounce(this.onKeydownStartDate, 50);
    this.onKeydownEndDate = debounce(this.onKeydownEndDate, 50);

    AjaxHelper.fetch<{ column_name: string }[]>({
      method: 'PrivacyManager.getAvailableVisitColumnsToAnonymize',
    }).then((columns) => {
      this.availableVisitColumns = [];
      columns.forEach((column) => {
        this.availableVisitColumns.push({
          key: column.column_name,
          value: column.column_name,
        });
      });
    });

    AjaxHelper.fetch<{ column_name: string }[]>({
      method: 'PrivacyManager.getAvailableLinkVisitActionColumnsToAnonymize',
    }).then((columns) => {
      this.availableActionColumns = [];
      columns.forEach((column) => {
        this.availableActionColumns.push({
          key: column.column_name,
          value: column.column_name,
        });
      });
    });

    setTimeout(() => {
      const options1 = Matomo.getBaseDatePickerOptions(null);
      const options2 = Matomo.getBaseDatePickerOptions(null);
      $(this.$refs.anonymizeStartDate as HTMLElement).datepicker(options1);
      $(this.$refs.anonymizeEndDate as HTMLElement).datepicker(options2);
    });
  },
  methods: {
    onVisitColumnChange() {
      const hasAll = this.selectedVisitColumns.every((col) => !!col?.column);
      if (hasAll) {
        this.addVisitColumn();
      }
    },
    addVisitColumn() {
      this.selectedVisitColumns.push({ column: '' });
    },
    removeVisitColumn(index: number) {
      if (index > -1) {
        const lastIndex = this.selectedVisitColumns.length - 1;

        if (lastIndex === index) {
          this.selectedVisitColumns[index] = { column: '' };
        } else {
          this.selectedVisitColumns.splice(index, 1);
        }
      }
    },
    onActionColumnChange() {
      const hasAll = this.selectedActionColumns.every((col) => !!col?.column);
      if (hasAll) {
        this.addActionColumn();
      }
    },
    addActionColumn() {
      this.selectedActionColumns.push({ column: '' });
    },
    removeActionColumn(index: number) {
      if (index > -1) {
        const lastIndex = this.selectedActionColumns.length - 1;

        if (lastIndex === index) {
          this.selectedActionColumns[index] = {
            column: '',
          };
        } else {
          this.selectedActionColumns.splice(index, 1);
        }
      }
    },
    scheduleAnonymization(password: string) {
      let date = `${this.startDate},${this.endDate}`;

      if (this.startDate === this.endDate) {
        date = this.startDate;
      }

      const params: QueryParameters = { date };
      params.idSites = this.site.id;
      params.anonymizeIp = this.anonymizeIp ? '1' : '0';
      params.anonymizeLocation = this.anonymizeLocation ? '1' : '0';
      params.anonymizeUserId = this.anonymizeUserId ? '1' : '0';
      params.unsetVisitColumns = this.selectedVisitColumns.filter(
        (c) => !!c?.column,
      ).map((c) => c.column);
      params.unsetLinkVisitActionColumns = this.selectedActionColumns.filter(
        (c) => !!c?.column,
      ).map((c) => c.column);
      params.passwordConfirmation = password;

      AjaxHelper.post({
        method: 'PrivacyManager.anonymizeSomeRawData',
      }, params).then(() => {
        window.location.reload(true);
      });
    },
    onKeydownStartDate(event: Event) {
      this.startDate = (event.target as HTMLInputElement).value;
    },
    onKeydownEndDate(event: Event) {
      this.endDate = (event.target as HTMLInputElement).value;
    },
  },
  computed: {
    isAnonymizePastDataDisabled(): boolean {
      return !this.anonymizeIp && !this.anonymizeLocation && !this.selectedVisitColumns
        && !this.selectedActionColumns;
    },
  },
});
</script>
