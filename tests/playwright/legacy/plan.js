/*!
 * Matomo - free/libre analytics platform
 *
 * Prints the shard matrix for GitHub Actions (shards=[{"shard":1,"label":"TagManager, Goals +4"}, ...]),
 * so each UI job is named after what it tests. Run with the shard count: node plan.js 10
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const { area, shardFiles, testFiles } = require('./specs');

const MAX_LABEL = 60;
const total = Number(process.argv[2] || 10);
const seconds = new Map(testFiles().map(({ file, seconds: s }) => [file, s]));

function label(files) {
  const areas = new Map();
  for (const file of files) {
    areas.set(area(file), (areas.get(area(file)) || 0) + seconds.get(file));
  }
  const names = [...areas.entries()].sort((a, b) => b[1] - a[1]).map(([name]) => name);
  let text = '';
  let shown = 0;
  while (shown < names.length && (text + names[shown]).length <= MAX_LABEL) {
    text += (shown ? ', ' : '') + names[shown];
    shown += 1;
  }
  return shown < names.length ? `${text} +${names.length - shown}` : text;
}

const shards = Array.from({ length: total }, (_, index) => ({
  shard: index + 1,
  label: label(shardFiles(`${index + 1}/${total}`)),
}));
process.stdout.write(`shards=${JSON.stringify(shards)}\n`);
