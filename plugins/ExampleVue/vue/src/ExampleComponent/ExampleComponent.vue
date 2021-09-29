<template>
  <div>
    <button v-on:click="decrement">-</button>
    {{ count }}
    <button v-on:click="increment">+</button>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
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
