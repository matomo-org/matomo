/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    var DATA_ATTR = 'data-auto-clear-password';
    var trackedFields = new WeakMap();

    function collectPasswordInputs(el)
    {
        var targets = [];

        if (el.tagName === 'INPUT' && el.type === 'password') {
            targets.push(el);
        } else {
            var nested = el.querySelectorAll('input[type="password"]');
            nested.forEach(nestedEl => targets.push(nestedEl));
        }

        return targets;
    }

    function setupAutoClear(el, delay)
    {
        var timeoutId = null;
        var lastValue = el.value;

        var clearValue = () => {
            el.value = '';
            el.dispatchEvent(new Event('input'));
        };

        var resetTimer = () => {
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            timeoutId = setTimeout(clearValue, delay * 1000);
        };

        var inputListener = () => resetTimer();
        var changeListener = () => resetTimer();

        el.addEventListener('input', inputListener);
        el.addEventListener('change', changeListener);

        // Watch for programmatic value changes
        var intervalId = setInterval(() => {
            if (el.value !== lastValue) {
                lastValue = el.value;
                resetTimer();
            }
        }, 250);

        // Store cleanup references
        trackedFields.set(el, () => {
            clearTimeout(timeoutId);
            clearInterval(intervalId);
            el.removeEventListener('input', inputListener);
            el.removeEventListener('change', changeListener);
        });
    }

    function initAutoClear(parentEl = document)
    {
        if (!(parentEl instanceof Element || parentEl instanceof Document)) {
            return;
        }

        var elements = parentEl.querySelectorAll(`[${DATA_ATTR}]`);
        elements.forEach(el => {
            var delayAttr = el.getAttribute(DATA_ATTR);
            var delay = parseInt(delayAttr, 10);
            var effectiveDelay = isNaN(delay) ? 600 : delay;

            var passwordInputs = collectPasswordInputs(el);
            passwordInputs.forEach(input => {
                if (trackedFields.has(input)) {
                    return;
                }

                setupAutoClear(input, effectiveDelay);
            });
        });
    }

    // Defer setup until DOM is ready and set up DOM observer
    document.addEventListener('DOMContentLoaded', () => {
        initAutoClear();

        var observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => initAutoClear(node));
                mutation.removedNodes.forEach(node => {
                    if (node instanceof HTMLElement && trackedFields.has(node)) {
                        var cleanup = trackedFields.get(node);
                        if (cleanup && typeof cleanup === 'function') {
                            cleanup();
                        }
                        trackedFields.delete(node);
                    }
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });
})();
