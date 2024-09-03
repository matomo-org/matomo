/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const { $ } = window;

export default {
  mounted(el: HTMLElement): void {
    $(el).off('click').on(
      'click',
      '.visits-live-launch-visitor-profile',
      function onClickLaunchProfile(this: HTMLElement, e: Event) {
        e.preventDefault();
        window.broadcast.propagateNewPopoverParameter(
          'visitorProfile',
          $(this).attr('data-visitor-id'),
        );
        return false;
      },
    ).tooltip({
      track: true,
      content() {
        const title = $(this).attr('title') || '';
        return window.vueSanitize(title.replace(/\n/g, '<br />'));
      },
      show: { delay: 100, duration: 0 },
      hide: false,
    });
  },
};
