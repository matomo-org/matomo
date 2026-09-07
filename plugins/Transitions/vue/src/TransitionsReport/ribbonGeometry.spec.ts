/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { computeRibbonPaths, MIN_RIBBON_THICKNESS, RibbonRow } from './ribbonGeometry';

describe('Transitions/ribbonGeometry', () => {
  const layout = {
    side: 'incoming' as const,
    width: 100,
    centerTop: 0,
    centerHeight: 200,
  };

  function rows(...shares: number[]): RibbonRow[] {
    return shares.map((share, index) => ({
      key: `row-${index}`,
      share,
      top: index * 40,
      height: 20,
    }));
  }

  /**
   * Reads back the anchor points of a band, so the tests do not depend on the exact path string.
   */
  function anchors(d: string) {
    const n = '(-?[\\d.]+)';
    const any = '[\\d.-]+';
    const pattern = new RegExp(
      `^M${n},${n}`
      + ` L${n},${n}`
      + ` C${any},${any} ${any},${any} ${n},${n}`
      + ` L${n},${n}`,
    );

    const match = d.match(pattern);
    expect(match).not.toBeNull();

    return {
      rowX: Number(match![1]),
      rowTop: Number(match![2]),
      curveX: Number(match![3]),
      centerX: Number(match![5]),
      bandTop: Number(match![6]),
      bandBottom: Number(match![8]),
    };
  }

  it('should scale thickness by each row share of the column total', () => {
    const paths = computeRibbonPaths(rows(0.3, 0.1), layout);

    expect(paths).toHaveLength(2);
    // 0.3 and 0.1 of a 0.4 total, spread over 200px.
    expect(paths[0].thickness).toBeCloseTo(150);
    expect(paths[1].thickness).toBeCloseTo(50);
  });

  it('should stack the bands on the center edge without gaps', () => {
    const paths = computeRibbonPaths(rows(0.3, 0.1), { ...layout, centerTop: 20 });

    expect(anchors(paths[0].d).bandTop).toBeCloseTo(20);
    expect(anchors(paths[0].d).bandBottom).toBeCloseTo(170);
    expect(anchors(paths[1].d).bandTop).toBeCloseTo(170);
    expect(anchors(paths[1].d).bandBottom).toBeCloseTo(220);
  });

  it('should keep each band anchored to the vertical extent of its row', () => {
    const [path] = computeRibbonPaths(rows(0.5), layout);

    expect(anchors(path.d).rowTop).toBe(0);
    expect(path.d).toMatch(/L0,20 Z$/); // closes at the row bottom, top 0 + height 20
  });

  it('should hold the row height over a straight run before curving', () => {
    const [incoming] = computeRibbonPaths(
      rows(0.5),
      { ...layout, rowOverlap: 10, rowStraight: 16 },
    );
    const [outgoing] = computeRibbonPaths(
      rows(0.5),
      { ...layout, side: 'outgoing', rowOverlap: 10, rowStraight: 16 },
    );

    // The straight run starts inside the row and ends 16px along, still at the row's own height.
    expect(anchors(incoming.d).rowX).toBe(-10);
    expect(anchors(incoming.d).curveX).toBe(6);
    expect(anchors(outgoing.d).rowX).toBe(110);
    expect(anchors(outgoing.d).curveX).toBe(94);
  });

  it('should not let the straight run overshoot the center edge', () => {
    const [path] = computeRibbonPaths(rows(0.5), { ...layout, rowStraight: 500 });

    expect(anchors(path.d).curveX).toBe(100); // clamped to the center edge
  });

  it('should clamp the straight run on the outgoing side too', () => {
    // The outgoing side runs right to left, so the same clamp is a Math.max against x=0.
    const [path] = computeRibbonPaths(rows(0.5), {
      ...layout,
      side: 'outgoing' as const,
      rowStraight: 500,
    });

    expect(anchors(path.d).curveX).toBe(0);
  });

  it('should clamp a tiny share up to the minimum thickness', () => {
    const paths = computeRibbonPaths(rows(0.999, 0.001), layout);

    expect(paths[1].thickness).toBe(MIN_RIBBON_THICKNESS);
    // The clamp comes out of the neighbour's allowance rather than overflowing the band.
    expect(paths[0].thickness).toBeCloseTo(200 - MIN_RIBBON_THICKNESS);
  });

  it('should scale every band back when the minimums alone overflow the band', () => {
    // 100 rows each wanting the 3px minimum would need 300px in a 200px band.
    const paths = computeRibbonPaths(rows(...new Array(100).fill(0.01)), layout);
    const total = paths.reduce((sum, path) => sum + path.thickness, 0);

    expect(paths).toHaveLength(100);
    expect(total).toBeCloseTo(200);
  });

  it('should anchor incoming ribbons from the column edge to the center edge', () => {
    const [path] = computeRibbonPaths(rows(0.5), layout);

    expect(anchors(path.d).rowX).toBe(0);
    expect(anchors(path.d).centerX).toBe(100);
  });

  it('should mirror the anchoring for the outgoing side', () => {
    const [path] = computeRibbonPaths(rows(0.5), { ...layout, side: 'outgoing' });

    expect(anchors(path.d).rowX).toBe(100);
    expect(anchors(path.d).centerX).toBe(0);
  });

  it('should reach back into the row when an overlap is given', () => {
    const [incoming] = computeRibbonPaths(rows(0.5), { ...layout, rowOverlap: 8 });
    const [outgoing] = computeRibbonPaths(
      rows(0.5),
      { ...layout, side: 'outgoing', rowOverlap: 8 },
    );

    // The row end starts past the layer's edge; the center end is untouched.
    expect(anchors(incoming.d).rowX).toBe(-8);
    expect(anchors(incoming.d).centerX).toBe(100);
    expect(anchors(outgoing.d).rowX).toBe(108);
    expect(anchors(outgoing.d).centerX).toBe(0);
  });

  it('should draw nothing when the shares add up to zero', () => {
    expect(computeRibbonPaths(rows(0, 0), layout)).toEqual([]);
  });

  it('should draw nothing when there are no rows or no room', () => {
    expect(computeRibbonPaths([], layout)).toEqual([]);
    expect(computeRibbonPaths(rows(0.5), { ...layout, width: 0 })).toEqual([]);
    expect(computeRibbonPaths(rows(0.5), { ...layout, centerHeight: 0 })).toEqual([]);
  });

  it('should ignore negative and non-finite shares', () => {
    const paths = computeRibbonPaths(rows(0.5, -1, Number.NaN), layout);

    // The two unusable rows fall back to the minimum; the real row keeps the rest.
    expect(paths[1].thickness).toBe(MIN_RIBBON_THICKNESS);
    expect(paths[2].thickness).toBe(MIN_RIBBON_THICKNESS);
    expect(paths[0].thickness).toBeCloseTo(200 - 2 * MIN_RIBBON_THICKNESS);
  });
});
