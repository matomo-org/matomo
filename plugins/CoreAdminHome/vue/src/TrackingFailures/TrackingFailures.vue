<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    class="matomoTrackingFailures"
    :content-title="translate('CoreAdminHome_TrackingFailures')"
  >
    <p>
      {{ translate('CoreAdminHome_TrackingFailuresIntroduction', '2') }}
      <br /><br />
      <input
        class="btn deleteAllFailures"
        v-show="!isLoading && failures.length > 0"
        type="button"
        @click="deleteAll()"
        :value="translate('CoreAdminHome_DeleteAllFailures')"
      />
    </p>
    <ActivityIndicator
      :loading="isLoading"
    />
    <table v-content-table>
      <thead>
        <tr>
          <th @click="changeSortOrder('idsite')">{{ translate('General_Measurable') }}</th>
          <th @click="changeSortOrder('problem')">{{ translate('CoreAdminHome_Problem') }}</th>
          <th @click="changeSortOrder('solution')">{{ translate('CoreAdminHome_Solution') }}</th>
          <th @click="changeSortOrder('date_first_occurred')">{{ translate('General_Date') }}</th>
          <th @click="changeSortOrder('url')">{{ translate('Actions_ColumnPageURL') }}</th>
          <th @click="changeSortOrder('request_url')">
            {{ translate('CoreAdminHome_TrackingURL') }}
          </th>
          <th class="action">{{ translate('General_Action') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td
            colspan="7"
            v-show="!isLoading && failures.length === 0"
          >{{ translate('CoreAdminHome_NoKnownFailures') }} <span class="icon-ok" /></td>
        </tr>
        <tr v-for="(failure, index) in sortedFailures" :key="index">
          <FailureRow
            :failure="failure"
            @delete="deleteFailure($event.idSite, $event.idFailure)"
          />
        </tr>
      </tbody>
    </table>
    <div
      class="ui-confirm"
      id="confirmDeleteAllTrackingFailures"
    >
      <h2>{{ translate('CoreAdminHome_ConfirmDeleteAllTrackingFailures') }}</h2>
      <input
        type="button"
        role="yes"
        :value="translate('General_Yes')"
      />
      <input
        type="button"
        role="no"
        :value="translate('General_No')"
      />
    </div>
    <div
      class="ui-confirm"
      id="confirmDeleteThisTrackingFailure"
    >
      <h2>{{ translate('CoreAdminHome_ConfirmDeleteThisTrackingFailure') }}</h2>
      <input
        type="button"
        role="yes"
        :value="translate('General_Yes')"
      />
      <input
        type="button"
        role="no"
        :value="translate('General_No')"
      />
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper,
  ContentBlock,
  Matomo,
  ActivityIndicator,
  ContentTable,
} from 'CoreHome';
import FailureRow, { TrackingFailure } from './FailureRow.vue';

interface TrackingFailuresState {
  failures: TrackingFailure[];
  sortColumn: string;
  sortReverse: boolean;
  isLoading: boolean;
}

export default defineComponent({
  components: {
    ContentBlock,
    ActivityIndicator,
    FailureRow,
  },
  directives: {
    ContentTable,
  },
  data(): TrackingFailuresState {
    return {
      failures: [],
      sortColumn: 'idsite',
      sortReverse: false,
      isLoading: false,
    };
  },
  created() {
    this.fetchAll();
  },
  methods: {
    changeSortOrder(columnToSort: string) {
      if (this.sortColumn === columnToSort) {
        this.sortReverse = !this.sortReverse;
      } else {
        this.sortColumn = columnToSort;
      }
    },
    fetchAll() {
      this.failures = [];
      this.isLoading = true;

      AjaxHelper.fetch<TrackingFailure[]>({
        method: 'CoreAdminHome.getTrackingFailures',
        filter_limit: '-1',
      }).then((failures) => {
        this.failures = failures;
        this.isLoading = false;
      }).finally(() => {
        this.isLoading = false;
      });
    },
    deleteAll() {
      Matomo.helper.modalConfirm(
        '#confirmDeleteAllTrackingFailures',
        {
          yes: () => {
            this.failures = [];
            AjaxHelper.fetch({
              method: 'CoreAdminHome.deleteAllTrackingFailures',
            }).then(() => {
              this.fetchAll();
            });
          },
        },
      );
    },
    deleteFailure(idSite: string|number, idFailure: string|number) {
      Matomo.helper.modalConfirm(
        '#confirmDeleteThisTrackingFailure',
        {
          yes: () => {
            this.failures = [];

            AjaxHelper.fetch({
              method: 'CoreAdminHome.deleteTrackingFailure',
              idSite,
              idFailure,
            }).then(() => {
              this.fetchAll();
            });
          },
        },
      );
    },
  },
  computed: {
    sortedFailures() {
      const { sortColumn } = this;

      const sorted: TrackingFailure[] = [...this.failures];

      if (this.sortReverse) {
        sorted.sort((lhs: TrackingFailure, rhs: TrackingFailure) => {
          if (lhs[sortColumn]! > rhs[sortColumn]!) {
            return -1;
          }

          if (lhs[sortColumn]! < rhs[sortColumn]!) {
            return 1;
          }

          return 0;
        });
      } else {
        sorted.sort((lhs: TrackingFailure, rhs: TrackingFailure) => {
          if (lhs[sortColumn]! < rhs[sortColumn]!) {
            return -1;
          }

          if (lhs[sortColumn]! > rhs[sortColumn]!) {
            return 1;
          }

          return 0;
        });
      }

      return sorted;
    },
  },
});
</script>
