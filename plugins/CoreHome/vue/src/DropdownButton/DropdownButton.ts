/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const { $ } = window;

/**
 * Usage (in a component):
 *
 * directives: {
 *   ExpandOnClick: ExpandOnClick(), // function call is important since we store state
 *                                   // in this directive
 * }
 */
export default {
  mounted(el: HTMLElement): void {
    const element = $(el);

    // BC for materializecss 0.97 => 1.0
    if (!element.attr('data-target')
      && element.attr('data-activates')
    ) {
      element.attr('data-target', element.attr('data-activates'));
    }

    const target = element.attr('data-target');
    if (target && $(`#${target}`).length) {
      $(element).dropdown({
        inDuration: 300,
        outDuration: 225,
        constrainWidth: false, // Does not change width of dropdown to that of the activator
        //  hover: true, // Activate on hover
        belowOrigin: true, // Displays dropdown below the button
      });
    }
  },
};
