/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

vi.mock('CoreHome', async () => {
  const { translateStub } = await import('./TransitionsReport/testCoreHomeMock');
  return {
    Matomo: {
      helper: {
        // as piwikHelper.htmlEntities
        htmlEntities: (value: string) => value
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/{{/g, '{&#8291;{'),
        htmlDecode: (value: string) => {
          const textArea = document.createElement('textarea');
          textArea.innerHTML = value;
          return textArea.value;
        },
      },
    },
    translate: translateStub,
  };
});

import {
  encodeHiddenCharacters,
  readableUrl,
  removedMessage,
  removedMessageHtml,
  stopConfirmLabel,
} from './preprocessedPageUrls';

describe('Transitions/preprocessedPageUrls', () => {
  describe('readableUrl', () => {
    it.each([
      ['UTF-8', 'https://example.org/%E6%A9%9F%E8%83%BD', 'https://example.org/機能'],
      ['an encoded percent sign once', 'https://example.org/100%25?q=%2541', 'https://example.org/100%?q=%41'],
      ['a plus sign as it is', 'https://example.org/?q=a+b%2Bc', 'https://example.org/?q=a+b+c'],
      ['an invalid byte encoded, the rest of its run decoded', 'https://example.org/%C3%A9%FF%C3%A9', 'https://example.org/é%FFé'],
      ['a truncated sequence encoded', 'https://example.org/%E6%A9', 'https://example.org/%E6%A9'],
      ['HTML entities decoded', 'https://example.org/?a=1&amp;b=%3Cx%3E', 'https://example.org/?a=1&b=<x>'],
      ['entities decoded once', 'https://example.org/?a=&amp;sect;&amp;amp;', 'https://example.org/?a=&sect;&amp;'],
      ['a raw ampersand that starts like an entity as it is', 'https://example.org/?a=1&section=2&copy=3', 'https://example.org/?a=1&section=2&copy=3'],
    ])('should show %s', (description, url, expected) => {
      expect(readableUrl(url)).toBe(expected);
    });

    it.each([
      ['a right-to-left override', 'https://example.org/%E2%80%AEfdp.exe', 'https://example.org/%E2%80%AEfdp.exe'],
      ['a zero-width space', 'https://example.org/a%E2%80%8Bb', 'https://example.org/a%E2%80%8Bb'],
      ['a control character', 'https://example.org/a%0Ab', 'https://example.org/a%0Ab'],
      ['a raw right-to-left override', 'https://example.org/‮fdp.exe', 'https://example.org/%E2%80%AEfdp.exe'],
      ['an HTML-encoded right-to-left override', 'https://example.org/&#8238;fdp.exe', 'https://example.org/%E2%80%AEfdp.exe'],
    ])('should keep %s encoded', (description, url, expected) => {
      expect(readableUrl(url)).toBe(expected);
    });
  });

  describe('encodeHiddenCharacters', () => {
    it('should encode only control and format characters', () => {
      expect(encodeHiddenCharacters('https://example.org/a\u202Eb\u200Bc機%41'))
        .toBe('https://example.org/a%E2%80%AEb%E2%80%8Bc機%41');
    });
  });

  describe('removedMessage', () => {
    it('should name the page as the confirmation does', () => {
      expect(removedMessage('http://www.example.org/%E6%A9%9F'))
        .toBe('Transitions_PreprocessedPageRemoved:example.org/機');
    });
  });

  describe('removedMessageHtml', () => {
    it.each([
      ['as stored, HTML-encoded', 'http://example.org/%3Cimg%20src%3Dx%3E?a=1&amp;section=2'],
      ['with a raw ampersand', 'http://example.org/%3Cimg%20src%3Dx%3E?a=1&section=2'],
    ])('should escape a decoded page name with markup and a query string, %s', (_, url) => {
      const message = removedMessageHtml(url);
      expect(message).toBe(
        'Transitions_PreprocessedPageRemoved:example.org/&lt;img src=x&gt;?a=1&amp;section=2',
      );

      // rendered as HTML, as a notification does: the name shows once decoded, without markup
      const container = document.createElement('div');
      container.innerHTML = message;
      expect(container.querySelector('img')).toBeNull();
      expect(container.textContent)
        .toBe('Transitions_PreprocessedPageRemoved:example.org/<img src=x>?a=1&section=2');
    });

    it('should keep the plain text message unescaped for text interpolation', () => {
      expect(removedMessage('http://example.org/%3Cimg%20src%3Dx%3E?a=1&amp;section=2'))
        .toBe('Transitions_PreprocessedPageRemoved:example.org/<img src=x>?a=1&section=2');
    });
  });

  describe('stopConfirmLabel', () => {
    it('should name the page without scheme and www., readable', () => {
      expect(stopConfirmLabel('https://www.example.org/%E6%A9%9F?x=%E2%80%AE'))
        .toBe('Transitions_StopPreprocessingConfirm:example.org/機?x=%E2%80%AE');
    });
  });
});
