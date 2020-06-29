describe('ngDialog open', function() {
  beforeEach(function() {
    browser.get('http://localhost:3000/example/index.html');
  });

  it('should open from functions', function() {
    element(by.css('#via-service')).click();

    var EC = protractor.ExpectedConditions;
    browser.wait(EC.visibilityOf(element(by.css('.ngdialog'))), 5000);

    element(by.css('.data-passed-through')).getText()
      .then(function(text) {
        expect(text).toBe('Data passed through: from a service');
      });
  });

  it('should define specific width through a property js', function() {
    element(by.css('#js-width')).click();
    expect(element(by.css('.ngdialog-content')).getCssValue('width')).toBe('650px');
  });

  it('should define custom height through a js property', function() {
    element(by.css('#js-height')).click();
    expect(element(by.css('.ngdialog-content')).getCssValue('height')).toBe('400px');
  });
});
