<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <div
    v-expand-on-hover="{expander: 'expander'}"
    id="header_message"
    class="piwikSelector"
    :class="{
      header_info: !latestVersionAvailable || lastUpdateCheckFailed,
      update_available: latestVersionAvailable
    }"
  >
    <Passthrough v-if="latestVersionAvailable && !isPiwikDemo">
      <span
        v-if="isMultiServerEnvironment"
        class="title"
        style="cursor:pointer;"
        ref="expander"
      >
        {{ translate('General_NewUpdatePiwikX', latestVersionAvailable) }}
        <span class="icon-warning"></span>
      </span>
      <a
        v-else
        class="title"
        href="?module=CoreUpdater&action=newVersionAvailable"
        style="cursor:pointer;"
        ref="expander"
      >
        {{ translate('General_NewUpdatePiwikX', latestVersionAvailable) }}
        <span class="icon-warning"></span>
      </a>
    </Passthrough>
    <Passthrough v-else-if="isSuperUser && (isAdminArea || lastUpdateCheckFailed)">
      <a v-if="isInternetEnabled" class="title" v-html="$sanitize(updateCheck)"></a>
      <a
        v-else
        class="title"
        href="https://matomo.org/changelog/"
        target="_blank"
        rel="noreferrer noopener"
      >
        <span id="updateCheckLinkContainer">
          {{ translate('CoreHome_SeeAvailableVersions') }}
        </span>
      </a>
    </Passthrough>

    <div class="dropdown positionInViewport">
      <span v-if="latestVersionAvailable && isSuperUser" v-html="$sanitize(updateNowText)"></span>
      <span
        v-else-if="latestVersionAvailable && !isPiwikDemo && hasSomeViewAccess && !isAnonymous"
        v-html="$sanitize(updateAvailableText)"
      ></span>

      {{ translate('General_YouAreCurrentlyUsing', piwikVersion) }}
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from '../translate';
import ExpandOnHover from '../ExpandOnHover/ExpandOnHover';
import Passthrough from '../Passthrough/Passthrough.vue';

export default defineComponent({
  props: {
    isMultiServerEnvironment: Boolean,
    lastUpdateCheckFailed: Boolean,
    latestVersionAvailable: String,
    isPiwikDemo: Boolean,
    isSuperUser: Boolean,
    isAdminArea: Boolean,
    isInternetEnabled: Boolean,
    updateCheck: String,
    isAnonymous: Boolean,
    hasSomeViewAccess: Boolean,
    contactEmail: String,
    piwikVersion: String,
  },
  components: {
    Passthrough,
  },
  directives: {
    ExpandOnHover,
  },
  computed: {
    updateNowText() {
      let text = '';

      if (this.isMultiServerEnvironment) {
        const link = `https://builds.matomo.org/piwik-${this.latestVersionAvailable}.zip`;
        text = translate(
          'CoreHome_OneClickUpdateNotPossibleAsMultiServerEnvironment',
          `<a rel="noreferrer noopener" href="${link}">builds.matomo.org</a>`,
        );
      } else {
        text = translate(
          'General_PiwikXIsAvailablePleaseUpdateNow',
          this.latestVersionAvailable || '',
          '<br /><a href="index.php?module=CoreUpdater&amp;action=newVersionAvailable">',
          '</a>',
          '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/changelog/">',
          '</a>',
        );
      }

      return `${text}<br/>`;
    },
    updateAvailableText() {
      const updateSubject = translate(
        'General_NewUpdatePiwikX',
        this.latestVersionAvailable || '',
      );
      const matomoLink = '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/">Matomo</a>';
      const changelogLinkStart = '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/changelog/">';

      const text = translate(
        'General_PiwikXIsAvailablePleaseNotifyPiwikAdmin',
        `${matomoLink} ${changelogLinkStart}${this.latestVersionAvailable}</a>`,
        `<a href="mailto:${this.contactEmail}?subject=${encodeURIComponent(updateSubject)}">`,
        '</a>',
      );

      return `${text}<br />`;
    },
  },
});
</script>
