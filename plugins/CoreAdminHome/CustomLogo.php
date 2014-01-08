<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_API
 */
namespace Piwik\Plugins\CoreAdminHome;

use Piwik\Config;
use Piwik\Filesystem;
use Piwik\SettingsPiwik;

class CustomLogo
{
    const LOGO_HEIGHT = 300;
    const LOGO_SMALL_HEIGHT = 100;

    public function getLogoUrl($pathOnly = false)
    {
        $defaultLogo = 'plugins/Zeitgeist/images/logo.png';
        $themeLogo = 'plugins/%s/images/logo.png';
        $userLogo = CustomLogo::getPathUserLogo();
        return $this->getPathToLogo($pathOnly, $defaultLogo, $themeLogo, $userLogo);
    }

    public function getHeaderLogoUrl($pathOnly = false)
    {
        $defaultLogo = 'plugins/Zeitgeist/images/logo-header.png';
        $themeLogo = 'plugins/%s/images/logo-header.png';
        $customLogo = CustomLogo::getPathUserLogoSmall();
        return $this->getPathToLogo($pathOnly, $defaultLogo, $themeLogo, $customLogo);
    }

    public function getSVGLogoUrl($pathOnly = false)
    {
        $defaultLogo = 'plugins/Zeitgeist/images/logo.svg';
        $themeLogo = 'plugins/%s/images/logo.svg';
        $customLogo = CustomLogo::getPathUserSvgLogo();
        $svg = $this->getPathToLogo($pathOnly, $defaultLogo, $themeLogo, $customLogo);
        return $svg;
    }

    public function hasSVGLogo()
    {
        if (Config::getInstance()->branding['use_custom_logo'] == 0) {
            /* We always have our application logo */
            return true;
        }

        if (Config::getInstance()->branding['use_custom_logo'] == 1
            && file_exists(Filesystem::getPathToPiwikRoot() . '/' . CustomLogo::getPathUserSvgLogo())
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isCustomLogoWritable()
    {
        $pathUserLogo = $this->getPathUserLogo();

        $directoryWritingTo = PIWIK_DOCUMENT_ROOT . '/' . dirname($pathUserLogo);

        // Create directory if not already created
        Filesystem::mkdir($directoryWritingTo, $denyAccess = false);

        $directoryWritable = is_writable($directoryWritingTo);
        $logoFilesWriteable = is_writeable(PIWIK_DOCUMENT_ROOT . '/' . $pathUserLogo)
            && is_writeable(PIWIK_DOCUMENT_ROOT . '/' . $this->getPathUserSvgLogo())
            && is_writeable(PIWIK_DOCUMENT_ROOT . '/' . $this->getPathUserLogoSmall());;

        $serverUploadEnabled = ini_get('file_uploads') == 1;
        $isCustomLogoWritable = ($logoFilesWriteable || $directoryWritable) && $serverUploadEnabled;

        return $isCustomLogoWritable;
    }

    protected function getPathToLogo($pathOnly, $defaultLogo, $themeLogo, $customLogo)
    {
        $pathToPiwikRoot = Filesystem::getPathToPiwikRoot();

        $logo = $defaultLogo;

        $themeName = \Piwik\Plugin\Manager::getInstance()->getThemeEnabled()->getPluginName();
        $themeLogo = sprintf($themeLogo, $themeName);

        if (file_exists($pathToPiwikRoot . '/' . $themeLogo)) {
            $logo = $themeLogo;
        }
        if (Config::getInstance()->branding['use_custom_logo'] == 1
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
        return SettingsPiwik::rewriteMiscUserPathWithHostname($path);
    }

    public function copyUploadedLogoToFilesystem()
    {

        if (empty($_FILES['customLogo'])
            || !empty($_FILES['customLogo']['error'])
        ) {
            return false;
        }

        $file = $_FILES['customLogo']['tmp_name'];
        if (!file_exists($file)) {
            return false;
        }

        list($width, $height) = getimagesize($file);
        switch ($_FILES['customLogo']['type']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file);
                break;
            default:
                return false;
        }

        $widthExpected = round($width * self::LOGO_HEIGHT / $height);
        $smallWidthExpected = round($width * self::LOGO_SMALL_HEIGHT / $height);

        $logo = imagecreatetruecolor($widthExpected, self::LOGO_HEIGHT);
        $logoSmall = imagecreatetruecolor($smallWidthExpected, self::LOGO_SMALL_HEIGHT);

        // Handle transparency
        $background = imagecolorallocate($logo, 0, 0, 0);
        $backgroundSmall = imagecolorallocate($logoSmall, 0, 0, 0);
        imagecolortransparent($logo, $background);
        imagecolortransparent($logoSmall, $backgroundSmall);

        if ($_FILES['customLogo']['type'] == 'image/png') {
            imagealphablending($logo, false);
            imagealphablending($logoSmall, false);
            imagesavealpha($logo, true);
            imagesavealpha($logoSmall, true);
        }

        imagecopyresized($logo, $image, 0, 0, 0, 0, $widthExpected, self::LOGO_HEIGHT, $width, $height);
        imagecopyresized($logoSmall, $image, 0, 0, 0, 0, $smallWidthExpected, self::LOGO_SMALL_HEIGHT, $width, $height);

        imagepng($logo, PIWIK_DOCUMENT_ROOT . '/' . $this->getPathUserLogo(), 3);
        imagepng($logoSmall, PIWIK_DOCUMENT_ROOT . '/' . $this->getPathUserLogoSmall(), 3);
        return true;
    }

}