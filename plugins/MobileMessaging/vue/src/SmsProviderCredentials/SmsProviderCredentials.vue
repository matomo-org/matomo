<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-if="fields">
    <Field
      v-for="field in fields"
      :key="field.name"
      :uicontrol="field.type"
      :name="field.name"
      :model-value="modelValue?.[field.name]"
      @update:model-value="$emit('update:modelValue', { ...modelValue, [field.name]: $event })"
      :title="translate(field.title)"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, reactive } from 'vue';
import { AjaxHelper } from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

interface FieldInfo {
  name: string;
  type: string;
  title: string;
}

const allFieldsByProvider = reactive<Record<string, FieldInfo[]>>({});

export default defineComponent({
  props: {
    provider: {
      type: String,
      required: true,
    },
    modelValue: Object,
  },
  emits: ['update:modelValue'],
  components: {
    Field,
  },
  watch: {
    provider() {
      // unset credentials when new provider is chosen
      this.$emit('update:modelValue', null);

      // fetch fields for provider
      this.getCredentialFields();
    },
  },
  created() {
    this.getCredentialFields();
  },
  methods: {
    getCredentialFields() {
      if (allFieldsByProvider[this.provider]) {
        this.$emit(
          'update:modelValue',
          Object.fromEntries(
            (allFieldsByProvider[this.provider] as FieldInfo[]).map((f) => [f.name, null]),
          ),
        );
        return;
      }

      AjaxHelper.fetch<FieldInfo[]>({
        module: 'MobileMessaging',
        action: 'getCredentialFields',
        provider: this.provider,
      }).then((fields) => {
        this.$emit('update:modelValue', Object.fromEntries(fields.map((f) => [f.name, null])));
        allFieldsByProvider[this.provider] = fields;
      });
    },
  },
  computed: {
    fields() {
      return allFieldsByProvider[this.provider];
    },
  },
});
</script>
