<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use DI\NotFoundException;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Filesystem;
use Piwik\Option;
use Piwik\Piwik;
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
        $userLogo = static::getPathUserLogo();
        return $this->getPathToLogo($pathOnly, $defaultLogo, $themeLogo, $userLogo);
    }

    public function getHeaderLogoUrl($pathOnly = false)
    {
        $defaultLogo = 'plugins/Morpheus/images/logo.svg';
        $themeLogo = 'plugins/%s/images/logo-header.png';
        $customLogo = static::getPathUserLogoSmall();
        return $this->getPathToLogo($pathOnly, $defaultLogo, $themeLogo, $customLogo);
    }

    public function getSVGLogoUrl($pathOnly = false)
    {
        $defaultLogo = 'plugins/Morpheus/images/logo.svg';
        $themeLogo = 'plugins/%s/images/logo.svg';
        $customLogo = static::getPathUserSvgLogo();
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

        if ($this->isEnabled() && static::logoExists(static::getPathUserSvgLogo())) {
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

    public function isCustomLogoFeatureEnabled()
    {
        return Config::getInstance()->General['enable_custom_logo'] != 0;
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
            && is_writeable(PIWIK_DOCUMENT_ROOT . '/' . $this->getPathUserLogoSmall());

        $isCustomLogoWritable = ($logoFilesWriteable || $directoryWritable) && $this->isFileUploadEnabled();

        return $isCustomLogoWritable;
    }

    protected function getPathToLogo($pathOnly, $defaultLogo, $themeLogo, $customLogo)
    {
        $logo = $defaultLogo;

        $theme = \Piwik\Plugin\Manager::getInstance()->getThemeEnabled();
        if(!$theme) {
            $themeName = Manager::DEFAULT_THEME;
        } else {
            $themeName = $theme->getPluginName();
        }
        $themeLogo = sprintf($themeLogo, $themeName);

        if (static::logoExists($themeLogo)) {
            $logo = $themeLogo;
        }
        if ($this->isEnabled() && static::logoExists($customLogo)) {
            $logo = $customLogo;
        }

        if (!$pathOnly) {
            return SettingsPiwik::getPiwikUrl() . $logo;
        }

        return Filesystem::getPathToPiwikRoot() . '/' . $logo;
    }

    private static function getBasePath()
    {
        try {
            $basePath = StaticContainer::get('path.misc.user');
            return $basePath;
        } catch (NotFoundException $e) {
            // happens when upgrading from an older version which didn't have that global config entry yet
            // to a newer version of Matomo when this value is being requested while the update happens
            // basically request starts... the old global.php is loaded, then we update all PHP files, then after the
            // update within the same request a newer version of CustomLogo.php is loaded and they are not compatible.
            // In this case we return the default value
            return 'misc/user/';
        }
    }

    public static function getPathUserLogo()
    {
        return static::rewritePath(self::getBasePath() . 'logo.png');
    }

    public static function getPathUserFavicon()
    {
        return static::rewritePath(self::getBasePath() . 'favicon.png');
    }

    public static function getPathUserSvgLogo()
    {
        return static::rewritePath(self::getBasePath() . 'logo.svg');
    }

    public static function getPathUserLogoSmall()
    {
        return static::rewritePath(self::getBasePath() . 'logo-header.png');
    }

    protected static function rewritePath($path)
    {
        return SettingsPiwik::rewriteMiscUserPathWithInstanceId($path);
    }

    /**
     * @return bool
     */
    public static function hasUserLogo()
    {
        return static::logoExists(static::getPathUserLogo());
    }

    /**
     * @return bool
     */
    public static function hasUserFavicon()
    {
        return static::logoExists(static::getPathUserFavicon());
    }

    public function copyUploadedLogoToFilesystem()
    {
        $uploadFieldName = 'customLogo';

        $smallLogoUserPath = $this->getPathUserLogoSmall();
        $logoUserPath = $this->getPathUserLogo();

        $success = $this->uploadImage($uploadFieldName, self::LOGO_SMALL_HEIGHT, $smallLogoUserPath);
        if (!$success) {
            return false;
        }

        $this->postLogoChangeEvent($smallLogoUserPath);

        $success = $this->uploadImage($uploadFieldName, self::LOGO_HEIGHT, $logoUserPath);
        if (!$success) {
            return false;
        }

        $this->postLogoChangeEvent($logoUserPath);

        return true;
    }

    private function postLogoChangeEvent($imagePath)
    {
        $rootPath = Filesystem::getPathToPiwikRoot();
        $absolutePath = $rootPath . '/' . $imagePath;

        /**
         * Triggered when a user uploads a custom logo. This event is triggered for
         * the large logo, for the smaller logo-header.png file, and for the favicon.
         *
         * @param string $absolutePath The absolute path to the logo file on the Piwik server.
         */
        Piwik::postEvent('CoreAdminHome.customLogoChanged', [$absolutePath]);
    }

    public function copyUploadedFaviconToFilesystem()
    {
        $uploadFieldName = 'customFavicon';

        $faviconUserPath = $this->getPathUserFavicon();

        $success = $this->uploadImage($uploadFieldName, self::FAVICON_HEIGHT, $faviconUserPath);
        if (!$success) {
            return false;
        }

        $this->postLogoChangeEvent($faviconUserPath);

        return true;
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

        if (!is_resource($image) && !($image instanceof \GdImage)) {
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

    /**
     * @return bool
     */
    private static function logoExists($relativePath)
    {
        return file_exists(Filesystem::getPathToPiwikRoot() . '/' . $relativePath);
    }

}
