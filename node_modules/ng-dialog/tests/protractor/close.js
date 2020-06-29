describe('ngDialog open', function() {
  beforeEach(function() {
    browser.get('http://localhost:3000/example/index.html');
  });

  it('should open from functions', function() {
    element(by.css('#via-service')).click();

    var EC = protractor.ExpectedConditions;
    browser.wait(EC.visibilityOf(element(by.css('.ngdialog'))), 5000);

    var plot0 = element(by.css('.ngdialog-overlay'));

    element(by.css('.close-this-dialog')).click();

    browser.wait(EC.not(EC.presenceOf(element(by.css('.ngdialog')))), 5000);
  });
});
