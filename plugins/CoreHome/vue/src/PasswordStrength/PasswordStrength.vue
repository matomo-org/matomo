<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ul class="password-strength row" v-if="rules.length">
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
} from 'vue';
import { PasswordRule } from './PasswordStrength';

interface PasswordStrengthState {
  pwd: string;
  rules: PasswordRule[];
}

export default defineComponent({
  props: {
    validationRules: {
      type: Array as PropType<PasswordRule[]>,
      required: true,
    },
    password: {
      type: String,
      default: '',
    },
    externalInputSelector: {
      type: String,
      default: '',
    },
  },
  data(): PasswordStrengthState {
    return {
      pwd: '',
      rules: [],
    };
  },
  emits: ['check:isValid'],
  watch: {
    pwdValue: {
      immediate: true,
      handler(pwd: string) {
        const rulesValidity = [];
        this.rules.forEach((rule) => {
          if (!pwd.length && typeof rule.passed !== 'undefined') {
            delete rule.passed;
            return;
          }
          try {
            const regex = new RegExp(rule.validationRegex.replace(/^\/|\/$/g, ''));
            if (regex.test(pwd as string)) {
              rule.passed = true;
              rulesValidity.push(true);
            } else {
              rule.passed = false;
            }
          } catch (e) {
            console.log('Invalid password validation pattern:', e);
          }
        });
        if (this.rules.length > 0 && (rulesValidity.length === this.rules.length)) {
          this.$emit('check:isValid', true);
        }
      },
    },
  },
  computed: {
    pwdValue(): string {
      if (this.externalInputSelector?.length) {
        return this.pwd;
      }
      return this.password;
    },
  },
  mounted() {
    this.rules = this.validationRules.length
      ? this.validationRules.map((rule) => ({ ...rule }))
      : [];

    if (this.externalInputSelector?.length) {
      const input = document.querySelector<HTMLInputElement>(this.externalInputSelector);
      if (input) {
        input.addEventListener('input', this.handleExternalInput);
        this.pwd = input.value;
      }
    }
  },
  unmounted() {
    if (this.externalInputSelector?.length) {
      const input = document.querySelector<HTMLInputElement>(this.externalInputSelector);
      if (input) {
        input.removeEventListener('input', this.handleExternalInput);
      }
    }
  },
  methods: {
    ruleStatus(rule: PasswordRule): string {
      if (typeof rule.passed === 'undefined') {
        return 'undefined';
      }
      return rule.passed ? 'valid' : 'invalid';
    },
    handleExternalInput(event: Event) {
      const target = event.target as HTMLInputElement;
      this.pwd = target.value;
    },
  },
});
</script>
