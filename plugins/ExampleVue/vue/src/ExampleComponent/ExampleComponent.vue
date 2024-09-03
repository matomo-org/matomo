<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="example-component">
    <button v-on:click="decrement">-</button>
    {{ count }}
    <button v-on:click="increment">+</button>

    <MatomoDialog v-model="showDialog">
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

<style lang="less" scoped>
  .example-component {
    display: flex;
    justify-content: center;
    align-items: center;

    button {
      background-color: indigo;
      color: white;
      margin: 8px;
    }
  }
</style>

<script lang="ts">
import { defineComponent } from 'vue';
import { MatomoDialog } from 'CoreHome';

interface ExampleComponentState {
  count: number;
  showDialog: boolean;
}

export default defineComponent({
  components: {
    MatomoDialog,
  },
  data(): ExampleComponentState {
    return {
      count: 12,
      showDialog: false,
    };
  },
  methods: {
    increment() {
      this.count += 1;
      this.showDialog = this.count > 15;
    },
    decrement() {
      this.count -= 1;
      this.showDialog = this.count > 15;
    },
  },
});
</script>
