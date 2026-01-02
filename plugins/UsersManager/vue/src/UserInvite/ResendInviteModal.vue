<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <PasswordConfirmation
    v-model="showPasswordConfirmationForInviteAction"
    @confirmed="onInviteAction"
  />
  <div class="resend-invite-confirm-modal modal" ref="resendInviteConfirmModal">
    <div class="btn-close modal-close"><i class="icon-close"></i></div>
    <div class="modal-content">
      <h2 class="modal-title">{{ translate('UsersManager_ResendInvite') }}</h2>
      <p
        v-html="$sanitize(translate(
            'UsersManager_InviteConfirmMessage',
            [`<strong>${user?.login}</strong>`,
             `<strong>${user?.email}</strong>`]
            ,
          ))"
      ></p>
      <p><strong>
        {{ translate('UsersManager_InviteActionNotes', inviteTokenExpiryDays) }}
      </strong></p>
    </div>
    <div class="modal-footer">
        <span v-if="copied" class="success-copied">
          <i class="icon-success"></i>
          {{ translate('UsersManager_LinkCopied') }}</span>
      <button
        @click="showInviteActionPasswordConfirm('copy')"
        class="btn btn-copy-link modal-action"
        style="margin-right:3.5px"
      >{{ translate('UsersManager_CopyLink') }}</button>
      <button
        class="btn btn-resend modal-action modal-no"
        @click = "showInviteActionPasswordConfirm('send')"
      >{{ translate('UsersManager_ResendInvite') }}</button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  AjaxHelper,
  NotificationsStore,
} from 'CoreHome';
import {
  PasswordConfirmation,
} from 'CorePluginsAdmin';

interface UserInviteState {
  copied: boolean;
  showPasswordConfirmationForInviteAction: boolean;
  inviteAction: string;
  loading: boolean;
}

export default defineComponent({
  props: {
    user: {
      type: Object,
      required: false,
    },
    inviteTokenExpiryDays: {
      type: String,
      required: true,
    },
  },
  components: {
    PasswordConfirmation,
  },
  data(): UserInviteState {
    return {
      copied: false,
      showPasswordConfirmationForInviteAction: false,
      inviteAction: '',
      loading: false,
    };
  },
  emits: ['close'],
  watch: {
    user(newUser) {
      if (!newUser) {
        return;
      }
      $(this.$refs.resendInviteConfirmModal as HTMLElement)
        .modal({
          dismissible: false,
          onCloseEnd: () => this.$emit('close'),
        })
        .modal('open');
      this.copied = false;
    },
  },
  methods: {
    showInviteActionPasswordConfirm(action: string) {
      if (this.loading) {
        return;
      }
      this.showPasswordConfirmationForInviteAction = true;
      this.inviteAction = action;
    },
    onInviteAction(password: string) {
      if (this.inviteAction === 'send') {
        this.onResendInvite(password);
      } else {
        this.generateInviteLink(password);
      }
    },
    onResendInvite(password: string) {
      if (password === '') return;
      AjaxHelper.post<AjaxHelper>(
        {
          method: 'UsersManager.resendInvite',
          userLogin: this.user!.login,
        },
        {
          passwordConfirmation: password,
        },
      ).then(() => {
        $(this.$refs.resendInviteConfirmModal as HTMLElement).modal('close');
        const id = NotificationsStore.show({
          message: translate('UsersManager_InviteSuccess'),
          id: 'resendInvite',
          context: 'success',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(id);
      });
    },
    async generateInviteLink(password: string) {
      if (this.loading) {
        return;
      }
      this.loading = true;
      try {
        const res = await AjaxHelper.post<{ value: string }>(
          {
            method: 'UsersManager.generateInviteLink',
          }, {
            userLogin: this.user!.login,
            passwordConfirmation: password,
          },
        );

        await this.copyToClipboard(res.value);
        // eslint-disable-next-line no-empty
      } catch (e) {

      }
      this.loading = false;
    },
    async copyToClipboard(value: string) {
      try {
        const tempInput = document.createElement('input');
        tempInput.style.top = '-100px';
        tempInput.style.left = '0';
        tempInput.style.position = 'fixed';
        tempInput.value = value;
        document.body.appendChild(tempInput);
        tempInput.select();
        if (window.location.protocol !== 'https:') {
          document.execCommand('copy');
        } else {
          await navigator.clipboard.writeText(tempInput.value);
        }
        document.body.removeChild(tempInput);
        this.copied = true;
        // eslint-disable-next-line no-empty
      } catch (e) {
        const id = NotificationsStore.show({
          message: `<strong>${translate('UsersManager_CopyDenied')}</strong><br>
${translate('UsersManager_CopyDeniedHints', [`<br><span class="invite-link">${value}</span>`])}`,
          id: 'copyError',
          context: 'error',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(id);
      }
    },
  },
});
</script>
