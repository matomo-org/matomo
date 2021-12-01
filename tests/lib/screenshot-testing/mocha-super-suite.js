app.init();
app.loadTestModules();

try {
  require('./local-extras');
} catch (e) {
  // ignore
}
