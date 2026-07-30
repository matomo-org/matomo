/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import ucfirst from './ucfirst';

describe('CoreHome/ucfirst', () => {
  it('uppercases the first character of a lowercase string', () => {
    expect(ucfirst('plays')).toBe('Plays');
  });

  it('leaves an already-capitalized string unchanged', () => {
    expect(ucfirst('Visits')).toBe('Visits');
  });

  it('only changes the first character, leaving the rest verbatim', () => {
    expect(ucfirst('test string')).toBe('Test string');
  });

  it('uses Turkish casing rules when uppercasing the first character', () => {
    expect(ucfirst('istanbul', 'tr')).toBe('İstanbul');
  });

  it('uses Azerbaijani casing rules when uppercasing the first character', () => {
    expect(ucfirst('izmir', 'az')).toBe('İzmir');
  });

  it('uppercases a first character represented by a Unicode surrogate pair', () => {
    expect(ucfirst('𐐨clair', 'en')).toBe('𐐀clair');
  });

  it('leaves a leading non-letter (e.g. a %s placeholder) untouched', () => {
    expect(ucfirst('%s plays', 'tr')).toBe('%s plays');
  });

  it('returns an empty string for an empty input', () => {
    expect(ucfirst('')).toBe('');
  });

  it('returns an empty string when the value is undefined', () => {
    expect(ucfirst(undefined)).toBe('');
  });
});
