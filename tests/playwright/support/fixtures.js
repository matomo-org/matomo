/*!
 * Matomo - free/libre analytics platform
 *
 * Sets up PHP fixtures through tests:setup-fixture, like TestingEnvironment.setupFixture(). Each fixture
 * (per plugin) is set up once per run, dumped, and restored before every spec that uses it, so specs
 * start from the same data no matter which specs ran before them.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const { execFileSync } = require('child_process');

const ROOT = path.resolve(__dirname, '../../..');
const ENV_FILE = path.join(ROOT, 'tmp/testingPathOverride.json');
const SNAPSHOT_DIR = path.join(ROOT, 'tmp/playwright-fixtures');
const MATOMO_URL = new URL(process.env.MATOMO_URL || 'http://localhost/');
const DEFAULT_FIXTURE = 'Piwik\\Tests\\Fixtures\\UITestFixture';
const SERVER_GLOBAL = { HTTP_HOST: MATOMO_URL.host, REQUEST_URI: '/', REMOTE_ADDR: '127.0.0.1' };

function readEnvironment() {
  try {
    return JSON.parse(fs.readFileSync(ENV_FILE, 'utf8') || '{}');
  } catch (e) {
    return {};
  }
}

function writeEnvironment(data) {
  fs.writeFileSync(ENV_FILE, JSON.stringify(data));
}

/** Reads [database_tests] from config/config.ini.php, for the mysql client calls below. */
function testDatabaseConfig() {
  const ini = fs.readFileSync(path.join(ROOT, 'config/config.ini.php'), 'utf8');
  const section = (ini.split(/^\[database_tests\]\s*$/m)[1] || '').split(/^\[/m)[0];
  const value = (key, fallback) => {
    const match = section.match(new RegExp(`^${key}[ \\t]*=[ \\t]*"?([^"\\n]*)"?`, 'm'));
    return match ? match[1].trim() : fallback;
  };
  return { host: value('host', '127.0.0.1'), user: value('username', 'root'), password: value('password', '') };
}

function mysqlArgs() {
  const { host, user, password } = testDatabaseConfig();
  return [`-h${host}`, `-u${user}`, ...(password ? [`-p${password}`] : [])];
}

function runConsole(args) {
  execFileSync(process.env.MATOMO_PHP || 'php', [path.join(ROOT, 'console'), ...args], { cwd: ROOT, stdio: 'inherit' });
}

function snapshotPaths(fixtureClass, plugin) {
  const id = crypto.createHash('sha1').update(`${fixtureClass}|${plugin || ''}`).digest('hex').slice(0, 12);
  return { sql: path.join(SNAPSHOT_DIR, `${id}.sql`), env: path.join(SNAPSHOT_DIR, `${id}.json`) };
}

function restoreDatabase(dbName, sqlFile) {
  const sql = `DROP DATABASE IF EXISTS \`${dbName}\`; CREATE DATABASE \`${dbName}\`;`;
  execFileSync('mysql', [...mysqlArgs(), '-e', sql]);
  execFileSync('mysql', [...mysqlArgs(), dbName], { input: fs.readFileSync(sqlFile), maxBuffer: 1024 * 1024 * 1024 });
}

/**
 * Prepares the database and tmp/testingPathOverride.json for a spec. `plugin` mirrors tests:run-ui --plugin
 * (the fixture loads that plugin). With `persist: false` the fixture is set up from scratch every time and
 * must be torn down with teardownFixture(), like specs that set 'persist-fixture-data': false.
 * PLAYWRIGHT_FIXTURE_DROP=1 ignores snapshots left over from earlier local runs.
 */
function prepareFixture({ fixtureClass = DEFAULT_FIXTURE, plugin, persist = true } = {}) {
  const snapshot = snapshotPaths(fixtureClass, plugin);
  const reuse = persist && fs.existsSync(snapshot.sql) && fs.existsSync(snapshot.env)
    && (!process.env.PLAYWRIGHT_FIXTURE_DROP || prepareFixture.fresh.has(snapshot.sql));

  if (reuse) {
    const environment = JSON.parse(fs.readFileSync(snapshot.env, 'utf8'));
    restoreDatabase(environment.dbName, snapshot.sql);
    writeEnvironment(environment);
    return environment;
  }

  writeEnvironment({});
  runConsole([
    'tests:setup-fixture',
    fixtureClass,
    '--set-symlinks',
    `--server-global=${JSON.stringify(SERVER_GLOBAL)}`,
    ...(persist ? ['--persist-fixture-data', '--drop'] : []),
    ...(plugin ? [`--plugins=${plugin}`] : []),
  ]);

  // the PHP side writes the fixture database and plugin list here, so merge into it
  const environment = { ...readEnvironment(), fixtureClass, ...(plugin ? { pluginsToLoad: [plugin] } : {}) };
  writeEnvironment(environment);

  if (persist) {
    fs.mkdirSync(SNAPSHOT_DIR, { recursive: true });
    const dump = execFileSync('mysqldump', [...mysqlArgs(), '--single-transaction', '--skip-lock-tables', '--no-tablespaces', environment.dbName], { maxBuffer: 1024 * 1024 * 1024 });
    fs.writeFileSync(snapshot.sql, dump);
    fs.writeFileSync(snapshot.env, JSON.stringify(environment));
    prepareFixture.fresh.add(snapshot.sql);
  }
  return environment;
}
prepareFixture.fresh = new Set();

function teardownFixture(fixtureClass = DEFAULT_FIXTURE) {
  runConsole(['tests:setup-fixture', fixtureClass, '--teardown', `--server-global=${JSON.stringify(SERVER_GLOBAL)}`]);
}

module.exports = {
  DEFAULT_FIXTURE,
  ENV_FILE,
  MATOMO_URL,
  ROOT,
  SERVER_GLOBAL,
  prepareFixture,
  readEnvironment,
  teardownFixture,
  writeEnvironment,
};
