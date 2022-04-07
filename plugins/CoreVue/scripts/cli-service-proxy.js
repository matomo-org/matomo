/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const fs = require('fs');
const readline = require('readline');

const middlePositions = {};
function getMiddlePosition(file) {
  if (!middlePositions[file] && fs.existsSync(file)) {
    const fileContents = fs.readFileSync(file).toString('utf-8');
    const scriptIndex = fileContents.indexOf('<script lang="ts"');
    if (scriptIndex !== -1) {
      middlePositions[file] = (fileContents.substring(0, scriptIndex).match(/\n/g) || []).length;
    }
  }
  return middlePositions[file];
}

function interceptWrite(originalWrite) {
  return function (chunk, encoding, cb) {
    if (typeof chunk === 'string' && /tsl.*?ERROR/.test(chunk)) {
      chunk = chunk.replace(/(\/(?:.(?!\())+.)\((\d+)(,\d+\))/g, (m, file, line, rest) => {
        if (/.vue.ts$/.test(file)) {
          file = file.substring(0, file.length - 3);
          const middleLine = getMiddlePosition(file);
          return `${file}(${middleLine + parseInt(line, 10)}${rest}`;
        }
        return m;
      });
    }

    return originalWrite.call(this, chunk, encoding, cb);
  };
}

process.stdout.clearLine = readline.clearLine.bind(null, process.stdout);
process.stdout.cursorTo = readline.cursorTo.bind(null, process.stdout);

process.stderr.clearLine = readline.clearLine.bind(null, process.stderr);
process.stderr.cursorTo = readline.cursorTo.bind(null, process.stderr);

process.stdout.write = interceptWrite(process.stdout.write);
process.stderr.write = interceptWrite(process.stderr.write);

require('../../../node_modules/@vue/cli-service/bin/vue-cli-service.js');
