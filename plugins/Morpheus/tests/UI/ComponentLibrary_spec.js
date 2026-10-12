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

    async function capture(example, name) {
        const element = await page.$(`[data-example="${example}"]`);
        expect(await element.screenshot()).to.matchImage(name);
    }

    async function reset() {
        await page.mouse.move(-10, -10);
        await page.evaluate(() => document.activeElement && document.activeElement.blur());
    }

    describe("ComponentLibrarySelector", function () {
        const examples = [
            'label',
            'arrow',
            'icon',
            'iconOnly',
            'placeholder',
            'startAction',
            'endAction',
            'bothActions',
            'fullWidth',
            'open',
            'disabled',
            'readOnly',
            'disabledAction',
        ];

        before(async function () {
            await page.goto(url + 'selector');
            await page.waitForNetworkIdle();
        });

        examples.forEach(function (example) {
            it(`should show the ${example} example`, async function () {
                await reset();
                await capture(example, example);
            });
        });

        it("should frame the whole selector when its trigger is hovered", async function () {
            await reset();
            await page.hover('[data-example="bothActions"] .mtm-selector__trigger');
            await capture('bothActions', 'trigger_hover');
        });

        it("should ring only the action when an action is hovered", async function () {
            await reset();
            await page.hover('[data-example="bothActions"] .mtm-selector__action--start');
            await capture('bothActions', 'action_hover');
        });

        it("should frame the whole selector when its trigger has the keyboard focus", async function () {
            await reset();
            await page.focus('[data-example="bothActions"] .mtm-selector__action--start');
            await page.keyboard.press('Tab');
            await capture('bothActions', 'trigger_focus');
        });

        it("should ring only the action when an action has the keyboard focus", async function () {
            await page.keyboard.press('Tab');
            await capture('bothActions', 'action_focus');
        });
    });
});
