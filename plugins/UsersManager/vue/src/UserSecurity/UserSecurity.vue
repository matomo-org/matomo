<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <ContentBlock
      v-if="isUsersAdminEnabled"
      :content-title="translate('General_ChangePassword')"
      feature="true"
    >
      <form
        id="userSettingsTable"
        method="post"
        :action="recordPasswordChangeAction"
      >
        <input type="hidden" :value="changePasswordNonce" name="nonce"/>

        <div v-if="isValidHost">
          <Field
            uicontrol="password"
            name="password"
            :autocomplete="false"
            v-model="password"
            :title="translate('Login_NewPassword')"
            :inline-help="translate('UsersManager_IfYouWouldLikeToChangeThePasswordTypeANewOne')"
          />

          <Field
            uicontrol="password"
            name="passwordBis"
            :autocomplete="false"
            v-model="passwordBis"
            :title="translate('Login_NewPasswordRepeat')"
            :inline-help="translate('UsersManager_TypeYourPasswordAgain')"
          />

          <Field
            uicontrol="password"
            name="passwordConfirmation"
            :autocomplete="false"
            v-model="passwordConfirmation"
            :title="translate('UsersManager_YourCurrentPassword')"
            :inline-help="translate('UsersManager_TypeYourCurrentPassword')"
          />

          <div class="alert alert-info">
            {{ translate('UsersManager_PasswordChangeTerminatesOtherSessions') }}
          </div>

          <input
            type="submit"
            :value="translate('General_Save')"
            class="btn"
          />
        </div>

        <div v-if="!isValidHost">
          <div class="alert alert-danger">
            {{ translate('UsersManager_InjectedHostCannotChangePwd', invalidHost) }}
            <span v-if="!isSuperUser" v-html="$sanitize(emailYourAdminText)"></span>
          </div>
        </div>
      </form>
    </ContentBlock>

    <div ref="afterPassword">
      <component
        v-if="isUsersAdminEnabled && afterPasswordComponent"
        :is="afterPasswordComponent"
      />
    </div>

    <a name="authtokens" id="authtokens"></a>
    <ContentBlock :content-title="translate('UsersManager_AuthTokens')">
      <p>
        {{ translate('UsersManager_TokenAuthIntro') }}
        <span v-if="hasTokensWithExpireDate">
          {{ translate('UsersManager_ExpiredTokensDeleteAutomatically') }}
        </span>
      </p>
      <table v-content-table class="listAuthTokens">
        <thead>
        <tr>
          <th>{{ translate('General_CreationDate') }}</th>
          <th>{{ translate('General_Description') }}</th>
          <th>{{ translate('UsersManager_LastUsed') }}</th>
          <th>{{ translate('UsersManager_SecureUseOnly') }}</th>
          <th
            v-if="hasTokensWithExpireDate"
            :title="translate('UsersManager_TokensWithExpireDateCreationBySystem')"
          >
            {{ translate('UsersManager_ExpireDate') }}
          </th>
          <th>{{ translate('General_Actions') }}</th>
        </tr>
        </thead>
        <tbody>
        <tr v-if="!tokens?.length">
          <td
            :colspan="hasTokensWithExpireDate ? 5 : 4"
            v-html="$sanitize(noTokenCreatedYetText)"
          ></td>
        </tr>
        <tr v-for="theToken in (tokens || [])" :key="theToken.idusertokenauth">
          <td><span class="creationDate">{{ theToken.date_created }}</span></td>
          <td>{{ theToken.description }}</td>
          <td>
            {{ theToken.last_used ? theToken.last_used : translate('General_Never') }}
          </td>
          <td>
            {{ parseInt(theToken.secure_only, 10) === 1 ?
               translate('General_Yes') : translate('General_No') }}
          </td>
          <td
            v-if="hasTokensWithExpireDate"
            :title="translate('UsersManager_TokensWithExpireDateCreationBySystem')"
          >
            {{ theToken.date_expired ? theToken.date_expired : translate('General_Never') }}
          </td>
          <td>
            <form
              method="post"
              :action="deleteTokenAction"
              style="display: inline"
            >
              <input name="nonce" type="hidden" :value="deleteTokenNonce"/>
              <input name="idtokenauth" type="hidden" :value="theToken.idusertokenauth"/>
              <button
                type="submit"
                class="table-action"
                :title="translate('General_Delete')"
              >
                <span class="icon-delete"></span>
              </button>
            </form>
          </td>
        </tr>
        </tbody>
      </table>

      <div class="tableActionBar">
        <a :href="addNewTokenLink" class="addNewToken">
          <span class="icon-add"></span>
          {{ translate('UsersManager_CreateNewToken') }}
        </a>

        <form
          v-if="tokens?.length"
          method="post"
          :action="deleteTokenAction"
          style="display: inline"
        >
          <input name="nonce" type="hidden" :value="deleteTokenNonce">
          <input name="idtokenauth" type="hidden" value="all">
          <button type="submit" class="table-action">
            <span class="icon-delete"></span> {{ translate('UsersManager_DeleteAllTokens') }}
          </button>
        </form>
      </div>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent, markRaw } from 'vue';
import {
  ContentBlock,
  ContentTable, Matomo,
  MatomoUrl,
  translate,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

interface UserSecurityState {
  password: string;
  passwordBis: string;
  passwordConfirmation: string;
}

export default defineComponent({
  props: {
    deleteTokenNonce: String,
    tokens: Array,
    hasTokensWithExpireDate: Boolean,
    isUsersAdminEnabled: Boolean,
    changePasswordNonce: String,
    isValidHost: Boolean,
    isSuperUser: Boolean,
    invalidHost: String,
    afterPasswordEventContent: String,
    invalidHostMailLinkStart: String,
  },
  components: {
    ContentBlock,
    Field,
  },
  directives: {
    ContentTable,
  },
  data(): UserSecurityState {
    return {
      password: '',
      passwordBis: '',
      passwordConfirmation: '',
    };
  },
  mounted() {
    const afterPassword = this.$refs.afterPassword as HTMLElement;
    Matomo.helper.compileVueEntryComponents(afterPassword);
  },
  computed: {
    recordPasswordChangeAction() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'recordPasswordChange',
      })}`;
    },
    emailYourAdminText() {
      return translate(
        'UsersManager_EmailYourAdministrator',
        this.invalidHostMailLinkStart || '',
        '</a>',
      );
    },
    noTokenCreatedYetText() {
      const addNewTokenLink = `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'addNewToken',
      })}`;

      return translate(
        'UsersManager_NoTokenCreatedYetCreateNow',
        `<a href="${addNewTokenLink}">`,
        '</a>',
      );
    },
    deleteTokenAction() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'deleteToken',
      })}`;
    },
    addNewTokenLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'addNewToken',
      })}`;
    },
    afterPasswordComponent() {
      if (!this.afterPasswordEventContent) {
        return null;
      }

      const afterPassword = this.$refs.afterPassword as HTMLElement;
      return markRaw({
        template: this.afterPasswordEventContent,
        beforeUnmount() {
          Matomo.helper.destroyVueComponent(afterPassword);
        },
      });
    },
  },
});
</script>
