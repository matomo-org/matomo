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
      v-for="capability in actualCapabilities"
      :key="capability.id"
      class="chip"
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
        @click="capabilityToRemoveId = capability.id; onToggleCapability(false)"
      />
    </div>
    <div
      class="addCapability"
      v-if="availableCapabilitiesGrouped.length"
    >
      <Field
        :model-value="capabilityToAddId"
        @update:model-value="capabilityToAddId = $event; onToggleCapability(true)"
        :disabled="isBusy"
        uicontrol="expandable-select"
        name="add_capability"
        :full-width="true"
        v-if="userRole !== 'noaccess'"
        :options="availableCapabilitiesGrouped"
      >
      </Field>
    </div>
    <div class="ui-confirm confirmCapabilityToggle modal" ref="confirmCapabilityToggleModal">
      <div class="modal-content">
        <h2
          v-if="isAddingCapability"
          v-html="$sanitize(confirmAddCapabilityToggleContent)"
        ></h2>
        <h2
          v-if="!isAddingCapability"
          v-html="$sanitize(confirmCapabilityToggleContent)"
        ></h2>
      </div>
      <div class="modal-footer">
        <a
          href=""
          class="modal-action modal-close btn"
          @click.prevent="toggleCapability()"
        >{{ translate('General_Yes') }}</a>
        <a
          href=""
          class="modal-action modal-close modal-no"
          @click.prevent="
            capabilityToAddOrRemove = null;
            capabilityToAddId = null;
            capabilityToRemoveId = null;"
        >
          {{ translate('General_No') }}
        </a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, DeepReadonly } from 'vue';
import { translate, AjaxHelper, Matomo } from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import CapabilitiesStore from '../CapabilitiesStore/CapabilitiesStore';
import Capability from '../CapabilitiesStore/Capability';
import ModalOptions = M.ModalOptions;

interface CapabilitiesEditState {
  isBusy: boolean;
  theCapabilities: string[];
  isAddingCapability: boolean;
  capabilityToAddId: string|null;
  capabilityToRemoveId: string|null;
  capabilityToAddOrRemove: DeepReadonly<Capability>|null;
}

const { $ } = window;

export default defineComponent({
  props: {
    idsite: [String, Number],
    siteName: {
      type: String,
      required: true,
    },
    userLogin: {
      type: String,
      required: true,
    },
    userRole: {
      type: String,
      required: true,
    },
    capabilities: Array,
  },
  components: {
    Field,
  },
  data(): CapabilitiesEditState {
    return {
      theCapabilities: (this.capabilities as string[]) || [],
      isBusy: false,
      isAddingCapability: false,
      capabilityToAddId: null,
      capabilityToRemoveId: null,
      capabilityToAddOrRemove: null,
    };
  },
  emits: ['change'],
  watch: {
    capabilities(newValue) {
      if (newValue) {
        this.theCapabilities = newValue as string[];
      }
    },
  },
  created() {
    CapabilitiesStore.init();

    if (!this.capabilities) {
      this.isBusy = true;

      AjaxHelper.fetch<{ capabilities: string[] }>({
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
      }).finally(() => {
        this.isBusy = false;
      });
    } else {
      this.theCapabilities = this.capabilities as string[];
    }
  },
  methods: {
    onToggleCapability(isAdd: boolean) {
      this.isAddingCapability = isAdd;

      const capabilityToAddOrRemoveId = isAdd ? this.capabilityToAddId : this.capabilityToRemoveId;

      this.capabilityToAddOrRemove = null;
      this.availableCapabilities.forEach((capability) => {
        if (capability.id === capabilityToAddOrRemoveId) {
          this.capabilityToAddOrRemove = capability;
        }
      });

      if (this.$refs.confirmCapabilityToggleModal) {
        $(this.$refs.confirmCapabilityToggleModal as HTMLElement).modal({
          dismissible: false,
          yes: () => null,
        } as unknown as ModalOptions).modal('open');
      }
    },
    toggleCapability() {
      if (this.isAddingCapability) {
        this.addCapability(this.capabilityToAddOrRemove!);
      } else {
        this.removeCapability(this.capabilityToAddOrRemove!);
      }
    },
    isIncludedInRole(capability: DeepReadonly<Capability>) {
      return (capability.includedInRoles || []).indexOf(this.userRole) !== -1;
    },
    getCapabilitiesList() {
      const result: string[] = [];
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
    addCapability(capability: DeepReadonly<Capability>) {
      this.isBusy = true;
      AjaxHelper.post(
        {
          method: 'UsersManager.addCapabilities',
        },
        {
          userLogin: this.userLogin,
          capabilities: capability.id,
          idSites: this.idsite,
        },
      ).then(() => {
        this.$emit('change', this.getCapabilitiesList());
      }).finally(() => {
        this.isBusy = false;
        this.capabilityToAddOrRemove = null;
        this.capabilityToAddId = null;
        this.capabilityToRemoveId = null;
      });
    },
    removeCapability(capability: DeepReadonly<Capability>) {
      this.isBusy = true;
      AjaxHelper.post(
        {
          method: 'UsersManager.removeCapabilities',
        },
        {
          userLogin: this.userLogin,
          capabilities: capability.id,
          idSites: this.idsite,
        },
      ).then(() => {
        this.$emit('change', this.getCapabilitiesList());
      }).finally(() => {
        this.isBusy = false;
        this.capabilityToAddOrRemove = null;
        this.capabilityToAddId = null;
        this.capabilityToRemoveId = null;
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
        `<strong>${this.capabilityToAddOrRemove ? this.capabilityToAddOrRemove.name : ''}</strong>`,
        `<strong>${this.siteNameText}</strong>`,
      );
    },
    confirmCapabilityToggleContent() {
      return translate(
        'UsersManager_AreYouSureRemoveCapability',
        `<strong>${this.capabilityToAddOrRemove ? this.capabilityToAddOrRemove.name : ''}</strong>`,
        `<strong>${this.userLogin}</strong>`,
        `<strong>${this.siteNameText}</strong>`,
      );
    },
    siteNameText() {
      return Matomo.helper.htmlEntities(this.siteName);
    },
    availableCapabilitiesGrouped() {
      const availableCapabilitiesGrouped = this.availableCapabilities.filter(
        (c) => !this.capabilitiesSet[c.id],
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
      const capabilitiesSet: Record<string, boolean> = {};
      const capabilities = this.theCapabilities as string[];

      (capabilities || []).forEach((capability) => {
        capabilitiesSet[capability] = true;
      });

      (this.availableCapabilities || []).forEach((capability) => {
        if (this.isIncludedInRole(capability)) {
          capabilitiesSet[capability.id] = true;
        }
      });

      return capabilitiesSet;
    },
    actualCapabilities() {
      const { capabilitiesSet } = this;
      return this.availableCapabilities.filter((c) => !!capabilitiesSet[c.id]);
    },
  },
});
</script>
