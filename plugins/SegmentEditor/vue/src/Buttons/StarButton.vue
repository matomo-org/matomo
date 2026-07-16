<template>
  <button
    :data-star="segment.idsegment"
    class="segmentAction starSegment"
    :title="segment.starTitle"
    :data-state="segment.starState"
    @click.stop.prevent="toggleStar(entry)"
  >
    <StarIcon :filled="!!segment.isStarred" />
  </button>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { SegmentSelectorEntry } from '../types';
import SegmentSelectorStore from '../SegmentSelector/SegmentSelector.store';
import StarIcon from '../SegmentSelector/StarIcon.vue';

export default defineComponent({
  name: 'StarButton',
  components: {
    StarIcon,
  },
  props: {
    segment: {
      type: Object as PropType<SegmentSelectorEntry>,
      required: true,
    },
  },
  methods: {
    toggleStar() {
      if (this.segment.starState === 'disabled' || !this.segment.idsegment) {
        return;
      }
      SegmentSelectorStore.toggleStarredSegmentById(this.segment.idsegment);
    },
  },
});
</script>
