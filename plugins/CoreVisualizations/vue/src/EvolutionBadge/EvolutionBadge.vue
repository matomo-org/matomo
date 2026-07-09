<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <span
    class="evolutionBadge"
    :class="directionClass"
    :title="tooltip || undefined"
  >
    <span class="evolutionBadge__icon" aria-hidden="true">
      <EvolutionTrendIcon class="evolutionTrendIcon" :direction="direction" />
    </span>
    <span class="evolutionBadge__value">{{ formattedPercent }}</span>
  </span>
</template>

<script lang="ts">
import { computed, defineComponent } from 'vue';
import EvolutionTrendIcon from './EvolutionTrendIcon.vue';

type Direction = 'up' | 'down' | 'neutral';

export default defineComponent({
  name: 'EvolutionBadge',
  components: {
    EvolutionTrendIcon,
  },
  props: {
    // the change to display, either a number (eg 4, -4) or a pre-formatted
    // string as emitted by Sparklines/Config.php (eg "4%", "-4%")
    percent: {
      type: [Number, String],
      required: true,
    },
    // when true the colour is inverted, so a decrease reads as positive (eg bounce rate)
    isLowerValueBetter: {
      type: Boolean,
      default: false,
    },
    // raw value difference (currentValue - pastValue); the authoritative source of the
    // arrow direction when available, falling back to the sign of percent otherwise
    trend: {
      type: Number,
      default: undefined,
    },
    tooltip: {
      type: String,
      default: '',
    },
  },
  setup(props) {
    const changeValue = computed<number>(() => {
      if (typeof props.trend === 'number' && !Number.isNaN(props.trend)) {
        return props.trend;
      }

      // only the sign matters here. Fold the localised minus (U+2212, eg fi/sv)
      // to ASCII so a coarse parse of the formatted percent gets the sign right.
      const numeric = parseFloat(
        String(props.percent)
          .replace('\u2212', '-')
          .replace(',', '.')
          .replace(/[^0-9.+-]/g, ''),
      );
      return Number.isNaN(numeric) ? 0 : numeric;
    });

    const direction = computed<Direction>(() => {
      if (changeValue.value > 0) {
        return 'up';
      }
      if (changeValue.value < 0) {
        return 'down';
      }
      return 'neutral';
    });

    // the arrow direction always reflects the actual value change, while the colour
    // (positive/negative) reflects whether that change is good or bad for the metric
    const directionClass = computed(() => {
      if (direction.value === 'neutral') {
        return 'evolutionBadge--neutral';
      }

      const increased = direction.value === 'up';
      const isPositive = props.isLowerValueBetter ? !increased : increased;

      return isPositive ? 'evolutionBadge--positive' : 'evolutionBadge--negative';
    });

    const formattedPercent = computed(() => {
      const label = typeof props.percent === 'number'
        ? `${props.percent}%`
        : String(props.percent).trim();

      const sign = label.charAt(0);
      if (changeValue.value > 0 && sign !== '+' && sign !== '-') {
        return `+${label}`;
      }

      return label;
    });

    return {
      direction,
      directionClass,
      formattedPercent,
    };
  },
});
</script>
