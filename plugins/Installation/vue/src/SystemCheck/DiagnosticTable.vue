<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <Passthrough v-for="(result, index) in results" :key="index">
    <tr>
      <td v-html="$sanitize(result.label)"></td>
      <td>
        <span v-for="(item, index) in result.items" :key="index">
          <span v-if="item.status === 'error'">
            <span class="icon-error"></span>
            <span
              class="err"
              v-html="$sanitize(typeof item.comment !== 'string' ? '' : item.comment)"
            ></span>
          </span>
          <span v-else-if="item.status === 'warning'">
            <span class="icon-warning"></span>
            <span v-html="$sanitize(typeof item.comment !== 'string' ? '' : item.comment)"></span>
          </span>
          <span v-else-if="item.status === 'informational'">
            <span class="icon-info"></span>
            <span v-html="$sanitize(typeof item.comment !== 'string' ? '' : item.comment)"></span>
          </span>
          <span v-else>
            <span class="icon-ok"></span>
            <span v-html="$sanitize(typeof item.comment !== 'string' ? '' : item.comment)"></span>
          </span>
          <br/>
        </span>
      </td>
    </tr>
    <tr v-if="result.longErrorMessage">
      <td
        colspan="2"
        class="error"
        style="font-size: small;"
        v-html="$sanitize(result.longErrorMessage)"
      ></td>
    </tr>
  </Passthrough>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Passthrough } from 'CoreHome';

export default defineComponent({
  props: {
    errorType: {
      type: String,
      required: true,
    },
    warningType: {
      type: String,
      required: true,
    },
    informationalType: {
      type: String,
      required: true,
    },
    results: {
      type: Array,
      required: true,
    },
  },
  components: {
    Passthrough,
  },
});
</script>
