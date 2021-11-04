/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import RateFeature from './FeedbackQuestion.vue';

export default createAngularJsAdapter({
  component: RateFeature,
  scope: {
    showQuestionBanner: {
      angularJsBind: '@',
    },
  },
  directiveName: 'piwikFeedbackQuestion',
});
