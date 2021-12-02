/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import { Matomo } from 'CoreHome';

const { $ } = window;

interface PluginManagementState {
  uninstallConfirmMessage?: string;
}

function onClickUninstall(binding: DirectiveBinding<PluginManagementState>, event: MouseEvent) {
  event.preventDefault();

  const link = $(event.target).attr('href');
  const pluginName = $(event.target).attr('data-plugin-name');

  if (!link || !pluginName) {
    return;
  }

  if (!binding.value.uninstallConfirmMessage) {
    binding.value.uninstallConfirmMessage = $('#uninstallPluginConfirm').text();
  }

  const messageToDisplay = binding.value.uninstallConfirmMessage.replace(
    '%s', pluginName);

  $('#uninstallPluginConfirm').text(messageToDisplay);

  Matomo.helper.modalConfirm('#confirmUninstallPlugin', {
    yes: () => {
      window.location = link;
    },
  });
}

function onDonateLinkClick(event: MouseEvent) {
  event.preventDefault();

  const overlayId = $(event.target).data('overlay-id');
  Matomo.helper.modalConfirm('#'+overlayId, {});
}

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<PluginManagementState>): void {
    setTimeout(() => {
      binding.value.uninstallConfirmMessage = '';

      $(el).find('.uninstall').click(onClickUninstall.bind(null, binding));
      $(el).find('.plugin-donation-link').click(onDonateLinkClick);
    });
  },
}
