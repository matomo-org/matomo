<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <div
    v-expand-on-hover="{ expander: 'expander', expandedClass: 'mtm-selector--expanded' }"
    id="header_message"
    class="mtm-selector"
    :class="{
      'mtm-selector--highlighted': latestVersionAvailable,
      header_info: !latestVersionAvailable || lastUpdateCheckFailed,
      update_available: latestVersionAvailable,
    }"
  >
    <!-- `header_info`, `update_available`, `title` and `piwikSelector__dropdown` carry no styles:
         plugins read them to find the message and its parts. -->
    <Passthrough v-if="latestVersionAvailable">
      <span
        v-if="isMultiServerEnvironment"
        class="mtm-selector__trigger title"
        ref="expander"
      >
        <span class="mtm-selector__icon" aria-hidden="true">
          <span class="icon-update" />
        </span>
        <span class="mtm-selector__label">
          {{ translate('General_NewUpdatePiwikX', latestVersionAvailable) }}
        </span>
      </span>
      <a
        v-else
        class="mtm-selector__trigger title"
        href="?module=CoreUpdater&action=newVersionAvailable"
        ref="expander"
      >
        <span class="mtm-selector__icon" aria-hidden="true">
          <span class="icon-update" />
        </span>
        <span class="mtm-selector__label">
          {{ translate('General_NewUpdatePiwikX', latestVersionAvailable) }}
        </span>
      </a>
    </Passthrough>
    <Passthrough v-else-if="isSuperUser && (isAdminArea || lastUpdateCheckFailed)">
      <a
        v-if="isInternetEnabled"
        class="mtm-selector__trigger title"
        v-html="$sanitize(updateCheck)"
      ></a>
      <a
        v-else
        class="mtm-selector__trigger title"
        :href="externalRawLink('https://matomo.org/changelog/')"
        target="_blank"
        rel="noreferrer noopener"
      >
        <span id="updateCheckLinkContainer" class="mtm-selector__label">
          {{ translate('CoreHome_SeeAvailableVersions') }}
        </span>
      </a>
    </Passthrough>

    <div class="mtm-selector__dropdown mtm-selector__dropdown--anchorLeft positionInViewport piwikSelector__dropdown">
      <div class="mtm-dropdownPanel">
        <div class="mtm-dropdownPanel__text">
          <span
            v-if="latestVersionAvailable && isSuperUser"
            v-html="$sanitize(updateNowText)"
          ></span>
          <span
            v-else-if="latestVersionAvailable && hasSomeViewAccess && !isAnonymous"
            v-html="$sanitize(updateAvailableText)"
          ></span>

          {{ translate('General_YouAreCurrentlyUsing', piwikVersion || '') }}
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from '../translate';
import ExpandOnHover from '../ExpandOnHover/ExpandOnHover';
import Passthrough from '../Passthrough/Passthrough.vue';
import { externalLink, externalRawLink } from '../externalLink';

export default defineComponent({
  props: {
    isMultiServerEnvironment: Boolean,
    lastUpdateCheckFailed: Boolean,
    latestVersionAvailable: String,
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
        const link = externalRawLink(`https://builds.matomo.org/matomo-${this.latestVersionAvailable}.zip`);
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
          externalLink('https://matomo.org/changelog/'),
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
      /* eslint-disable prefer-template */
      const matomoLink = externalLink('https://matomo.org/') + 'Matomo</a>';
      const changelogLinkStart = externalLink('https://matomo.org/changelog/');

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
