describe('ngDialog closeByNavigation', function () {

    it('should close on state change with legacy ui-router', function () {
        // Load page
        browser.get('http://localhost:3000/example/browser-back-button/index.html');

        // Click button to go to 'about' state
        element(by.css('[ui-sref=about]')).click();

        // Expect a visible dialog on the page
        var EC = protractor.ExpectedConditions;
        browser.wait(EC.visibilityOf(element(by.css('.ngdialog'))), 5000);

        // Go back to 'home' state
        browser.navigate().back();

        // Expect there's no visible dialog on the page
        browser.wait(EC.not(EC.presenceOf(element(by.css('.ngdialog')))), 5000);
    });

    it('should close on state change with legacy ui-router', function () {
        // Load page
        browser.get('http://localhost:3000/example/browser-back-button/ui-router-1.0.0-rc.1.html');

        // Click button to go to 'about' state
        element(by.css('[ui-sref=about]')).click();

        // Expect a visible dialog on the page
        var EC = protractor.ExpectedConditions;
        browser.wait(EC.visibilityOf(element(by.css('.ngdialog'))), 5000);

        // Go back to 'home' state
        browser.navigate().back();

        // Expect there's no visible dialog on the page
        browser.wait(EC.not(EC.presenceOf(element(by.css('.ngdialog')))), 5000);
    });

});
