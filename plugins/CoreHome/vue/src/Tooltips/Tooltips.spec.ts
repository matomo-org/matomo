/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import Tooltips from './Tooltips';

// The directive only ever reads binding.value, so a minimal object is enough.
// A custom `content` avoids the default transform's dependency on
// window.vueSanitize, and `show: false` opens tooltips without the show delay.
function makeBinding(value: Record<string, unknown> = {}) {
  return { value: { content: () => 'tooltip content', show: false, ...value } };
}

const directive = Tooltips as unknown as {
  mounted(el: HTMLElement, binding: unknown): void;
  updated(el: HTMLElement, binding: unknown): void;
  beforeUnmount(el: HTMLElement): void;
};

const { $ } = window;

// Lets the deferred setupTooltips (setTimeout) and any pending MutationObserver
// callback run before we assert.
function flush(): Promise<void> {
  return new Promise((resolve) => { setTimeout(resolve, 0); });
}

function openTooltipFor(target: HTMLElement): void {
  // jQuery UI binds `mouseover`/`focusin` on the host and resolves the hovered
  // descendant via `.closest(items)`, so triggering mouseover on the target
  // (which bubbles to the host) opens its delegated tooltip.
  $(target).trigger('mouseover');
}

function openTooltipCount(): number {
  return document.querySelectorAll('.ui-tooltip').length;
}

describe('CoreHome/Tooltips directive', () => {
  let host: HTMLElement;

  beforeEach(() => {
    host = document.createElement('div');
    document.body.appendChild(host);
  });

  afterEach(async () => {
    try {
      directive.beforeUnmount(host);
    } catch (e) {
      // already torn down
    }
    host.remove();
    document.querySelectorAll('.ui-tooltip').forEach((node) => node.remove());
    await flush();
  });

  it('closes a tooltip whose target is removed from the DOM natively', async () => {
    const button = document.createElement('a');
    button.setAttribute('title', 'Edit');
    host.appendChild(button);

    directive.mounted(host, makeBinding());
    await flush();

    openTooltipFor(button);
    // sanity check: the tooltip really opened, so the test is not vacuous
    expect(openTooltipCount()).toBe(1);

    // Native removal (eg. a Vue v-if/v-else swap) fires neither mouseleave nor
    // jQuery's `remove` event, which is what used to orphan the tooltip.
    button.remove();
    await flush();

    expect(openTooltipCount()).toBe(0);
  });

  it('leaves an open tooltip alone when a different element is removed', async () => {
    const edit = document.createElement('a');
    edit.setAttribute('title', 'Edit');
    const remove = document.createElement('a');
    remove.setAttribute('title', 'Remove');
    host.appendChild(edit);
    host.appendChild(remove);

    directive.mounted(host, makeBinding());
    await flush();

    openTooltipFor(edit);
    expect(openTooltipCount()).toBe(1);

    // The open tooltip's target (edit) stays attached, so it must not be closed.
    remove.remove();
    await flush();

    expect(openTooltipCount()).toBe(1);
  });

  it('disconnects the observer and destroys the tooltip on unmount', async () => {
    const button = document.createElement('a');
    button.setAttribute('title', 'Edit');
    host.appendChild(button);

    directive.mounted(host, makeBinding());
    await flush();

    expect($(host).tooltip('instance')).toBeTruthy();

    directive.beforeUnmount(host);

    expect($(host).tooltip('instance')).toBeFalsy();

    // With the widget destroyed and the observer disconnected, removing a child
    // must neither reopen, throw, nor leave a tooltip behind.
    openTooltipFor(button);
    button.remove();
    await flush();

    expect(openTooltipCount()).toBe(0);
  });
});
