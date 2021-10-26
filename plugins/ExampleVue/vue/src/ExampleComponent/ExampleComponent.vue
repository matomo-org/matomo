<!--
  Matomo - free/libre analytics platform

  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <button v-on:click="decrement">-</button>
    {{ count }}
    <button v-on:click="increment">+</button>

    <MatomoDialog :show="showDialog">
      <div class="ui-confirm exampleDialog">
        <h2>Alert</h2>
        <p>
          The count is greater than 1 right now!
        </p>

        <input type="button" value="OK" role="yes"/>
      </div>
    </MatomoDialog>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MatomoDialog } from 'CoreHome';

export default defineComponent({
  components: {
    MatomoDialog,
  },
  computed: {
    showDialog() {
      return this.count > 15;
    },
  },
  data() {
    return {
      count: 12,
    };
  },

  setup() {
    return {
      increment() {
        this.count += 1;
      },
      decrement() {
        this.count -= 1;
      },
    };
  },
});
</script>
