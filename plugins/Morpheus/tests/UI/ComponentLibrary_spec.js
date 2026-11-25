/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ComponentLibrary", function () {
    const url = "?module=Morpheus&action=componentLibrary&component=";

    before(function () {
        testEnvironment.overrideConfig('Development', 'enabled', true);
        testEnvironment.save();
    });

    // Hover and keyboard focus cannot be set from the markup. The page names, on each preview, the
    // state to show and the element that carries it, and the browser is told to apply them, so a
    // single capture shows every state of the component.
    async function forceDeclaredStates() {
        const client = await page.webpage.createCDPSession();
        await client.send('DOM.enable');
        await client.send('CSS.enable');

        const { root } = await client.send('DOM.getDocument', { depth: -1 });
        const { nodeIds } = await client.send('DOM.querySelectorAll', {
            nodeId: root.nodeId,
            selector: '[data-force-state]',
        });

        for (const previewId of nodeIds) {
            const { attributes } = await client.send('DOM.getAttributes', { nodeId: previewId });
            const attribute = (name) => attributes[attributes.indexOf(name) + 1];

            const { nodeId } = await client.send('DOM.querySelector', {
                nodeId: previewId,
                selector: attribute('data-force-target'),
            });
            // A keyboard focus is a focus too: forcing :focus-visible alone leaves the browser's own
            // outline, which :focus removes.
            const state = attribute('data-force-state');
            await client.send('CSS.forcePseudoState', {
                nodeId,
                forcedPseudoClasses: state === 'focus-visible' ? ['focus', state] : [state],
            });
        }
    }

    async function captureComponent(component) {
        await page.goto(url + component);
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);
        await forceDeclaredStates();

        // The markup under each preview is for reading, not for spotting a visual regression.
        await page.webpage.addStyleTag({ content: '.componentLibrary__code { display: none !important; }' });

        const element = await page.$('.componentLibrary__page');
        expect(await element.screenshot()).to.matchImage(component);
    }

    it("should show every variation and state of the selector", async function () {
        await captureComponent('selector');
    });
});
