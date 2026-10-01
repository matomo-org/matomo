/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import dropMalformedQueryParameters, {
  isCanonicalQueryParameterName,
} from './queryParameterNames';

const NUL = String.fromCharCode(0);

describe('queryParameterNames', () => {
  describe('isCanonicalQueryParameterName', () => {
    const supportedNames = [
      'foo',
      'idSite',
      'filter_pattern',
      'a-b',
      'columns[]',
      'filter_column[0]',
      'settingValues[Live][0][name]',
      'list[a b]',
      'list[a.b]',
      'list[  ]',
      'list[x]',
      'a[b[c]',
      'a!b',
      'a]b',
      'paramé',
      'filter.pattern',
      'filter pattern',
      'foo ',
      'a[b',
    ];

    const unsupportedNames = [
      `foo${NUL}suffix`,
      ' foo',
      'a[b]c',
      'a[b]]',
      'a[]x',
      'a[b][c',
      '[foo]',
      'list[ ]',
      'list[\t]',
      'list[\n]',
      'list[\v]',
      'list[\f]',
      'list[\r]',
      '',
    ];

    supportedNames.forEach((name) => {
      it(`accepts ${JSON.stringify(name)}`, () => {
        expect(isCanonicalQueryParameterName(name)).toBe(true);
      });
    });

    unsupportedNames.forEach((name) => {
      it(`rejects ${JSON.stringify(name)}`, () => {
        expect(isCanonicalQueryParameterName(name)).toBe(false);
      });
    });
  });

  describe('dropMalformedQueryParameters', () => {
    it('keeps a supported query string', () => {
      const query = 'idSite=1&period=day&columns%5B%5D=nb_visits&filter_pattern=foo.*bar';

      expect(dropMalformedQueryParameters(query)).toBe(query);
    });

    it('keeps a supported array name', () => {
      expect(dropMalformedQueryParameters('a=1&list%5Bx%5D=2')).toBe('a=1&list%5Bx%5D=2');
    });

    it('keeps a repeated array name', () => {
      const query = 'columns%5B%5D=nb_visits&columns%5B%5D=nb_actions';

      expect(dropMalformedQueryParameters(query)).toBe(query);
    });

    it('drops a name containing an unsupported character', () => {
      expect(dropMalformedQueryParameters('a=1&a%00extra=2')).toBe('a=1');
    });

    it('drops a name with unsupported leading whitespace', () => {
      expect(dropMalformedQueryParameters('a=1&%20a=2')).toBe('a=1');
      expect(dropMalformedQueryParameters('a=1&+a=2')).toBe('a=1');
    });

    it('drops an ambiguous name', () => {
      expect(dropMalformedQueryParameters('a=1&a%00alias=2')).toBe('a=1');
    });

    it('drops names with ambiguous normalized forms', () => {
      expect(dropMalformedQueryParameters('a_b=1&a.b=2')).toBe('a_b=1');
      expect(dropMalformedQueryParameters('a_b=1&a%20b=2')).toBe('a_b=1');
      expect(dropMalformedQueryParameters('a_b=1&a%5Bb=2')).toBe('a_b=1');
      expect(dropMalformedQueryParameters('a.b=1&a%20b=2')).toBe('');
    });

    it('keeps a repeated rewritten name', () => {
      expect(dropMalformedQueryParameters('a.b=1&a.b=2')).toBe('a.b=1&a.b=2');
      expect(dropMalformedQueryParameters('a%20b=1&a%20b=2')).toBe('a%20b=1&a%20b=2');
    });

    it('keeps a repeated rewritten array name', () => {
      const query = 'a.b%5B%5D=one&a.b%5B%5D=two';

      expect(dropMalformedQueryParameters(query)).toBe(query);
    });

    it('drops a rewritten name repeated alongside its normalized form', () => {
      expect(dropMalformedQueryParameters('a_b=1&a.b=2&a.b=3')).toBe('a_b=1');
    });

    it('keeps an unambiguous normalized name', () => {
      expect(dropMalformedQueryParameters('x=1&a.b=2')).toBe('x=1&a.b=2');
      expect(dropMalformedQueryParameters('x=1&a%20b=2')).toBe('x=1&a%20b=2');
      expect(dropMalformedQueryParameters('x=1&a%2Eb=2')).toBe('x=1&a%2Eb=2');
    });

    it('keeps a supported encoded name', () => {
      expect(dropMalformedQueryParameters('a=1&a%252Eb=2')).toBe('a=1&a%252Eb=2');
    });

    it('drops an unsupported subscript', () => {
      expect(dropMalformedQueryParameters('list%5B%5D=1&list%5B%20%5D=2')).toBe('list%5B%5D=1');
    });

    it('drops a name it cannot decode', () => {
      expect(dropMalformedQueryParameters('idSite=1&%E0%A4%A=x')).toBe('idSite=1');
    });

    it('changes nothing when it filters an already filtered string', () => {
      const once = dropMalformedQueryParameters('a=1&a%00x=2&b=3');

      expect(once).toBe('a=1&b=3');
      expect(dropMalformedQueryParameters(once)).toBe(once);
    });

    it('handles an empty query string', () => {
      expect(dropMalformedQueryParameters('')).toBe('');
    });

    it('keeps only supported names when serializing an object', () => {
      const params = {
        foo: 'one',
        bar: 'two',
        [`foo${NUL}x`]: 'three',
        [`bar${NUL}x`]: 'four',
      };

      expect(dropMalformedQueryParameters(window.$.param(params))).toBe('foo=one&bar=two');
    });
  });
});
