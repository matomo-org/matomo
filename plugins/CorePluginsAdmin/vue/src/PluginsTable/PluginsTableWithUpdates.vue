<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    v-if="Object.keys(pluginsHavingUpdate).length"
    :content-title="translate(
      'CorePluginsAdmin_NUpdatesAvailable',
      Object.keys(pluginsHavingUpdate).length,
    )"
  >
    <p>{{ translate('CorePluginsAdmin_InfoPluginUpdateIsRecommended') }}</p>

    <div v-if="isPluginsAdminEnabled">
      <a
        id="update-selected-plugins"
        @click.prevent="updateSelectedPlugins()"
        :class="{btn: true, disabled: isUpdateLinkDisabled}"
      >
        {{ translate('CorePluginsAdmin_UpdateSelected') }}
      </a>
    </div>
    <table v-content-table>
      <thead>
      <tr>
        <th v-if="isPluginsAdminEnabled">
          <span class="checkbox-container">
            <label>
              <input
                type="checkbox"
                id="select-plugin-all"
                @change="selectAll($event.target.checked)"
              />
              <span></span>
            </label>
          </span>
        </th>
        <th>{{ translate('General_Plugin') }}</th>
        <th class="num">{{ translate('CorePluginsAdmin_Version') }}</th>
        <th>{{ translate('General_Description') }}</th>
        <th class="status">{{ translate('CorePluginsAdmin_Status') }}</th>
        <th
          v-if="isPluginsAdminEnabled"
          class="action-links"
        >{{ translate('General_Action') }}</th>
      </tr>
      </thead>
      <tbody id="plugins">
      <tr
        v-for="(plugin, name) in pluginsHavingUpdate"
        :key="name"
        :class="plugin.isActivated ? 'active-plugin' : 'inactive-plugin'"
      >
        <td class="select-cell" v-if="isPluginsAdminEnabled">
          <span class="checkbox-container">
              <label>
                  <input
                    type="checkbox"
                    :id="`select-plugin-${plugin.name}`"
                    :disabled="typeof plugin.isDownloadable !== 'undefined'
                      && plugin.isDownloadable !== null
                      && !plugin.isDownloadable"
                    v-model="pluginsSelected[name]"
                  />
                  <span></span>
              </label>
          </span>
        </td>
        <td class="name">
          <a @click.prevent v-plugin-name="{pluginName:plugin.name}" class="plugin-details">
            {{ plugin.name }}
          </a>
        </td>
        <td class="vers">
          <a
            v-if="plugin.changelog?.url"
            :href="plugin.changelog.url"
            :title="translate('CorePluginsAdmin_Changelog')"
            target="_blank"
            rel="noreferrer noopener"
          >{{ plugin.currentVersion }} => {{ plugin.latestVersion }}</a>

          <span v-else>{{ plugin.currentVersion }} => {{ plugin.latestVersion }}</span>
        </td>
        <td class="desc">
          {{ plugin.description }}

          <MissingReqsNotice :plugin="plugin"/>
        </td>
        <td class="status">
          {{ plugin.isActivated
            ? translate('CorePluginsAdmin_Active')
            : translate('CorePluginsAdmin_Inactive') }}
        </td>
        <td class="togl action-links" v-if="isPluginsAdminEnabled">
          <span
            v-if="typeof plugin.isDownloadable !== 'undefined'
              && plugin.isDownloadable !== null
              && !plugin.isDownloadable"
            :title="`${translate('CorePluginsAdmin_PluginNotDownloadable')} ${plugin.isPaid
              ? translate('CorePluginsAdmin_PluginNotDownloadablePaidReason') : ''}`"
          >
            {{ translate('CorePluginsAdmin_NotDownloadable') }}
          </span>
          <a
            v-else-if="isMultiServerEnvironment"
            v-show="!isPluginDownloadLinkClicked"
            @click="isPluginDownloadLinkClicked = true"
            :href="downloadPluginLink(plugin)"
          >
            {{ translate('General_Download') }}
          </a>
          <a
            v-else-if="plugin.missingRequirements.length === 0"
            :href="updatePluginLink(plugin)"
          >{{ translate('CoreUpdater_UpdateTitle') }}</a>
          <span v-else>-</span>
        </td>
      </tr>
      </tbody>
    </table>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  useExternalPluginComponent,
  ContentTable,
  MatomoUrl,
} from 'CoreHome';
import PluginName from '../Plugins/PluginName';

const MissingReqsNotice = useExternalPluginComponent('Marketplace', 'MissingReqsNotice');

interface PluginsTableWithUpdatesState {
  isUpdating: boolean;
  isPluginDownloadLinkClicked: boolean;
  pluginsSelected: Record<string, boolean>;
}

interface PluginInfo {
  name: string;
  isDownloadable?: boolean|null;
}

export default defineComponent({
  props: {
    pluginsHavingUpdate: {
      type: Object,
      required: true,
    },
    pluginUpdateNonces: {
      type: Object,
      required: true,
    },
    updateNonce: {
      type: String,
      required: true,
    },
    isMultiServerEnvironment: Boolean,
    isPluginsAdminEnabled: Boolean,
  },
  components: {
    ContentBlock,
    MissingReqsNotice,
  },
  directives: {
    ContentTable,
    PluginName,
  },
  data(): PluginsTableWithUpdatesState {
    return {
      isUpdating: false,
      isPluginDownloadLinkClicked: false,
      pluginsSelected: {},
    };
  },
  computed: {
    isUpdateLinkDisabled() {
      return this.isUpdating
        || !Object.keys(this.pluginsSelected).length
        || !Object.values(this.pluginsSelected).some((s) => !!s);
    },
  },
  methods: {
    selectAll(checked: boolean) {
      const plugins = this.pluginsHavingUpdate as Record<string, PluginInfo>;
      Object.entries(plugins).forEach(([name, plugin]) => {
        if (plugin.isDownloadable !== null
          && typeof plugin.isDownloadable !== 'undefined'
          && !plugin.isDownloadable
        ) {
          return;
        }

        this.pluginsSelected[name] = checked;
      });
    },
    downloadPluginLink(plugin: PluginInfo) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'Marketplace',
        action: 'download',
        pluginName: plugin.name,
        nonce: this.pluginUpdateNonces[plugin.name],
      })}`;
    },
    updatePluginLink(plugin: PluginInfo) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'Marketplace',
        action: 'updatePlugin',
        pluginName: plugin.name,
        nonce: this.updateNonce,
      })}`;
    },
    updateSelectedPlugins() {
      this.isUpdating = true;

      const pluginsToUpdate = Object.entries(this.pluginsSelected)
        .filter(([, selected]) => selected)
        .map(([name]) => name);

      MatomoUrl.updateUrl({
        ...MatomoUrl.urlParsed.value,
        module: 'Marketplace',
        action: 'updatePlugin',
        nonce: this.updateNonce,
        pluginName: pluginsToUpdate.join(','),
      });
    },
  },
});
</script>
