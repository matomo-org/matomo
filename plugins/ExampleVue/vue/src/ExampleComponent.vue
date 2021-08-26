<template>
  <div>
    <button v-on:click="decrement">-</button>
    {{ count }}
    <button v-on:click="increment">+</button>
  </div>
</template>

<script lang="ts">
import { Vue, Options } from 'vue-class-component';

@Options({})
export default class ExampleComponent extends Vue {
  count = 0;

  increment(): void {
    this.count += 1;
  }

  decrement(): void {
    this.count -= 1;
  }
}
</script>
