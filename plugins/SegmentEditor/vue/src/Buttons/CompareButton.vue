<template>
  <button
    v-if="segment.showCompareButton"
    :class="[segment.compareButtonClass, { isAnonymous }]"
    :title="segment.compareTitle"
    :data-state="segment.compareState"
    @click.stop.prevent="dispatchToggleEvent()"
  >
    <CompareIcon :state="segment.compareState" />
  </button>
</template>
<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { SegmentSelectorEntry } from '../types';
import CompareIcon from '../SegmentSelector/CompareIcon.vue';

export default defineComponent({
  name: 'CompareButton',
  components: {
    CompareIcon,
  },
  props: {
    segment: {
      type: Object as PropType<SegmentSelectorEntry>,
      required: true,
    },
    isAnonymous: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['toggleCompareButton'],
  methods: {
    // This should be replaced with a direct call to the store to toggle the comparison once the
    // add/edit segment modal is migrated to its own vue component, since that migration will
    // introduce the store-driven panel close mechanism this action depends on. For now we need
    // this to dispatch the event to Segmentation.js.
    dispatchToggleEvent() {
      if (this.segment.compareState === 'disabled' || typeof this.segment.definition === 'undefined') {
        return;
      }
      this.$emit('toggleCompareButton', this.segment.definition);
    },
  },
});
</script>
