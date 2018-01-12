<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Translation\Loader;

/**
 * Loads a pseudo-language for developers where translation are equal to translation ids.
 */
class DevelopmentLoader implements LoaderInterface
{
    const LANGUAGE_ID = 'dev';

    /**
     * Decorated loader.
     *
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var string
     */
    private $fallbackLanguage = 'en';

    /**
     * @param LoaderInterface $loader Decorate another loader to add the pseudo-language.
     */
    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function load($language, array $directories)
    {
        if ($language !== self::LANGUAGE_ID) {
            return $this->loader->load($language, $directories);
        }

        return $this->getDevelopmentTranslations($directories);
    }

    private function getDevelopmentTranslations(array $directories)
    {
        $fallbackTranslations = $this->loader->load($this->fallbackLanguage, $directories);

        $translations = array();

        foreach ($fallbackTranslations as $section => $sectionFallbackTranslations) {
            $translationIds = array_keys($sectionFallbackTranslations);
            $sectionTranslations = $this->prefixTranslationsWithSection($section, $translationIds);

            $translations[$section] = array_combine($translationIds, $sectionTranslations);
        }

        return $translations;
    }

    private function prefixTranslationsWithSection($section, $translationIds)
    {
        return array_map(function ($translation) use ($section) {
            return $section . '_' . $translation;
        }, $translationIds);
    }
}
