<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<todo>
- property types
- state types
- look over template
- look over component code
- get to build
- test in UI
- check uses:
  ./plugins/PrivacyManager/templates/privacySettings.twig
  ./plugins/PrivacyManager/angularjs/anonymize-log-data/anonymize-log-data.directive.js
- create PR
</todo>

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
            class="anonymizeStartDate"
            name="anonymizeStartDate"
            v-model="start_date"
          >
          </input>
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
            name="anonymizeEndDate"
            v-model="end_date"
          >
          </input>
        </div>
      </div>
    </div>
    <div>
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
    <div>
      <Field
        uicontrol="checkbox"
        name="anonymizeLocation"
        :title="translate('PrivacyManager_AnonymizeLocation')"
        v-model="anonymizeLocation"
        :inline-help="translate('PrivacyManager_AnonymizeLocationHelp')"
      >
      </Field>
    </div>
    <div>
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
            v-for="(index, visitColumn) in selectedVisitColumns"
            :key="TODO"
          >
            <div class="innerFormField">
              <Field
                uicontrol="select"
                name="visit_columns"
                :model-value="selectedVisitColumns.index.column"
                @update:model-value="selectedVisitColumns.index.column = $event; onVisitColumnChange()"
                :full-width="true"
                :options="availableVisitColumns"
              >
              </Field>
            </div>
            <span
              class="icon-minus valign"
              @click="removeVisitColumn(index)"
              v-show="!(index + 1 == (anonymizeLogData.selectedVisitColumns | length))"
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
            v-for="(index, actionColumn) in selectedActionColumns"
            :key="TODO"
          >
            <div class="innerFormField">
              <Field
                uicontrol="select"
                name="action_columns"
                :model-value="selectedActionColumns.index.column"
                @update:model-value="selectedActionColumns.index.column = $event; onActionColumnChange()"
                :full-width="true"
                :options="availableActionColumns"
              >
              </Field>
            </div>
            <span
              class="icon-minus valign"
              @click="removeActionColumn(index)"
              v-show="!(index + 1 == (anonymizeLogData.selectedActionColumns | length))"
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
      @confirm="scheduleAnonymization()"
      :disabled="!anonymizeIp && !anonymizeLocation && !selectedVisitColumns && !selectedActionColumns"
      :value="translate('PrivacyManager_AnonymizeDataNow')"
    >
    </SaveButton>
    <div
      class="ui-confirm"
      id="confirmAnonymizeLogData"
    >
      <h2>{{ translate('PrivacyManager_AnonymizeDataConfirm') }}</h2>
      <input
        role="yes"
        type="button"
        :value="translate('General_Yes')"
      />
      <input
        role="no"
        type="button"
        :value="translate('General_No')"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  Matomo,
  AjaxHelper,
  SiteSelector
} from 'CoreHome';
import { Field, SaveButton } from 'CorePluginsAdmin';


interface AnonymizeLogDataState {
  isLoading: boolean;
  isDeleting: boolean;
  anonymizeIp: boolean;
  anonymizeLocation: boolean;
  anonymizeUserId: boolean;
  site: Record<string, string>;
  availableVisitColumns: unknown[]; // TODO
  availableActionColumns: unknown[]; // TODO
  selectedVisitColumns: Record<string, string>[];
  selectedActionColumns: Record<string, string>[];
  start_date: unknown; // TODO
  end_date: unknown; // TODO
  now: unknown; // TODO
}

export default defineComponent({
  props: {
  },
  components: {
    SiteSelector,
    Field,
    SaveButton,
  },
  data(): AnonymizeLogDataState {
    return {
      isLoading: false,
      isDeleting: false,
      anonymizeIp: false,
      anonymizeLocation: false,
      anonymizeUserId: false,
      site: {
        id: 'all',
        name: 'All Websites'
      },
      availableVisitColumns: [],
      availableActionColumns: [],
      selectedVisitColumns: [{
        column: ''
      }],
      selectedActionColumns: [{
        column: ''
      }],
      start_date: `${now.getFullYear()}-${sub(now.getMonth() + 1)}-` + sub(now.getDay() + 1),
      end_date: this.start_date,
      now: new Date(),
    };
  },
  created() {
    AjaxHelper.fetch({
      method: 'PrivacyManager.getAvailableVisitColumnsToAnonymize'
    }).then((columns) => {
      this.availableVisitColumns = [];
      angular.forEach(columns, (column) => {
        this.availableVisitColumns.push({
          key: column.column_name,
          value: column.column_name
        });
      });
    });
    AjaxHelper.fetch({
      method: 'PrivacyManager.getAvailableLinkVisitActionColumnsToAnonymize'
    }).then((columns) => {
      this.availableActionColumns = [];
      angular.forEach(columns, (column) => {
        this.availableActionColumns.push({
          key: column.column_name,
          value: column.column_name
        });
      });
    });
    setTimeout(() => {
      const options1 = Matomo.getBaseDatePickerOptions(null);
      const options2 = Matomo.getBaseDatePickerOptions(null);
      $(".anonymizeStartDate").datepicker(options1);
      $(".anonymizeEndDate").datepicker(options2);
    });
  },
  methods: {
    // TODO
    onVisitColumnChange() {
      const hasAll = true;
      angular.forEach(this.selectedVisitColumns, (visitColumn) => {
        if (!visitColumn || !visitColumn.column) {
          hasAll = false;
        }
      });

      if (hasAll) {
        this.addVisitColumn();
      }
    },
    // TODO
    addVisitColumn() {
      this.selectedVisitColumns.push({
        column: ''
      });
    },
    // TODO
    removeVisitColumn(index) {
      if (index > -1) {
        const lastIndex = this.selectedVisitColumns.length - 1;

        if (lastIndex === index) {
          this.selectedVisitColumns[index] = {
            column: ''
          };
        } else {
          this.selectedVisitColumns.splice(index, 1);
        }
      }
    },
    // TODO
    onActionColumnChange() {
      const hasAll = true;
      angular.forEach(this.selectedActionColumns, (actionColumn) => {
        if (!actionColumn || !actionColumn.column) {
          hasAll = false;
        }
      });

      if (hasAll) {
        this.addActionColumn();
      }
    },
    // TODO
    addActionColumn() {
      this.selectedActionColumns.push({
        column: ''
      });
    },
    // TODO
    removeActionColumn(index) {
      if (index > -1) {
        const lastIndex = this.selectedActionColumns.length - 1;

        if (lastIndex === index) {
          this.selectedActionColumns[index] = {
            column: ''
          };
        } else {
          this.selectedActionColumns.splice(index, 1);
        }
      }
    },
    // TODO
    scheduleAnonymization() {
      const date = `${this.start_date},${this.end_date}`;

      if (this.start_date === this.end_date) {
        date = this.start_date;
      }

      const params = {
        date: date
      };
      params.idSites = this.site.id;
      params.anonymizeIp = this.anonymizeIp ? '1' : '0';
      params.anonymizeLocation = this.anonymizeLocation ? '1' : '0';
      params.anonymizeUserId = this.anonymizeUserId ? '1' : '0';
      params.unsetVisitColumns = [];
      params.unsetLinkVisitActionColumns = [];
      angular.forEach(this.selectedVisitColumns, (column) => {
        if (column.column) {
          params.unsetVisitColumns.push(column.column);
        }
      });
      angular.forEach(this.selectedActionColumns, (column) => {
        if (column.column) {
          params.unsetLinkVisitActionColumns.push(column.column);
        }
      });
      Matomo.helper.modalConfirm('#confirmAnonymizeLogData', {
        yes: () => {
          AjaxHelper.post({
            method: 'PrivacyManager.anonymizeSomeRawData'
          }, params).then(() => {
            location.reload(true);
          });
        }
      });
    },
  },
});
</script>
