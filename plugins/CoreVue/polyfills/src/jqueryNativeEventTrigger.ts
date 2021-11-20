/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const oldTrigger = window.$.fn.trigger;

function triggerWithNativeEventDispatch(jqEventOrType, data) {
  function nativeDispatch(element: HTMLElement) {
    const type = jqEventOrType.type || jqEventOrType;

    const onEventAttributeName = `on${type}`;
    if (element[onEventAttributeName]
      || element[type] instanceof Function
    ) {
      // if a on... (eg, onchange) handler is specified, it will be triggered by jquery.
      // it will also be triggered by addEventListener, and we don't want that so just
      // assume there is no addEventListener event.
      return;
    }

    if (element.dispatchEvent) {
      const event = new Event(type, {
        // do not rely on browser bubbling so we can keep checking for the on... attribute
        bubbles: false,
        cancelable: true,
      });
      element.dispatchEvent(event);
    }

    const parent = element.parentElement;
    if (parent) {
      nativeDispatch(parent);
    }
  }

  oldTrigger.call(this, jqEventOrType, data);

  this.each(function onEach() {
    nativeDispatch(this);
  });
}

window.$.fn.trigger = triggerWithNativeEventDispatch;
