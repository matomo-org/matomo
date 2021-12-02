/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Matomo, translate } from 'CoreHome';

const { $ } = window;

function onUploadPlugin(event: MouseEvent) {
  event.preventDefault();
  Matomo.helper.modalConfirm('#installPluginByUpload', {});
}

function onSubmitPlugin(event: MouseEvent) {
  const $zipFile = $('[name=pluginZip]');
  const fileName = $zipFile.val();

  if (!fileName || fileName.slice(-4) !== '.zip') {
    event.preventDefault();
    alert(translate('CorePluginsAdmin_NoZipFileSelected'));
  } else if ($zipFile.data('maxSize') > 0
    && $zipFile[0].files[0].size > $zipFile.data('maxSize') * 1048576
  ) {
    event.preventDefault();
    alert(translate('CorePluginsAdmin_FileExceedsUploadLimit'));
  }
}

export default {
  mounted(): void {
    setTimeout(() => {
      $('.uploadPlugin').click(onUploadPlugin);
      $('#uploadPluginForm').submit(onSubmitPlugin);
    });
  },
}
