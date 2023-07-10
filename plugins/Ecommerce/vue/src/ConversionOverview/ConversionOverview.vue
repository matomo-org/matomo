<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="translate('Goals_ConversionsOverview')"
  >
    <ul class="ulGoalTopElements">
      <li>
        {{ translate('General_ColumnRevenue') }}: <span v-html="$sanitize(revenue)"></span>
        <span v-if="revenue_subtotal">,
          {{ translate('General_Subtotal') }}: <span v-html="$sanitize(revenue_subtotal)"></span>
        </span>
        <span v-if="revenue_tax">,
          {{ translate('General_Tax') }}: <span v-html="$sanitize(revenue_tax)"></span>
        </span>
        <span v-if="revenue_shipping">,
          {{ translate('General_Shipping') }}: <span v-html="$sanitize(revenue_shipping)"></span>
        </span>
        <span v-if="revenue_shipping">,
          {{ translate('General_Discount') }}: <span v-html="$sanitize(revenue_discount)"></span>
        </span>
      </li>
    </ul>
    <a
      v-if="visitorLogEnabled"
      href=""
      class="segmentedlog"
      @click.prevent="showSegmentedVisitorLog()"
    >
      <span class="icon-visitor-profile rowActionIcon">&nbsp;
      </span>{{ translate('Live_RowActionTooltipWithDimension', translate('General_Goal')) }}
    </a>
    <br style="clear:left"/>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock } from 'CoreHome';

export default defineComponent({
  props: {
    idGoal: {
      type: [String, Number],
      required: true,
    },
    visitorLogEnabled: Boolean,
    revenue: String,
    revenue_subtotal: String,
    revenue_tax: String,
    revenue_shipping: String,
    revenue_discount: String,
  },
  components: {
    ContentBlock,
  },
  methods: {
    showSegmentedVisitorLog() {
      window.SegmentedVisitorLog.show(
        'Goals.getMetrics',
        `visitConvertedGoalId==${this.idGoal}`,
        {},
      );
    },
  },
});
</script>
