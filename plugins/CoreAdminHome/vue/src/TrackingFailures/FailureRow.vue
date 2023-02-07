<template>
  <td>{{ failure.site_name }} ({{ translate('General_Id') }} {{ failure.idsite }})</td>
  <td>{{ failure.problem }}</td>
  <td>{{ failure.solution }} <a
    v-show="failure.solution_url"
    rel="noopener noreferrer"
    :href="failure.solution_url"
  >{{ translate('CoreAdminHome_LearnMore') }}</a></td>
  <td class="datetime">{{ failure.pretty_date_first_occurred }}</td>
  <td>{{ failure.url }}</td>
  <td>
    <span
      v-show="!showFullRequestUrl"
      @click="showFullRequestUrl = true"
      :title="translate('CoreHome_ClickToSeeFullInformation')"
    >{{ limtedRequestUrl }}...</span>
    <span v-show="failure.showFullRequestUrl">{{ failure.request_url }}</span>
  </td>
  <td><span
    class="table-action icon-delete"
    @click="deleteFailure(failure.idsite, failure.idfailure)"
    :title="translate('General_Delete')"
  /></td>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export interface TrackingFailure {
  [index: string]: string|number|undefined;

  idfailure: string|number;
  site_name: string;
  idsite: string|number;
  problem: string;
  solution: string;
  solution_url?: string;
  url: string;
  pretty_date_first_occurred: string;
  request_url: string;
}

export default defineComponent({
  props: {
    failure: {
      type: Object,
      required: true,
    },
  },
  emits: ['delete'],
  data() {
    return {
      showFullRequestUrl: false,
    };
  },
  computed: {
    limtedRequestUrl() {
      return (this.failure as TrackingFailure).request_url.substring(0, 100);
    },
  },
  methods: {
    deleteFailure(idSite: string|number, idFailure: string|number) {
      this.$emit('delete', { idSite, idFailure });
    },
  },
});
</script>
