<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Piwik\Config;
use Piwik\Filesystem;
use Piwik\Option;
use Piwik\Plugin\Manager;
use Piwik\SettingsPiwik;

class CustomLogo
{
    const LOGO_HEIGHT = 300;
    const LOGO_SMALL_HEIGHT = 100;
    const FAVICON_HEIGHT = 32;

    public function getLogoUrl($pathOnly = false)
    {
        $defaultLogo = 'plugins/Morpheus/images/logo.png';
        $themeLogo = 'plugins/%s/images/logo.png';
        $userLogo = CustomLogo::getPathUserLogo();
        return $this->getPathToLogo($pathOnly, $defaultLogo, $themeLogo, $userLogo);
    }

    public function getHeaderLogoUrl($pathOnly = false)
    {
        $defaultLogo = 'plugins/Morpheus/images/logo-header.png';
        $themeLogo = 'plugins/%s/images/logo-header.png';
        $customLogo = CustomLogo::getPathUserLogoSmall();
        return $this->getPathToLogo($pathOnly, $defaultLogo, $themeLogo, $customLogo);
    }

    public function getSVGLogoUrl($pathOnly = false)
    {
        $defaultLogo = 'plugins/Morpheus/images/logo.svg';
        $themeLogo = 'plugins/%s/images/logo.svg';
        $customLogo = CustomLogo::getPathUserSvgLogo();
        $svg = $this->getPathToLogo($pathOnly, $defaultLogo, $themeLogo, $customLogo);
        return $svg;
    }

    public function isEnabled()
    {
        return (bool) Option::get('branding_use_custom_logo');
    }

    public function enable()
    {
        Option::set('branding_use_custom_logo', '1', true);
    }

    public function disable()
    {
        Option::set('branding_use_custom_logo', '0', true);
    }

    public function hasSVGLogo()
    {
        if (!$this->isEnabled()) {
            /* We always have our application logo */
            return true;
        }

        if ($this->isEnabled()
            && file_exists(Filesystem::getPathToPiwikRoot() . '/' . CustomLogo::getPathUserSvgLogo())
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isFileUploadEnabled()
    {
        return ini_get('file_uploads') == 1;
    }

    /**
     * @return bool
     */
    public function isCustomLogoWritable()
    {
        if (Config::getInstance()->General['enable_custom_logo_check'] == 0) {
            return true;
        }
        $pathUserLogo = $this->getPathUserLogo();

        $directoryWritingTo = PIWIK_DOCUMENT_ROOT . '/' . dirname($pathUserLogo);

        // Create directory if not already created
        Filesystem::mkdir($directoryWritingTo);

        $directoryWritable = is_writable($directoryWritingTo);
        $logoFilesWriteable = is_writeable(PIWIK_DOCUMENT_ROOT . '/' . $pathUserLogo)
            && is_writeable(PIWIK_DOCUMENT_ROOT . '/' . $this->getPathUserSvgLogo())
            && is_writeable(PIWIK_DOCUMENT_ROOT . '/' . $this->getPathUserLogoSmall());;

        $isCustomLogoWritable = ($logoFilesWriteable || $directoryWritable) && $this->isFileUploadEnabled();

        return $isCustomLogoWritable;
    }

    protected function getPathToLogo($pathOnly, $defaultLogo, $themeLogo, $customLogo)
    {
        $pathToPiwikRoot = Filesystem::getPathToPiwikRoot();

        $logo = $defaultLogo;

        $theme = \Piwik\Plugin\Manager::getInstance()->getThemeEnabled();
        if(!$theme) {
            $themeName = Manager::DEFAULT_THEME;
        } else {
            $themeName = $theme->getPluginName();
        }
        $themeLogo = sprintf($themeLogo, $themeName);

        if (file_exists($pathToPiwikRoot . '/' . $themeLogo)) {
            $logo = $themeLogo;
        }
        if ($this->isEnabled()
            && file_exists($pathToPiwikRoot . '/' . $customLogo)
        ) {
            $logo = $customLogo;
        }

        if (!$pathOnly) {
            return SettingsPiwik::getPiwikUrl() . $logo;
        }
        return $pathToPiwikRoot . '/' . $logo;
    }

    public static function getPathUserLogo()
    {
        return self::rewritePath('misc/user/logo.png');
    }

    public static function getPathUserFavicon()
    {
        return self::rewritePath('misc/user/favicon.png');
    }

    public static function getPathUserSvgLogo()
    {
        return self::rewritePath('misc/user/logo.svg');
    }

    public static function getPathUserLogoSmall()
    {
        return self::rewritePath('misc/user/logo-header.png');
    }

    protected static function rewritePath($path)
    {
        return SettingsPiwik::rewriteMiscUserPathWithInstanceId($path);
    }

    public function copyUploadedLogoToFilesystem()
    {
        $uploadFieldName = 'customLogo';

        $success = $this->uploadImage($uploadFieldName, self::LOGO_SMALL_HEIGHT, $this->getPathUserLogoSmall());
        $success = $success && $this->uploadImage($uploadFieldName, self::LOGO_HEIGHT, $this->getPathUserLogo());

        return $success;
    }

    public function copyUploadedFaviconToFilesystem()
    {
        $uploadFieldName = 'customFavicon';

        return $this->uploadImage($uploadFieldName, self::FAVICON_HEIGHT, $this->getPathUserFavicon());
    }

    private function uploadImage($uploadFieldName, $targetHeight, $userPath)
    {
        if (empty($_FILES[$uploadFieldName])
            || !empty($_FILES[$uploadFieldName]['error'])
        ) {
            return false;
        }

        $file = $_FILES[$uploadFieldName]['tmp_name'];
        if (!file_exists($file)) {
            return false;
        }

        list($width, $height) = getimagesize($file);
        switch ($_FILES[$uploadFieldName]['type']) {
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($file);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($file);
                break;
            case 'image/gif':
                $image = @imagecreatefromgif ($file);
                break;
            default:
                return false;
        }

        if (!is_resource($image)) {
            return false;
        }

        $targetWidth = round($width * $targetHeight / $height);

        $newImage = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($_FILES[$uploadFieldName]['type'] == 'image/png') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        $backgroundColor = imagecolorallocate($newImage, 0, 0, 0);
        imagecolortransparent($newImage, $backgroundColor);

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagepng($newImage, PIWIK_DOCUMENT_ROOT . '/' . $userPath, 3);
        return true;
    }

}
