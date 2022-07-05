<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="translate('Diagnostics_ConfigFileTitle')"
    feature="true"
  >
    <p>
      <span v-html="$sanitize(configFileIntro)"></span>
      <span
        v-html="$sanitize(translate('Diagnostics_HideUnchanged', '<a>', '</a>'))"
        @click="onHideUnchanged($event)"
      ></span>
    </p>

    <h3>{{ translate('Diagnostics_Sections') }}</h3>
    <p>
      <span v-for="(values, category) in allConfigValues" :key="category">
        <a :href="`#${category}`">{{ category }}</a><br />
      </span>
    </p>

    <table class="diagnostics configfile" v-content-table>
      <tbody>
      <Passthrough v-for="(configValues, category) in allConfigValues" :key="category">

      <tr>
        <td colspan="3">
          <a :name="category"></a><h3>{{ category }}</h3>
        </td>
      </tr>

      <tr
        v-for="(configEntry, key) in configValues"
        :key="key"
        :class="{'custom-value': configEntry.isCustomValue}"
        v-show="configEntry.isCustomValue || !hideGlobalConfigValues"
      >
        <td class="name">
          {{ `${key}${configEntry.value instanceof Array || typeof configEntry.value === 'object'
            ? '[]' : ''}` }}
        </td>
        <td class="value" v-html="$sanitize(humanReadableValue(configEntry.value))"></td>
        <td class="description">
          {{ configEntry.description }}

          <span
            v-if="(configEntry.isCustomValue || configEntry.value === null)
              && configEntry.defaultValue !== null"
          >
            <br v-if="configEntry.description" />
            {{ translate('General_Default') }}:
            <span
              class="defaultValue"
              v-html="$sanitize(humanReadableValue(configEntry.defaultValue))"
            ></span>
          </span>
        </td>
      </tr>
      </Passthrough>
      </tbody>
    </table>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  translate,
  ContentTable,
  Passthrough,
} from 'CoreHome';

interface ConfigFileState {
  hideGlobalConfigValues: boolean;
}

export default defineComponent({
  props: {
    allConfigValues: {
      type: Object,
      required: true,
    },
  },
  components: {
    ContentBlock,
    Passthrough,
  },
  directives: {
    ContentTable,
  },
  data(): ConfigFileState {
    return {
      hideGlobalConfigValues: false,
    };
  },
  methods: {
    humanReadableValue(value: unknown) {
      if (value === false) {
        return 'false';
      }

      if (value === true) {
        return 'true';
      }

      if (value === null) {
        return 'null';
      }

      if (value === '') {
        return '\'\'';
      }

      if (Array.isArray(value)) {
        return (value as unknown[]).join(', ');
      }

      if (typeof value === 'object'
        && Object.keys(value as Record<string, unknown>).length === 0
      ) {
        return '[]';
      }

      if (typeof value === 'object'
        && Object.keys(value as Record<string, unknown>).length > 0
      ) {
        return `<pre>${JSON.stringify(value, null, 4)}</pre>`;
      }

      return `${value}`;
    },
    onHideUnchanged(event: Event) {
      if ((event.target as HTMLElement).tagName !== 'A') {
        return;
      }

      this.hideGlobalConfigValues = !this.hideGlobalConfigValues;
    },
  },
  computed: {
    configFileIntro() {
      return translate(
        'Diagnostics_ConfigFileIntroduction',
        '<code>"config/config.ini.php"</code>',
      );
    },
  },
});
</script>
