<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ActivityIndicator
    v-if="loading"
    :loading="true"
    :loading-message="translate('General_LoadingData')"
  />
  <Alert
    v-else-if="loadingFailed"
    severity="danger"
  >
    {{ translate('General_ErrorRequest', '', '') }}
  </Alert>
  <component
    v-else-if="componentToRender"
    :is="componentToRender"
    v-bind="componentProps"
  />
</template>

<script lang="ts">
import {
  Component,
  defineComponent,
  markRaw,
} from 'vue';
import ActivityIndicator from '../ActivityIndicator/ActivityIndicator.vue';
import Alert from '../Alert/Alert.vue';
import importPluginUmd from '../importPluginUmd';
import { Widget as WidgetData } from './types';

interface ClientWidgetRendererState {
  componentToRender: Component|null;
  loading: boolean;
  loadingFailed: boolean;
}

export default defineComponent({
  props: {
    widget: {
      type: Object,
      required: true,
    },
    widgetized: Boolean,
  },
  components: {
    ActivityIndicator,
    Alert,
  },
  data(): ClientWidgetRendererState {
    return {
      componentToRender: null,
      loading: false,
      loadingFailed: false,
    };
  },
  watch: {
    widget: {
      handler() {
        this.loadComponent();
      },
      immediate: true,
    },
  },
  computed: {
    componentProps() {
      const widget = this.widget as WidgetData;
      return {
        ...(widget.clientComponent?.props || {}),
        uniqueId: widget.uniqueId,
        widgetName: widget.name,
        widgetized: this.widgetized,
        isWidget: this.widgetized,
        isWide: widget.isWide,
      };
    },
  },
  methods: {
    async loadComponent() {
      const widget = this.widget as WidgetData;
      const { clientComponent } = widget;
      this.loading = true;
      this.loadingFailed = false;
      this.componentToRender = null;

      try {
        if (!clientComponent) {
          throw new Error('Missing client-rendered widget metadata');
        }

        const pluginModule = await importPluginUmd(
          clientComponent.plugin,
        ) as Record<string, unknown>|undefined;
        const component = pluginModule?.[clientComponent.name];

        if (!component) {
          throw new Error(
            `Unknown widget component ${clientComponent.plugin}.${clientComponent.name}`,
          );
        }

        this.componentToRender = markRaw(component as Component);
      } catch (e) {
        console.error(e);
        this.loadingFailed = true;
      } finally {
        this.loading = false;
      }
    },
  },
});
</script>
