/*!
 * Piwik - free/libre analytics platform
 *
 * Site selector screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("UsersManager", function () {
    this.timeout(0);
    this.fixture = "Piwik\\Plugins\\UsersManager\\tests\\Fixtures\\ManyUsers";

    var url = "?module=UsersManager&action=index";

    function assertScreenshotEquals(screenshotName, done, test)
    {
        expect.screenshot(screenshotName).to.be.captureSelector('#content', test, done);
    }

    function openGiveAccessForm(page) {
        page.click('#showGiveViewAccessForm');
    }

    function setLoginOrEmailForGiveAccessForm(page, loginOrEmail)
    {
        page.evaluate(function () {
            $('#user_invite').val('');
        });
        page.sendKeys('#user_invite', loginOrEmail);
    }

    function submitGiveAccessForm(page)
    {
        page.click('#giveUserAccessToViewReports');
        page.wait(1000); // we wait in case error notification is still fading in and not fully visible yet
    }

    before(function () {
        testEnvironment.idSitesAdminAccess = [1,2];
        testEnvironment.save();
    });

    after(function () {
        delete testEnvironment.idSitesAdminAccess;
        testEnvironment.save();
    });

    it("should show only users having access to same site", function (done) {
        assertScreenshotEquals("loaded_as_admin", done, function (page) {
            page.load(url);
        });
    });

    it("should open give view access form when clicking on button", function (done) {
        assertScreenshotEquals("adminuser_give_view_access_form_opened", done, function (page) {
            openGiveAccessForm(page);
        });
    });

    it("should show an error when nothing entered", function (done) {
        assertScreenshotEquals("adminuser_give_view_access_no_user_entered", done, function (page) {
            submitGiveAccessForm(page);
        });
    });

    it("should show an error when no such user found", function (done) {
        assertScreenshotEquals("adminuser_give_view_access_user_not_found", done, function (page) {
            setLoginOrEmailForGiveAccessForm(page, 'anyNoNExistingUser');
            submitGiveAccessForm(page);
        });
    });

    it("should show an error if user already has access", function (done) {
        assertScreenshotEquals("adminuser_give_view_access_user_already_has_access", done, function (page) {
            setLoginOrEmailForGiveAccessForm(page, 'login2');
            submitGiveAccessForm(page);
        });
    });

    it("should add a user by login", function (done) {
        assertScreenshotEquals("adminuser_give_view_access_via_login", done, function (page) {
            setLoginOrEmailForGiveAccessForm(page, 'login3');
            submitGiveAccessForm(page);
        });
    });

    it("should add a user by email", function (done) {
        assertScreenshotEquals("adminuser_give_view_access_via_email", done, function (page) {
            page.load(url);
            openGiveAccessForm(page);
            setLoginOrEmailForGiveAccessForm(page, 'login4@example.com');
            submitGiveAccessForm(page);
        });
    });

    it("should ask for confirmation when all sites selected", function (done) {
        assertScreenshotEquals("adminuser_all_users_loaded", done, function (page) {
            page.load(url + '&idSite=all');
        });
    });

    it("should ask for confirmation when all sites selected", function (done) {
        expect.screenshot("adminuser_all_users_confirmation").to.be.captureSelector('.modal.open', function (page) {
            openGiveAccessForm(page);
            setLoginOrEmailForGiveAccessForm(page, 'login5@example.com');
            submitGiveAccessForm(page);
        }, done);
    });
});