/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

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

  const dialog = element.closest('.ui-dialog') ? 'dialog:' : '';

  const widget = element.closest('[widgetId]');
  if (widget) {
    return `${dialog}widget:${widget.getAttribute('widgetId')}`;
  }

  // Widget.vue puts the unique id here, including on each child of a container
  const matomoWidget = element.closest('.matomo-widget');
  if (matomoWidget?.id) {
    return `${dialog}widget:${matomoWidget.id}`;
  }

  const id = reportId || element.closest('[data-report]')?.getAttribute('data-report') || '';
  if (!id) {
    return '';
  }

  const container = element.closest('[containerid]');
  const containerPart = container ? `${container.getAttribute('containerid')}:` : '';

  return `${dialog}report:${containerPart}${id}`;
}
