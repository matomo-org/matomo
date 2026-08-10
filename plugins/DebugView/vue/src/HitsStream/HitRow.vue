<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <button
    type="button"
    class="debugViewHitRow"
    :class="{ 'debugViewHitRow--selected': isSelected }"
    :data-hit-id="hit.idRawRequest"
    :aria-label="ariaLabel"
    aria-haspopup="dialog"
    :title="hit.subtitle || undefined"
    @click="$emit('open')"
  >
    <span
      class="debugViewHitIconCircle"
      :class="typeInfo.cssClass"
      aria-hidden="true"
    >
      <img
        v-if="typeInfo.iconSvg"
        :src="typeInfo.iconSvg"
        alt=""
      />
      <span
        v-else
        :class="typeInfo.icon"
      />
    </span>
    <span class="debugViewHitTitle">{{ hit.title }}</span>
    <span
      v-if="hit.isBot"
      class="debugViewBotBadge"
      :title="hit.botName || undefined"
    >{{ translate('DebugView_BotBadge') }}</span>
    <span class="debugViewHitTime">{{ hit.timePretty }}</span>
  </button>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import { Hit } from '../types';
import getHitTypeInfo, { HitTypeInfo } from '../hitTypes';

export default defineComponent({
  props: {
    hit: {
      type: Object as PropType<Hit>,
      required: true,
    },
    isSelected: Boolean,
  },
  emits: ['open'],
  computed: {
    typeInfo(): HitTypeInfo {
      return getHitTypeInfo(this.hit.type, this.hit.trackingParams);
    },
    ariaLabel(): string {
      let typeLabel = translate(this.typeInfo.labelKey);
      if (this.hit.isBot) {
        typeLabel = `${typeLabel} (${this.hit.botName || translate('DebugView_BotBadge')})`;
      }
      return `${typeLabel}: ${this.hit.title}, ${this.hit.timePretty}`;
    },
  },
});
</script>
