<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <DemoCodePair
      v-for="snippet in demoSnippets"
      :key="snippet.id"
      :snippet="snippet"
    />

    <h2>Icons</h2>

    <div id="icons" class="demo icons">
      <div v-for="(iconList, category) in icons" :key="category">
        <h4>{{ category }}</h4>
        <div class="row">
          <div class="col s4 icon" v-for="(icon, index) in iconList" :key="index">
            <span :class="`icon-${icon}`"></span> {{ icon }}
          </div>
        </div>
      </div>
    </div>
    <div class="demo-code">
      <pre>&lt;span class=&quot;icon-ok&quot;&gt;&lt;/span&gt;</pre>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import DemoCodePair from './DemoCodePair.vue';

export default defineComponent({
  props: {
    demoSnippets: {
      type: Array,
      required: true,
    },
    icons: {
      type: Object,
      required: true,
    },
  },
  components: {
    DemoCodePair,
  },
});
</script>
