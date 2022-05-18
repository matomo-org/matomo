/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/

import './GoalPageLink/GoalPageLink.adapter';
import './ManageGoals/ManageGoals.adapter';

export { default as GoalPageLink } from './GoalPageLink/GoalPageLink.ts';
export { default as ManageGoals } from './ManageGoals/ManageGoals.vue';
export { default as ManageGoalsStore } from './ManageGoals/ManageGoals.store';
export { default as PiwikApiMock } from './ManageGoals/PiwikApiMock';
