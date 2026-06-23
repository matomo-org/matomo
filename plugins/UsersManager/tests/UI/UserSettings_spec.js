/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("UserSettings", function () {
    this.fixture = "Piwik\\Plugins\\UsersManager\\tests\\Fixtures\\ManyUsersPastDate";

    var userSettingsUrl = "?module=UsersManager&action=userSettings";
    var userSecurityUrl = "?module=UsersManager&action=userSecurity";

    async function getAuthTokenRowCount() {
        // Exclude the empty-state row (which has no .creationDate cell).
        return page.evaluate(() => $('table.listAuthTokens tbody tr:has(.creationDate)').length);
    }

    async function getAuthTokenRows() {
        // Description is read via .html() so XSS-escape assertions (&lt;img) can run on
        // the rendered cell; expiry is the column shown either as a date or "Never".
        return page.evaluate(() => $('table.listAuthTokens tbody tr:has(.creationDate)').map(function () {
            return {
                description: $(this).find('td:nth-child(2)').html().trim(),
                expiry: $(this).find('td:nth-child(5)').text().trim(),
            };
        }).get());
    }

    before(async function() {
        await page.webpage.setViewport({
            width: 1250,
            height: 768
        });
    });

    it('should show user security page', async function () {
        await page.goto(userSecurityUrl);
        await page.waitForSelector('.listAuthTokens', { visible: true });
        await page.evaluate(() => { // give table headers constant width so the screenshot stays the same
            $('table.listAuthTokens th').css('width', '16%'); // five columns + actions
        });
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector('.admin')).to.matchImage('load_security');
    });

    it('should ask for password when trying to add token', async function () {
        await page.click('.addNewToken');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.loginSection', { visible: true });

        const passwordVisible = await page.evaluate(() => $('#login_form_password:visible').length === 1);
        const submitVisible = await page.evaluate(() => $('#login_form_submit:visible').length === 1);
        expect(passwordVisible).to.eq(true);
        expect(submitVisible).to.eq(true);
    });

    it('should accept correct password', async function () {
        await page.type('#login_form_password', superUserPassword);
        await page.click('#login_form_submit');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.addTokenForm', { visible: true });

        const formState = await page.evaluate(() => ({
            descriptionVisible: $('.addTokenForm input[id=description]:visible').length === 1,
            hasExpirationVisible: $('.addTokenForm #has_expiration').length === 1,
            submitVisible: $('.addTokenForm input[type=submit]:visible').length === 1,
        }));
        expect(formState.descriptionVisible).to.eq(true);
        expect(formState.hasExpirationVisible).to.eq(true);
        expect(formState.submitVisible).to.eq(true);
    });

    it('should create new token with default expiration date', async function () {
        await page.type('.addTokenForm input[id=description]', 'test description<img src=j&#X41vascript:alert("xss fail")>');
        await page.click('.addTokenForm .btn');
        await page.waitForNetworkIdle();
        await page.waitForFunction(
            () => $('.admin').text().indexOf('Token successfully generated') !== -1,
            { timeout: 30000 }
        );

        const successText = await page.evaluate(() => $('.admin').text());
        expect(successText).to.contain('Please store your token securely');
    });

    it('should show new token with default expire date on security page', async function () {
        await page.click('[vue-entry="UsersManager.AddNewTokenSuccess"] .btn');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.listAuthTokens', { visible: true });

        const descriptions = (await getAuthTokenRows()).map((r) => r.description);
        expect(descriptions.some((d) => d.indexOf('test description') !== -1)).to.eq(true);

        const matchingRow = descriptions.find((d) => d.indexOf('test description') !== -1);
        expect(matchingRow).to.contain('&lt;img');
        expect(matchingRow).to.not.match(/<img\b/i);
        expect(matchingRow).to.not.match(/<script\b/i);
    });

    it('should not ask for password when trying to add a second token in quick succession', async function () {
        testEnvironment.overrideConfig('General', 'auth_token_default_expiration_days', 90);
        testEnvironment.save();

        await page.goto(userSecurityUrl);
        await page.waitForSelector('.listAuthTokens', { visible: true });
        await page.click('.addNewToken');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.addTokenForm', { visible: true });

        const loginSectionVisible = await page.evaluate(() => $('.loginSection:visible').length > 0);
        expect(loginSectionVisible).to.eq(false);
    });

    it('should show a date picker with a shorter configured expire interval when clicked into the date field', async function () {
        await page.click('[name="token_expire_date"]');
        await page.waitForSelector('.ui-datepicker');

        if (testEnvironment.configOverride.General &&
          testEnvironment.configOverride.General.auth_token_default_expiration_days
        ) {
            delete testEnvironment.configOverride.General.auth_token_default_expiration_days;
            testEnvironment.save();
        }

        expect(await page.screenshotSelector('.admin')).to.matchImage('add_token_show_calendar');
    });

    it('should create new token without expiration date', async function () {
        await page.type('.addTokenForm input[id=description]', 'no expiration token');
        await page.click('.addTokenForm #has_expiration');
        await page.click('.addTokenForm .btn');
        await page.waitForNetworkIdle();
        await page.waitForFunction(
            () => $('.admin').text().indexOf('Token successfully generated') !== -1,
            { timeout: 30000 }
        );
    });

    it('should show new token without expire date on security page', async function () {
        await page.goto(userSecurityUrl);
        await page.waitForSelector('.listAuthTokens', { visible: true });

        const rows = await getAuthTokenRows();
        const noExpiryRow = rows.find((r) => r.description.indexOf('no expiration token') !== -1);
        expect(noExpiryRow, 'no-expiration token row should be present').to.not.eq(undefined);
        expect(noExpiryRow.expiry).to.eq('Never');
    });

    it('should delete all tokens without password confirmation right after one was created', async function () {
        await page.click('button.delete-all-tokens');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(200);

        const remainingRows = await getAuthTokenRowCount();
        expect(remainingRows).to.eq(0);
    });

    it('should show user settings page with all theme mode options', async function () {
        await page.goto(userSettingsUrl);
        await page.waitForSelector('input[name="themeMode"][value="auto"]');

        const themeModes = await page.evaluate(() => $('input[name="themeMode"]').map(function () {
            return $(this).val();
        }).get());
        expect(themeModes).to.include.members(['light', 'dark', 'auto']);

        const themeModeHelp = await page.evaluate(() => $('#themeModeHelp').text());
        expect(themeModeHelp).to.contain('Match browser');
        expect(themeModeHelp).to.contain('Custom theme');
    });

    it('should allow user to subscribe to newsletter', async function () {
        await page.click('#newsletterSignupCheckbox');
        await page.click('#newsletterSignupBtn input');
        await page.waitForNetworkIdle();
        await page.waitForFunction(() => !$('#newsletterSignup').is(':visible'));

        const isNewsletterVisible = await page.evaluate(() => $('#newsletterSignup').is(':visible'));
        expect(isNewsletterVisible).to.eq(false);
    });

    it('should not prompt user to subscribe to newsletter again', async function () {
        // Assumes previous test has clicked on the signup button - so we shouldn't see it this time
        await page.goto(userSettingsUrl);
        const isNewsletterVisible = await page.evaluate(() => $('#newsletterSignup').is(':visible'));
        expect(isNewsletterVisible, 'newsletter signup should stay hidden after signup').to.equal(false);
    });

    it('should ask for password confirmation when changing email', async function () {
        await page.evaluate(function () {
            $('#userSettingsTable input#email').val('testlogin123@example.com').change();
        });
        await page.waitForTimeout(100);
        await page.click('#userSettingsTable .matomo-save-button .btn');
        await page.waitForSelector('.modal.open', { visible: true });
        await page.waitForTimeout(500); // wait for animation

        const modalText = await page.evaluate(() => $('.modal.open').text().replace(/\s+/g, ' ').trim());
        expect(modalText).to.contain('Please re-authenticate to confirm this change');

        const passwordInputVisible = await page.evaluate(() => $('.modal.open #currentUserPassword:visible').length === 1);
        expect(passwordInputVisible).to.eq(true);
    });

    it('should load error when wrong password specified', async function () {
        await page.type('.modal.open #currentUserPassword', 'foobartest123');
        btnNo = await page.jQuery('.modal.open .modal-action:not(.modal-no)');
        await btnNo.click();
        await page.waitForNetworkIdle();
        await page.waitForSelector('#notificationContainer .notification', { visible: true });

        const notificationText = await page.evaluate(() => $('#notificationContainer').text());
        expect(notificationText).to.contain('The current password you entered is not correct');
    });

    it('should not allow to set the current password as new password', async function () {
        await page.goto(userSecurityUrl);
        await page.type('#password', superUserPassword);
        await page.type('#passwordBis', superUserPassword);
        await page.type('#passwordConfirmation', superUserPassword);
        await page.click('#userSettingsTable .btn');
        await page.waitForNetworkIdle();

        const bodyText = await page.evaluate(() => document.body.innerText);
        expect(bodyText).to.contain('already using this password');
    });
});
