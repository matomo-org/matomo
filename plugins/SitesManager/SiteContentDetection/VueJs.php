<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\SiteContentDetector;
use Piwik\Url;
use Piwik\View;

class VueJs extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'Vue.js';
    }

    public static function getIcon(): string
    {
        return './plugins/SitesManager/images/vuejs.svg';
    }

    public static function getContentType(): int
    {
        return self::TYPE_JS_FRAMEWORK;
    }

    public static function getInstructionUrl(): ?string
    {
        return Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-websites-that-use-vue-js/');
    }

    public static function getPriority(): int
    {
        return 50;
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        return preg_match("/vue\.\w.+.js|vue\-\w.+.js/i", $data) === 1;
    }

    public function renderInstructionsTab(SiteContentDetector $detector): string
    {
        $view     = new View("@SitesManager/_vueTabInstructions");
        $view->sendHeadersWhenRendering = false;
        $view->wasDetected = $detector->wasDetected(self::getId());
        $view->SiteWithoutDataVueFollowStepNote2Key = StaticContainer::get('SitesManager.SiteWithoutDataVueFollowStepNote2');
        $view->vue3Code = $this->getVueInitializeCode(3);
        $view->vue2Code = $this->getVueInitializeCode(2);
        return $view->render();
    }

    public function renderOthersInstruction(SiteContentDetector $detector): string
    {
        if ($detector->wasDetected(self::getId())) {
            return ''; // don't show on others page if tab is being displayed
        }

        return sprintf(
            '<p>%s</p>',
            Piwik::translate(
                'SitesManager_SiteWithoutDataVueDescription',
                [
                    '<a target="_blank" rel="noreferrer noopener" href="' . Url::addCampaignParametersToMatomoLink('https://github.com/AmazingDreams/vue-matomo') . '">vue-matomo</a>',
                    '<a target="_blank" rel="noreferrer noopener" href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-websites-that-use-vue-js/') . '">',
                    '</a>'
                ]
            )
        );
    }

    private function getVueInitializeCode($vueVersion = '3')
    {
        $request = \Piwik\Request::fromRequest();
        $piwikUrl = Url::getCurrentUrlWithoutFileName();
        $siteId = $request->getIntegerParameter('idSite', 1);
        $configureComment = Piwik::translate('SitesManager_SiteWithoutDataVueFollowStep2ExampleCodeCommentConfigureMatomo');
        $trackViewComment = Piwik::translate('SitesManager_SiteWithoutDataVueFollowStep2ExampleCodeCommentTrackPageView');
        if ($vueVersion == 2) {
            return <<<INST
import { createApp } from 'vue'
import VueMatomo from 'vue-matomo'
import App from './App.vue'

createApp(App)
  .use(VueMatomo, {
    // $configureComment
    host: '$piwikUrl',
    siteId: $siteId,
  })
  .mount('#app')

window._paq.push(['trackPageView']); // $trackViewComment
INST;
        }

        return <<<INST
import Vue from 'vue'
import App from './App.vue'
import VueMatomo from 'vue-matomo'

Vue.use(VueMatomo, {
  host: '$piwikUrl',
  siteId: $siteId
});

new Vue({
  el: '#app',
  router,
  components: {App},
  template: ''
})

window._paq.push(['trackPageView']); // $trackViewComment
INST;
    }
}
