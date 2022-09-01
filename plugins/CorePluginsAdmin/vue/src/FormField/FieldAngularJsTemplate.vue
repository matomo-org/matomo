<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root"/>
</template>

<script lang="ts">
import {
  defineComponent,
  ref,
  watch,
  onMounted,
} from 'vue';
import { Matomo } from 'CoreHome';

function clone<T>(obj?: T): T|undefined {
  if (typeof obj === 'undefined') {
    return undefined;
  }

  return JSON.parse(JSON.stringify(obj));
}

export default defineComponent({
  props: {
    modelValue: null,
    formField: {
      type: null,
      required: true,
    },
    templateFile: {
      type: String,
      required: true,
    },
  },
  emits: ['update:modelValue'],
  inheritAttrs: false,
  setup(props, context) {
    const root = ref(null);

    const $element = window.$(
      `<div ng-include="'${props.templateFile}?cb=${Matomo.cacheBuster}'"></div>`,
    );

    const $timeout = Matomo.helper.getAngularDependency('$timeout');
    const $rootScope = Matomo.helper.getAngularDependency('$rootScope');

    const scope = $rootScope.$new();
    scope.formField = {
      ...clone(props.formField),
      value: clone(props.modelValue),
    };

    scope.$watch('formField.value', (newValue: unknown, oldValue: unknown) => {
      if (newValue !== oldValue
        && JSON.stringify(newValue) !== JSON.stringify(props.modelValue)
      ) {
        context.emit('update:modelValue', clone(newValue));
      }
    });

    watch(() => props.modelValue, (newValue) => {
      if (JSON.stringify(newValue) !== JSON.stringify(scope.formField.value)) {
        $timeout(() => {
          scope.formField.value = newValue;
        });
      }
    });

    watch(() => props.formField, (newValue) => {
      $timeout(() => {
        const currentValue = scope.formField.value;
        scope.formField = {
          ...clone(newValue),
          value: currentValue,
        };
      });
    }, { deep: true });

    // append on mount
    onMounted(() => {
      window.$(root.value! as HTMLElement).append($element);

      Matomo.helper.compileAngularComponents($element, {
        scope,
        params: {
          formField: {
            ...clone(props.formField),
            value: props.modelValue,
          },
        },
      });
    });

    return {
      root,
    };
  },
});
</script>
