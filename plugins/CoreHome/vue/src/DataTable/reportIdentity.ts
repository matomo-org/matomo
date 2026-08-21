/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const { $ } = window;

/**
 * The key identifying one report's actions.
 *
 * A report is addressed from two places that are not nested in one another: its table, and the
 * header rendered beside it so an ajax reload cannot replace it. Both have to land on the same
 * key, so this reads only what they share - the widget or container they sit in - and takes the
 * report id from the caller, because `data-report` lives on the table the header is not inside.
 *
 * Same identity notifyWidgetParametersChange() already saves parameters under: the widget id where
 * there is one, the container and report otherwise. A dialog marker is always prefixed, because a
 * popover renders a report that may also be on the page behind it.
 */
export default function reportIdentity(
  element?: HTMLElement | null,
  reportId?: string,
): string {
  if (!element) {
    return '';
  }

  const $el = $(element);
  const dialog = $el.closest('.ui-dialog').length ? 'dialog:' : '';

  const widget = $el.closest('[widgetId]');
  if (widget.length) {
    return `${dialog}widget:${widget.attr('widgetId')}`;
  }

  // Widget.vue puts the unique id here, including on each child of a container
  const matomoWidget = $el.closest('.matomo-widget');
  if (matomoWidget.length && matomoWidget.attr('id')) {
    return `${dialog}widget:${matomoWidget.attr('id')}`;
  }

  const id = reportId || $el.closest('[data-report]').attr('data-report') || '';
  if (!id) {
    return '';
  }

  const container = $el.closest('[containerid]');
  const containerPart = container.length ? `${container.attr('containerid')}:` : '';

  return `${dialog}report:${containerPart}${id}`;
}
