<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template v-if="validationRules.length">
  <ul class="password-strength row">
    <li v-for="rule in rules"
        :key="rule.ruleText"
        :class="`col s12 xl6 rule rule-${ruleStatus(rule)}`"
    >
      <span
        :class="{
          'icon': true,
          'icon-ok': ruleStatus(rule) === 'valid',
          'icon-close': ruleStatus(rule) === 'invalid',
          'icon-circle': ruleStatus(rule) === 'undefined',
        }"></span>
      {{ rule.ruleText }}
    </li>
  </ul>
</template>

<script lang="ts">
import {
  defineComponent,
  PropType,
  reactive,
  watch,
} from 'vue';
import { PasswordRule } from './PasswordStrength';

export default defineComponent({
  props: {
    validationRules: {
      type: Array as PropType<PasswordRule[]>,
      required: true,
    },
    password: {
      type: String,
      required: true,
    },
    submitted: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['check:isValid'],
  setup(props, { emit }) {
    // local reactive copy of the rules
    const rules = reactive(
      props.validationRules.map((rule) => ({ ...rule })),
    );

    // Watch for password changes and update validation status
    watch(
      () => [props.password, props.submitted],
      ([pwd, sub]) => {
        const rulesValidity = [];
        rules.forEach((rule) => {
          try {
            const regex = new RegExp(rule.validationRegex.replace(/^\/|\/$/g, ''));
            if (regex.test(pwd as string)) {
              rule.passed = true;
              rulesValidity.push(true);
            } else if (sub) {
              rule.passed = false;
            } else {
              delete rule.passed;
            }
          } catch (e) {
            console.log('Invalid password validation pattern:', e);
          }
        });
        if (rules.length > 0 && (rulesValidity.length === rules.length)) {
          emit('check:isValid', true);
        }
      },
      { immediate: true },
    );

    return { rules };
  },
  methods: {
    ruleStatus(rule: PasswordRule): string {
      if (typeof rule.passed === 'undefined') {
        return 'undefined';
      }
      return rule.passed ? 'valid' : 'invalid';
    },
  },
});
</script>
