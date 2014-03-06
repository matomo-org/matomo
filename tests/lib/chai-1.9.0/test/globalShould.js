describe('global should', function () {
  it('works', function () {
    var theGlobal = typeof window !== 'undefined'
      ? window
      : global;

    theGlobal.globalShould = chai.should();

    try {
        globalShould.not.exist(undefined);
    } finally {
        delete theGlobal.globalShould;
    }
  });
});
