/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const fs = require('node:fs');
const path = require('node:path');

require('./visitor-map');

const svgDir = path.join(__dirname, '..', 'svg');

function contours(d) {
  const rings = [];
  let ring = [];
  for (const token of d.match(/[MLZ]|[-\d.]+,[-\d.]+/g) || []) {
    if (token === 'M' || token === 'Z') {
      if (ring.length > 2) {
        rings.push(ring);
      }
      ring = [];
    } else if (token !== 'L') {
      const [x, y] = token.split(',');
      ring.push([+x, +y]);
    }
  }
  if (ring.length >= 2) {
    rings.push(ring);
  }
  return rings;
}

function contextPaths(svg) {
  const group = /<g[^>]*id="context"[^>]*>([\s\S]*?)<\/g>/.exec(svg);
  if (!group) {
    return {};
  }
  const paths = {};
  for (const match of group[1].matchAll(/<path\b([^>]*?)\/?>/g)) {
    const iso = /data-iso="([^"]*)"/.exec(match[1]);
    const d = /\sd="([^"]*)"/.exec(match[1]);
    if (iso && d && !paths[iso[1]]) {
      paths[iso[1]] = contours(d[1]);
    }
  }
  return paths;
}

function canvas(svg) {
  const box = /viewBox="([-\d.]+) ([-\d.]+) ([\d.]+) ([\d.]+)"/.exec(svg);
  return box ? [+box[1], +box[2], +box[1] + +box[3], +box[2] + +box[4]] : null;
}

function ringArea(ring) {
  let area = 0;
  for (let i = 0; i < ring.length; i++) {
    const p = ring[i];
    const q = ring[(i + 1) % ring.length];
    area += p[0] * q[1] - q[0] * p[1];
  }
  return area / 2;
}

function pathArea(rings) {
  return rings.reduce((total, ring) => total + ringArea(ring), 0);
}

function distance(rings, x, y) {
  let min = Infinity;
  for (const ring of rings) {
    for (let i = 0; i < ring.length; i++) {
      const p = ring[i];
      const q = ring[(i + 1) % ring.length];
      const dx = q[0] - p[0];
      const dy = q[1] - p[1];
      const length = dx * dx + dy * dy;
      const t = length ? Math.max(0, Math.min(1, ((x - p[0]) * dx + (y - p[1]) * dy) / length)) : 0;
      const ex = x - (p[0] + t * dx);
      const ey = y - (p[1] + t * dy);
      min = Math.min(min, Math.sqrt(ex * ex + ey * ey));
    }
  }
  return min;
}

function covers(rings, x, y) {
  let crossings = 0;
  for (const ring of rings) {
    for (let i = 0; i < ring.length; i++) {
      const p = ring[i];
      const q = ring[(i + 1) % ring.length];
      if ((p[1] > y) !== (q[1] > y)
        && x < p[0] + (y - p[1]) / (q[1] - p[1]) * (q[0] - p[0])
      ) {
        crossings++;
      }
    }
  }
  return crossings % 2 === 1;
}

const LABEL_CLEARANCE = 3;

describe('UserCountryMap.countryLabelPosition', function () {
  const maps = [];

  fs.readdirSync(svgDir).filter((file) => file.endsWith('.svg')).sort().forEach((file) => {
    const name = path.basename(file, '.svg');
    const svg = fs.readFileSync(path.join(svgDir, file), 'utf8');
    const paths = contextPaths(svg);
    const bounds = canvas(svg);
    if (!paths[name] || !paths[name].length || !bounds) {
      return;
    }
    const neighbours = Object.keys(paths).filter((iso) => iso !== name
      && Math.abs(pathArea(paths[iso])) > window.UserCountryMap.countryLabelMinArea);
    maps.push({ name, paths, bounds, neighbours });
  });

  it('validates every region map and every labelled neighbour', function () {
    expect(maps.length).toBeGreaterThan(200);
    expect(maps.reduce((total, map) => total + map.neighbours.length, 0)).toBeGreaterThan(700);
  });

  it.each(maps.map((map) => [map.name, map]))('places the labels of %s inside the countries they name', function (name, map) {
    const misplaced = [];

    map.neighbours.forEach((iso) => {
      const position = window.UserCountryMap.countryLabelPosition(
        { contours: map.paths[iso] },
        { contours: map.paths[name] },
        map.bounds
      );

      if (!position) {
        misplaced.push(`${iso}: no position found`);
        return;
      }

      const [x, y] = position;
      const at = `${x.toFixed(1)},${y.toFixed(1)}`;
      if (!covers(map.paths[iso], x, y)) {
        misplaced.push(`${iso}: ${at} is outside ${iso}`);
      }
      if (covers(map.paths[name], x, y)) {
        misplaced.push(`${iso}: ${at} is inside ${name}`);
      }
      const fromEdge = Math.min(x - map.bounds[0], y - map.bounds[1],
        map.bounds[2] - x, map.bounds[3] - y);
      if (fromEdge < LABEL_CLEARANCE) {
        misplaced.push(`${iso}: ${at} is ${fromEdge.toFixed(1)} from the map canvas edge`);
      }

      const fromSelected = distance(map.paths[name], x, y);
      if (fromSelected < LABEL_CLEARANCE) {
        misplaced.push(`${iso}: ${at} is ${fromSelected.toFixed(1)} from ${name}`);
      }
    });

    expect(misplaced).toEqual([]);
  });
});
