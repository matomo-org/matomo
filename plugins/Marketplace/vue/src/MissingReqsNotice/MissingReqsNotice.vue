<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    v-for="(req, index) in (plugin.missingRequirements || [])"
    :key="index"
    class="alert alert-danger"
  >
    {{ translate(
      'CorePluginsAdmin_MissingRequirementsNotice',
      requirement(req.requirement),
      req.actualVersion,
      req.requiredVersion,
    ) }}
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    plugin: {
      type: Object,
      required: true,
    },
  },
  methods: {
    requirement(req: string) {
      if (req === 'php') {
        return 'PHP';
      }

      return `${req[0].toUpperCase()}${req.substr(1)}`;
    },
  },
});
</script>
