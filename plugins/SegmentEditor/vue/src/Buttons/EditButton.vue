<template>
  <button
    v-if="segment.showEditButton"
    class="segmentAction editSegment"
    :title="segment.editTitle"
    :data-state="segment.editState"
    @click.stop.prevent="dispatchOpenEvent(segment)"
  />
</template>
<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { SegmentSelectorEntry } from '../types';

export default defineComponent({
  name: 'EditButton',
  props: {
    segment: {
      type: Object as PropType<SegmentSelectorEntry>,
      required: true,
    },
  },
  emits: ['openEditButton'],
  methods: {
    // This should be replaced with a direct call to store to open edit modal once we have migrated
    // it to its own vue component. For now we need this to dispatch the event to Segmentation.js
    dispatchOpenEvent() {
      if (this.segment.editState === 'disabled' || !this.segment.idsegment) {
        return;
      }
      this.$emit('openEditButton', this.segment.idsegment);
    },
  },
});
</script>
