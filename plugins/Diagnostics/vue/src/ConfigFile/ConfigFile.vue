<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="translate('Diagnostics_ConfigFileTitle')"
    feature="true"
  >
    <p>
      <span v-html="$sanitize(configFileIntro)" style="margin-right:3.5px"></span>
      <span
        v-html="$sanitize(translate('Diagnostics_HideUnchanged', '<a>', '</a>'))"
        @click="onHideUnchanged($event)"
      ></span>
    </p>

    <h3>{{ translate('Diagnostics_Sections') }}</h3>
    <Passthrough v-for="(values, category) in allConfigValues" :key="category">
      <a :href="`#${category}`">{{ category }}</a><br />
    </Passthrough>

    <p/>

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
          {{ `${key}${configEntry.value !== null
            && (configEntry.value instanceof Array
              || typeof configEntry.value === 'object'
            ) ? '[]' : ''}` }}
        </td>
        <td class="value" v-html="$sanitize(humanReadableValue(configEntry.value))"></td>
        <td class="description">
          <span v-html="$sanitize(configEntry.description)"></span>

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
        return '';
      }

      if (value === '') {
        return '\'\'';
      }

      if (typeof value === 'object'
        && Object.keys(value as Record<string, unknown>).length === 0
      ) {
        return '[]';
      }

      if (typeof value === 'object'
        && Object.keys(value as Record<string, unknown>).length > 0
      ) {
        return `<div class="pre">${JSON.stringify(value, null, 4)}</div>`;
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
