<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="capabilitiesEdit"
    :class="{busy: isBusy}"
  >
    <div
      class="chip"
      v-if="capabilitiesSet.capability.id"
      v-for="capability in availableCapabilities"
    >
      <span
        class="capability-name"
        :title="`${capability.description} ${
          isIncludedInRole(capability)
            ? `<br/><br/>${translate('UsersManager_IncludedInUsersRole')}`
            : ''
        }`"
      >
        {{ capability.category }}: {{ capability.name }}
      </span>
      <span
        class="icon-close"
        v-if="!isIncludedInRole(capability)"
        @click="capabilityToAddOrRemoveId = capability.id; onToggleCapability(false)"
      />
    </div>
    <div
      class="addCapability"
    >
      <Field
        @change="onToggleCapability(true)"
        v-model="capabilityToAddOrRemoveId"
        :disabled="isBusy"
        v-if="availableCapabilitiesGrouped.length"
        uicontrol="expandable-select"
        name="add_capability"
        :full-width="true"
        :options="availableCapabilitiesGrouped"
      >
      </Field>
    </div>
    <div class="ui-confirm confirmCapabilityToggle modal" ref="confirmCapabilityToggleModal">
      <div class="modal-content">
        <h2
          v-if="isAddingCapability"
          v-html="confirmAddCapabilityToggleContent"
        ></h2>
        <h2
          v-if="!isAddingCapability"
          v-html="confirmCapabilityToggleContent"
        ></h2>
      </div>
      <div class="modal-footer">
        <a
          href=""
          class="modal-action modal-close btn"
          @click="toggleCapability()"
        >{{ translate('General_Yes') }}</a>
        <a
          href=""
          class="modal-action modal-close modal-no"
          @click="capabilityToAddOrRemove = null;capabilityToAddOrRemoveId = null"
        >
          {{ translate('General_No') }}
        </a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate, AjaxHelper } from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import CapabilitiesStore from '../CapabilitiesStore/CapabilitiesStore';
import Capability from '../CapabilitiesStore/Capability';

interface CapabilitiesEditState {
  isBusy: boolean;
  theCapabilities: Capability[];
  isAddingCapability: boolean;
  capabilityToAddOrRemoveId: string|null;
  capabilityToAddOrRemove: Capability|null;
}

const { $ } = window;

export default defineComponent({
  props: {
    idsite: [String, Number],
    siteName: String,
    userLogin: String,
    userRole: String,
    capabilities: Array,
  },
  components: {
    Field,
  },
  data(): CapabilitiesEditState {
    return {
      theCapabilities: this.capabilities || [],
      isBusy: false,
      isAddingCapability: false,
      capabilityToAddOrRemoveId: null,
      capabilityToAddOrRemove: null,
    };
  },
  emits: ['onCapabilitiesChange'],
  created() {
    if (!Array.isArray(this.capabilities)) {
      this.isBusy = true;

      AjaxHelper.fetch<{ capabilities: Capability[] }>({
        method: 'UsersManager.getUsersPlusRole',
        limit: '1',
        filter_search: this.userLogin,
      }).then((user) => {
        if (!user || !user.capabilities) {
          return [];
        }

        return user.capabilities;
      }).then((capabilities) => {
        this.theCapabilities = capabilities;
      }).finally(function () {
        this.isBusy = false;
      });
    }
  },
  methods: {
    onToggleCapability(isAdd: boolean) {
      this.isAddingCapability = isAdd;

      this.capabilityToAddOrRemove = null;
      this.availableCapabilities.forEach((capability) => {
        if (capability.id === this.capabilityToAddOrRemoveId) {
          this.capabilityToAddOrRemove = capability;
        }
      });

      if (this.$refs.confirmCapabilityToggleModal) {
        $(this.$refs.confirmCapabilityToggleModal as HTMLElement).modal({
          dismissible: false,
          yes: function () {
          },
        }).modal('open');
      }
    },
    toggleCapability() {
      if (this.isAddingCapability) {
        this.addCapability(this.capabilityToAddOrRemove);
      } else {
        this.removeCapability(this.capabilityToAddOrRemove);
      }
    },
    isIncludedInRole(capability: Capability) {
      return (capability.includedInRoles || []).indexOf(this.userRole) !== -1;
    },
    getCapabilitiesList() {
      const result = [];
      this.availableCapabilities.forEach((capability) => {
        if (this.isIncludedInRole(capability)) {
          return;
        }

        if (this.capabilitiesSet[capability.id]) {
          result.push(capability.id);
        }
      });
      return result;
    },
    addCapability(capability: Capability) {
      this.isBusy = true;
      AjaxHelper.post(
        {
          method: 'UsersManager.addCapabilities',
        }, {
          userLogin: this.userLogin,
          capabilities: capability.id,
          idSites: this.idsite,
        },
      ).then(() => {
        // TODO: adapter
        this.$emit('capabilitiesChange', this.getCapabilitiesList());
        /*
        vm.onCapabilitiesChange.call({
          capabilities: getCapabilitiesList(),
        });
         */
      }).finally(() => {
        this.isBusy = false;
        this.capabilityToAddOrRemove = null;
        this.capabilityToAddOrRemoveId = null;
      });
    },
    removeCapability(capability: Capability) {
      this.isBusy = true;
      AjaxHelper.post(
        {
          method: 'UsersManager.removeCapabilities',
        },
        {
          userLogin: this.userLogin,
          capabilities: capability.id,
          idSites: this.idsite
        },
      ).then(() => {
        this.$emit('capabilitiesChange', this.getCapabilitiesList());
      }).finally(() => {
        this.isBusy = false;
        this.capabilityToAddOrRemove = null;
        this.capabilityToAddOrRemoveId = null;
      });
    },
  },
  computed: {
    availableCapabilities() {
      return CapabilitiesStore.capabilities.value;
    },
    confirmAddCapabilityToggleContent() {
      return translate(
        'UsersManager_AreYouSureAddCapability',
        `<strong>${this.userLogin}</strong>`,
        `<strong>${this.capabilityToAddOrRemove.name}</strong>`,
        `<strong>${this.siteName}</strong>`
      );
    },
    confirmCapabilityToggleContent() {
      return translate(
        'UsersManager_AreYouSureRemoveCapability',
        `<strong>${this.capabilityToAddOrRemove.name}</strong>`,
        `<strong>${this.userLogin}</strong>`,
        `<strong>${this.siteName}</strong>`
      );
    },
    availableCapabilitiesGrouped() {
      const availableCapabilitiesGrouped = this.availableCapabilities.filter(
        (c) => this.capabilitiesSet[c.id],
      ).map((c) => ({
        group: c.category,
        key: c.id,
        value: c.name,
        tooltip: c.description,
      }));

      availableCapabilitiesGrouped.sort((lhs, rhs) => {
        if (lhs.group === rhs.group) {
          if (lhs.value === rhs.value) {
            return 0;
          }
          return lhs.value < rhs.value ? -1 : 1;
        }
        return lhs.group < rhs.group ? -1 : 1;
      });

      return availableCapabilitiesGrouped;
    },
    capabilitiesSet() {
      const capabilitiesSet = {};
      const capabilities = this.theCapabilities as string[];

      (capabilities || []).forEach((capability) => {
        capabilitiesSet[capability] = true;
      });

      (this.availableCapabilities || []).forEach((capability) => {
        if (this.isIncludedInRole(capability)) {
          this.capabilitiesSet[capability.id] = true;
        }
      });

      return capabilitiesSet;
    },
  },
});
</script>
