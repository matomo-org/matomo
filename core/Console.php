<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik;

use Piwik\Plugins\CoreConsole\CodeCoverage;
use Piwik\Plugins\CoreConsole\GenerateApi;
use Piwik\Plugins\CoreConsole\GenerateController;
use Piwik\Plugins\CoreConsole\GeneratePlugin;
use Piwik\Plugins\CoreConsole\GenerateSettings;
use Piwik\Plugins\CoreConsole\GenerateVisualizationPlugin;
use Piwik\Plugins\CoreConsole\GitCommit;
use Piwik\Plugins\CoreConsole\GitPull;
use Piwik\Plugins\CoreConsole\GitPush;
use Piwik\Plugins\CoreConsole\RunTests;
use Piwik\Plugins\CoreConsole\Translations\CreatePull;
use Piwik\Plugins\CoreConsole\Translations\FetchFromOTrance;
use Piwik\Plugins\CoreConsole\Translations\LanguageCodes;
use Piwik\Plugins\CoreConsole\Translations\LanguageNames;
use Piwik\Plugins\CoreConsole\Translations\PluginsWithTranslations;
use Piwik\Plugins\CoreConsole\Translations\SetTranslations;
use Piwik\Plugins\CoreConsole\Translations\Update;
use Piwik\Plugins\CoreConsole\WatchLog;
use Symfony\Component\Console\Application;

class Console
{
    public function run()
    {
        $console = new Application();

        $console->add(new RunTests());
        $console->add(new GeneratePlugin());
        $console->add(new GenerateApi());
        $console->add(new GenerateSettings());
        $console->add(new GenerateController());
        $console->add(new GenerateVisualizationPlugin());
        $console->add(new WatchLog());
        $console->add(new GitPull());
        $console->add(new GitCommit());
        $console->add(new GitPush());
        $console->add(new PluginsWithTranslations());
        $console->add(new LanguageCodes());
        $console->add(new LanguageNames());
        $console->add(new FetchFromOTrance());
        $console->add(new SetTranslations());
        $console->add(new Update());
        $console->add(new CreatePull());
        $console->add(new CodeCoverage());

        $console->run();
    }
}
