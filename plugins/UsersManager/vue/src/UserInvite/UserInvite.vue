<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    class="userInviteForm"
    :content-title="translate('UsersManager_InviteNewUser')"
  >
    <div
      class="row"
      v-form=""
    >
      <div class="col s12 m6 invite-notes">
        <div class="form-help">
           <span v-html="$sanitize(
                translate('UsersManager_InviteSuccessNotification',
                [inviteTokenExpiryDays]))">
           </span>
        </div>
      </div>
      <div class="col m10">
        <div>
          <Field
            v-model="theUser.login"
            :disabled="isInvitingUser"
            autocomplete="off"
            uicontrol="text"
            name="user_login"
            :maxlength="100"
            :title="translate('General_Username')"
          />
        </div>
        <div class="email-input">
          <Field
            v-model="theUser.email"
            :disabled="isInvitingUser"
            uicontrol="text"
            name="user_email"
            autocomplete="off"
            :maxlength="100"
            :title="translate('UsersManager_Email')"
          />
        </div>
        <div>
          <Field
            v-model="firstSiteAccess"
            :disabled="isInvitingUser"
            uicontrol="site"
            name="user_site"
            :ui-control-attributes="{ onlySitesWithAdminAccess: true }"
            :title="translate('UsersManager_FirstWebsitePermission')"
            :inline-help="translate('UsersManager_FirstSiteInlineHelp')"
          />
        </div>
        <div>
          <div class="form-group row" style="position: relative">
            <div class="col s12 m6 save-button">
              <SaveButton
                :value="translate('UsersManager_InviteUser')"
                :disabled="!firstSiteAccess || !firstSiteAccess.id
                            || !theUser.login || !theUser.email"
                :saving="isInvitingUser"
                @confirm="showPasswordConfirmation = true"
              />
            </div>
          </div>
          <PasswordConfirmation
            v-model="showPasswordConfirmation"
            @confirmed="inviteUser"
          />
        </div>
        <div
          class="entityCancel"
        >
          <a
            href=""
            class="entityCancelLink"
            @click.prevent="abort()"
          >
            <span class="icon icon-arrow-left">&nbsp;
            </span>{{ translate('UsersManager_BackToUser') }}</a>
        </div>
      </div>
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  SiteRef,
  translate,
  AjaxHelper,
  NotificationsStore,
  AutoClearPassword,
} from 'CoreHome';
import {
  PasswordConfirmation,
  Form,
  Field,
  SaveButton,
} from 'CorePluginsAdmin';
import User from '../User';

const DEFAULT_USER: User = {
  login: '',
  superuser_access: false,
  uses_2fa: false,
  password: '',
  email: '',
  invite_status: '',
};

interface UserInviteState {
  theUser: User;
  isInvitingUser: boolean;
  firstSiteAccess: SiteRef | null;
  showPasswordConfirmation: boolean;
}

export default defineComponent({
  props: {
    initialSiteId: {
      type: [String, Number],
      required: true,
    },
    initialSiteName: {
      type: String,
      required: true,
    },
    inviteTokenExpiryDays: {
      type: String,
      required: true,
    },
  },
  components: {
    ContentBlock,
    Field,
    SaveButton,
    PasswordConfirmation,
  },
  directives: {
    Form,
    AutoClearPassword,
  },
  data(): UserInviteState {
    return {
      theUser: { ...DEFAULT_USER },
      isInvitingUser: false,
      firstSiteAccess: {
        id: this.initialSiteId,
        name: this.initialSiteName,
      },
      showPasswordConfirmation: false,
    };
  },
  emits: ['aborted', 'invited'],
  methods: {
    inviteUser(password: string) {
      this.isInvitingUser = true;
      return AjaxHelper.post(
        {
          method: 'UsersManager.inviteUser',
        },
        {
          userLogin: this.theUser.login,
          email: this.theUser.email,
          initialIdSite: this.firstSiteAccess ? this.firstSiteAccess.id : undefined,
          passwordConfirmation: password,
        },
      ).then(() => {
        this.firstSiteAccess = {
          id: this.initialSiteId,
          name: this.initialSiteName,
        };
        this.theUser.invite_status = 'pending';

        this.showUserInvitedNotification();
        this.$emit('invited', { user: this.theUser });
        this.theUser = DEFAULT_USER;
      }).finally(() => {
        this.isInvitingUser = false;
      });
    },
    showUserInvitedNotification() {
      NotificationsStore.show({
        message: translate('UsersManager_InviteSuccess'),
        context: 'success',
        type: 'toast',
      });
    },
    abort() {
      this.theUser = DEFAULT_USER;
      this.firstSiteAccess = null;
      this.$emit('aborted');
    },
  },
});
</script>
