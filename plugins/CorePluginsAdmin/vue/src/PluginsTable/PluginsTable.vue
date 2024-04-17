<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="title"
    class="pluginsManagement"
    v-plugin-management="{}"
  >
    <p class="row pluginsFilter" v-plugin-filter>
        <span class="origin">
            <strong style="margin-right: 3.5px">{{ translate('CorePluginsAdmin_Origin') }}</strong>
            <a data-filter-origin="all" href="#" class="active">
              {{ translate('General_All') }}<span class="counter"></span>
            </a> |
            <a data-filter-origin="core" href="#">
              {{ translate('CorePluginsAdmin_OriginCore') }}<span class="counter"></span>
            </a> |
            <a data-filter-origin="official" href="#">
              {{ translate('CorePluginsAdmin_OriginOfficial') }}<span class="counter"></span>
            </a> |
            <a data-filter-origin="thirdparty" href="#">
              {{ translate('CorePluginsAdmin_OriginThirdParty') }}<span class="counter"></span>
            </a>
        </span>

      <span class="status">
            <strong style="margin-right: 3.5px">{{ translate('CorePluginsAdmin_Status') }}</strong>
            <a data-filter-status="all" href="#" class="active">
              {{ translate('General_All') }}<span class="counter"></span>
            </a> |
            <a data-filter-status="active" href="#">
              {{ translate('CorePluginsAdmin_Active') }}<span class="counter"></span>
            </a> |
            <a data-filter-status="inactive" href="#">
              {{ translate('CorePluginsAdmin_Inactive') }}<span class="counter"></span>
            </a>
        </span>
    </p>

    <div id="confirmUninstallPlugin" class="ui-confirm">
      <h2 id="uninstallPluginConfirm">{{ translate('CorePluginsAdmin_UninstallConfirm') }}</h2>
      <input role="yes" type="button" :value="translate('General_Yes')"/>
      <input role="no" type="button" :value="translate('General_No')"/>
    </div>

    <table v-content-table>
      <thead>
      <tr>
        <th>{{isTheme ? translate('CorePluginsAdmin_Theme') : translate('General_Plugin') }}</th>
        <th>{{ translate('General_Description') }}</th>
        <th class="status">{{ translate('CorePluginsAdmin_Status') }}</th>
        <th class="action-links" v-if="displayAdminLinks">{{ translate('General_Action') }}</th>
      </tr>
      </thead>
      <tbody id="plugins">
      <tr
        v-for="(plugin, name) in pluginsToDisplay"
        :key="name"
        :class="plugin.activated ? 'active-plugin' : 'inactive-plugin'"
        :data-filter-status="plugin.activated ? 'active' : 'inactive'"
        :data-filter-origin="getPluginOrigin(plugin)"
      >
        <td class="name">
          <a :name="name"></a>
          <a
            v-plugin-name="{pluginName: name}"
            v-if="!plugin.isCorePlugin && marketplacePluginNames.indexOf(name) !== -1"
          >{{ name }}</a>
          <span v-else>{{ name }}</span>

          <span
            class="plugin-version"
            :title="plugin.isCorePlugin
              ? translate('CorePluginsAdmin_CorePluginTooltip')
              : undefined"
          >
            ({{ plugin.isCorePlugin
              ? translate('CorePluginsAdmin_OriginCore')
              : plugin.info.version }})
          </span>

          <span v-if="pluginNamesHavingSettings.indexOf(name) !== -1">
            <br /><br />
            <a
              :href="`${generalSettingsLink}#${name}`"
              class="settingsLink"
            >{{ translate('General_Settings') }}</a>
          </span>
        </td>
        <td class="desc">
          <div class="plugin-desc-missingrequirements">
            <span v-if="plugin.missingRequirements">
              {{ plugin.missingRequirements }}
              <br />
            </span>
          </div>
          <div class="plugin-desc-text">
            {{ plugin.info.description.replaceAll('\n', '<br/>') }}

            <span
              v-if="plugin.info?.homepage && !isMatomoUrl(plugin.info?.homepage)"
              class="plugin-homepage"
            >
              <a
                target="_blank"
                rel="noreferrer noopener"
                :href="plugin.info.homepage"
              >
                ({{ translate('CorePluginsAdmin_PluginHomepage').replaceAll(' ', '&nbsp;') }})
              </a>
            </span>

            <div class="plugin-donation" v-if="plugin.info?.donate?.length">
              {{ translate('CorePluginsAdmin_LikeThisPlugin') }}

              <a @click.prevent class="plugin-donation-link" :data-overlay-id="`overlay-${name}`">
                {{ translate('CorePluginsAdmin_ConsiderDonating') }}
              </a>

              <div
                :id="`overlay-${name}`"
                class="donation-overlay ui-confirm"
                :title="translate('CorePluginsAdmin_LikeThisPlugin')"
              >
                <p>{{ translate('CorePluginsAdmin_CommunityContributedPlugin') }}</p>

                <p v-html="$sanitize(translate(
                  'CorePluginsAdmin_ConsiderDonatingCreatorOf',
                  `<b>${name}</b>`,
                ))"></p>

                <div class="donation-links">
                  <a
                    v-if="plugin.info?.donate?.paypal"
                    class="donation-link paypal"
                    target="_blank"
                    rel="noreferrer noopener"
                    :href="getPluginDonateLink(name, plugin.info.donate.paypal)"
                  >
                    <img src="plugins/CorePluginsAdmin/images/paypal_donate.png" height="30"/>
                  </a>

                  <a
                    v-if="plugin.info?.donate?.flattr"
                    class="donation-link flattr"
                    target="_blank"
                    rel="noreferrer noopener"
                    :href="plugin.info.donate?.flattr"
                  >
                    <img
                      class="alignnone"
                      title="Flattr"
                      alt=""
                      src="plugins/CorePluginsAdmin/images/flattr.png"
                      height="29"
                    />
                  </a>

                  <div
                    v-if="plugin.info?.donate?.bitcoin"
                    class="donation-link bitcoin"
                  >
                    <span>Donate Bitcoins to:</span>
                    <a :href="`bitcoin:${encodeURIComponent(plugin.info.donate.bitcoin)}`">
                      {{ plugin.info.donate.bitcoin }}
                    </a>
                  </div>
                </div>
                <input role="no" type="button" :value="translate('General_Close')"/>
              </div>
            </div>
          </div>
          <div class="plugin-license" v-if="plugin.info?.license">
            <a
              v-if="plugin.info?.license_file"
              :title="translate('CorePluginsAdmin_LicenseHomepage')"
              rel="noreferrer noopener"
              target="_blank"
              :href="`index.php?module=CorePluginsAdmin&action=showLicense&pluginName=${name}`"
            >
              {{ plugin.info.license }}
            </a>
            <span v-else>
              {{ plugin.info.license }}
            </span>
          </div>
          <div class="plugin-author" v-if="plugin.info?.authors">
            By
            <span v-for="(author, index) in plugin.info.authors.filter((a) => a.name)" :key="index">
              <a
                v-if="author.homepage"
                :title="translate('CorePluginsAdmin_AuthorHomepage')"
                :href="author.homepage"
                rel="noreferrer noopener"
                target="_blank"
              >{{ author.name }}</a>
              <span v-else>{{ author.name }}</span>
              <span
                v-if="plugin.info.authors.length - 1 > index"
                style="margin-right:3.5px"
              >,</span>
            </span>.
          </div>
        </td>
        <td
          class="status"
          :style="{'border-left-width': isDefaultTheme(name) ? '0' : undefined}"
        >
          <span v-if="!isDefaultTheme(name)">
            <span v-if="plugin.activated">
              {{ translate('CorePluginsAdmin_Active') }}
            </span>
            <span v-else>
              {{ translate('CorePluginsAdmin_Inactive') }}
              <span v-if="plugin.uninstallable && displayAdminLinks">
                <br/>
                -
                <a :data-plugin-name="name" class="uninstall" :href="getUninstallLink(name)">
                  {{ translate('CorePluginsAdmin_ActionUninstall') }}
                </a>
              </span>
            </span>
          </span>
        </td>

        <td
          class="togl action-links"
          v-if="displayAdminLinks"
          :style="{'border-left-width': isDefaultTheme(name) ? 0 : undefined}"
        >
          <span v-if="!isDefaultTheme(name)">
            <span v-if="plugin.invalid && plugin.alwaysActivated">-</span>
            <span v-else>
              <a
                v-if="plugin.activated"
                :href="getDeactivateLink(name)"
              >
                {{ translate('CorePluginsAdmin_Deactivate') }}
              </a>
              <span v-else-if="plugin.missingRequirements">-</span>
              <a
                v-else
                :href="getActivateLink(name)"
              >
                {{ translate('CorePluginsAdmin_Activate') }}
              </a>
            </span>
          </span>
        </td>
      </tr>
      </tbody>
    </table>

    <div class="tableActionBar" v-if="displayAdminLinks">
      <a
        v-if="isTheme"
        :href="themeOverviewLink"
      >
        <span class="icon-add"></span> {{ translate('CorePluginsAdmin_InstallNewThemes') }}
      </a>
      <a
        v-else
        :href="overviewLink"
      >
        <span class="icon-add"></span> {{ translate('CorePluginsAdmin_InstallNewPlugins') }}
      </a>
    </div>

    <div class="footer-message">
      {{ translate('CorePluginsAdmin_AlwaysActivatedPluginsList', pluginsAlwaysActivated) }}
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock, ContentTable, MatomoUrl } from 'CoreHome';
import PluginManagement from '../Plugins/PluginManagement';
import PluginFilter from '../Plugins/PluginFilter';
import PluginName from '../Plugins/PluginName';

interface PluginInfo {
  isCorePlugin?: boolean;
  isOfficialPlugin?: boolean;
  alwaysActivated?: boolean;
}

export default defineComponent({
  props: {
    isTheme: Boolean,
    displayAdminLinks: Boolean,
    pluginsInfo: {
      type: Object,
      required: true,
    },
    uninstallNonce: {
      type: String,
      required: true,
    },
    deactivateNonce: {
      type: String,
      required: true,
    },
    activateNonce: {
      type: String,
      required: true,
    },
    marketplacePluginNames: {
      type: Array,
      required: true,
    },
    pluginNamesHavingSettings: {
      type: Array,
      required: true,
    },
    title: {
      type: String,
      required: true,
    },
  },
  components: {
    ContentBlock,
  },
  directives: {
    PluginManagement,
    PluginFilter,
    ContentTable,
    PluginName,
  },
  methods: {
    getPluginOrigin(plugin: PluginInfo) {
      if (plugin.isCorePlugin) {
        return 'core';
      }

      if (plugin.isOfficialPlugin) {
        return 'official';
      }

      return 'thirdparty';
    },
    getPluginDonateLink(pluginName: string, business: string) {
      return `https://www.paypal.com/cgi-bin/webscr?${MatomoUrl.stringify({
        cmd: '_donations',
        item_name: `Matomo Plugin ${pluginName}`,
        bn: 'PP-DonationsBF:btn_donateCC_LG.gif:NonHosted',
        business,
      })}`;
    },
    getUninstallLink(pluginName: string) {
      return `?${MatomoUrl.stringify({
        module: 'CorePluginsAdmin',
        action: 'uninstall',
        pluginName,
        nonce: this.uninstallNonce,
      })}`;
    },
    isDefaultTheme(pluginName: string) {
      return this.isTheme && pluginName === 'Morpheus';
    },
    getDeactivateLink(pluginName: string) {
      return `?${MatomoUrl.stringify({
        module: 'CorePluginsAdmin',
        action: 'deactivate',
        pluginName,
        nonce: this.deactivateNonce,
        redirectTo: 'referrer',
      })}`;
    },
    getActivateLink(pluginName: string) {
      return `?${MatomoUrl.stringify({
        module: 'CorePluginsAdmin',
        action: 'activate',
        pluginName,
        nonce: this.activateNonce,
        redirectTo: 'referrer',
      })}`;
    },
    isMatomoUrl(url: string) {
      try {
        const pluginHost = (new URL(url)).host;

        return this.matomoHosts.indexOf(pluginHost) !== -1;
      } catch (error) {
        // the plugin may provide a broken/invalid url
        return false;
      }
    },
  },
  computed: {
    pluginsToDisplay() {
      const pluginsInfo = this.pluginsInfo as Record<string, PluginInfo>;

      return Object.fromEntries(
        Object.entries(pluginsInfo).filter(([, info]) => {
          if (this.isTheme) {
            return true;
          }

          const { alwaysActivated } = info;
          return typeof alwaysActivated !== 'undefined'
            && alwaysActivated !== null
            && !alwaysActivated;
        }),
      );
    },
    generalSettingsLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'CoreAdminHome',
        action: 'generalSettings',
      })}`;
    },
    matomoHosts() {
      return [
        'piwik.org',
        'www.piwik.org',
        'matomo.org',
        'www.matomo.org',
      ];
    },
    themeOverviewLink() {
      const query = MatomoUrl.stringify({ module: 'Marketplace', action: 'overview' });
      const hash = MatomoUrl.stringify({ pluginType: 'themes' });

      return `?${query}#?${hash}`;
    },
    overviewLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'Marketplace',
        action: 'overview',
        sort: '',
      })}`;
    },
    pluginsAlwaysActivated() {
      const pluginsInfo = this.pluginsInfo as Record<string, PluginInfo>;
      return Object.entries(pluginsInfo)
        .filter(([, plugin]) => plugin.alwaysActivated)
        .map(([name]) => name)
        .join(', ');
    },
  },
});
</script>
