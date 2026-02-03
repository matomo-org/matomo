/*!
 * Matomo - free/libre analytics platform
 *
 * ManageGoals UI tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ManageGoals", function () {
    this.fixture = 'Piwik\\Tests\\Fixtures\\SomePageGoalVisitsWithConversions';

    const manageGoalsUrl = "?module=CoreHome&action=index&idSite=1&period=year&date=2009-01-01#?idSite=1&period=year&date=2009-01-01&category=Goals_Goals&subcategory=Goals_ManageGoals";

    async function fillField(selector, value) {
        await page.$eval(selector, (el) => {
            el.value = '';
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });
        await page.type(selector, value);
    }

    it("should show correct notification when creating a new goal", async function () {
        await page.goto(manageGoalsUrl);
        await page.waitForNetworkIdle();

        await page.waitForSelector('#add-goal');
        await page.click('#add-goal');
        await page.waitForSelector('.addEditGoal', { visible: true });

        const goalName = 'My name';
        await fillField('#goal_name', goalName);
        await fillField('#pattern', '/thank-you');

        const saveButton = await page.waitForSelector('.addEditGoal .matomo-save-button .btn');
        await saveButton.click();

        await page.waitForNetworkIdle();

        // We check that the created goal id is in the View Goal Report url
        const createdGoalId = await page.$eval(
          'div.manageGoals table.entityTable tbody tr:last-child td:first-child',
          (cell) => cell.textContent.trim()
        );
        const notificationText = await page.$eval(
          '.notification.notification-success .notification-body div',
          (el) => el.textContent.trim(),
        );
        const viewGoalLinkHref = await page.$eval(
          '.notification.notification-success a',
          (link) => link.getAttribute('href')
        );
        const expectedNotificationText = "Goal successfully created [View Goal Report]";
        expect(notificationText).to.equal(expectedNotificationText);
        expect(viewGoalLinkHref).to.include(`subcategory=${createdGoalId}`);
    });
    it("should show the correct notification when editing the goal", async function () {
      const goalEditButtonSelector = 'table.entityTable tbody tr:nth-last-child(1) button.icon-edit';
      await page.click(goalEditButtonSelector);

      const updatedGoalName = 'My edited name';
      await fillField('#goal_name', updatedGoalName);

      const updateButton = await page.waitForSelector('.addEditGoal .matomo-save-button .btn');
      await updateButton.click();
      await page.waitForNetworkIdle();

      // We check that the edited goal id is in the View Goal Report url
      const editedGoalId = await page.$eval(
        'div.manageGoals table.entityTable tbody tr:last-child td:first-child',
        (cell) => cell.textContent.trim()
      );
      const viewGoalLinkHref = await page.$eval(
        '.notification.notification-success a',
        (link) => link.getAttribute('href')
      );
      const notificationText = await page.$eval(
        '.notification.notification-success .notification-body div',
        (el) => el.textContent.trim(),
      );
      const expectedNotificationText = "Goal successfully updated [View Goal Report]";
      expect(notificationText).to.equal(expectedNotificationText);
      expect(viewGoalLinkHref).to.include(`subcategory=${editedGoalId}`);
    });
});
