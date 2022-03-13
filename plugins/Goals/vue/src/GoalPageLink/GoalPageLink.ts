/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import { Matomo, translate, MatomoUrl } from 'CoreHome';

interface GoalPageLinkArgs {
  idGoal: string|number;
}

const { $ } = window;

// usage v-goal-page-link="{ idGoal: 5 }"
const GoalPageLink = {
  mounted(el: HTMLElement, binding: DirectiveBinding<GoalPageLinkArgs>): void {
    if (!Matomo.helper.isAngularRenderingThePage()) {
      return;
    }

    const title = $(el).text();

    const link = $('<a></a>');
    link.text(title);
    link.attr('title', translate('Goals_ClickToViewThisGoal'));
    link.click((e) => {
      e.preventDefault();

      MatomoUrl.updateHash({
        ...MatomoUrl.hashParsed.value,
        category: 'Goals_Goals',
        subcategory: binding.value.idGoal,
      });
    });

    $(el).html(link[0]);
  },
};

export default GoalPageLink;

// manually handle occurrence of goal-page-link on datatable html attributes since dataTable.js is
// not managed by vue.
// eslint-disable-next-line @typescript-eslint/no-explicit-any
Matomo.on('Matomo.processDynamicHtml', ($element: JQuery) => {
  $element.find('[goal-page-link]').each((i, e) => {
    if ($(e).attr('goal-page-link-handled')) {
      return;
    }

    const idGoal = $(e).attr('goal-page-link');
    if (idGoal) {
      GoalPageLink.mounted(e, {
        instance: null,
        value: {
          idGoal,
        },
        oldValue: null,
        modifiers: {},
        dir: {},
      });
    }

    $(e).attr('goal-page-link-handled', '1');
  });
});
