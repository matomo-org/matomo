/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// dompurify is a devDependency of the dummy package.json this subdirectory is compiled with
/* eslint-disable-next-line import/no-extraneous-dependencies */
import DOMPurify from 'dompurify';

function hasSafeRel(rel: string) {
  const parts = rel.split(/\s+/);
  return parts.includes('noopener') && parts.includes('noreferrer');
}

// remove target=_blank if a link doesn't have noopener noreferrer
DOMPurify.addHook('afterSanitizeAttributes', (node: Element) => {
  if (node.hasAttribute('target')
    && node.getAttribute('target') === '_blank'
    && (!node.hasAttribute('rel')
      || !hasSafeRel(node.getAttribute('rel')))
  ) {
    node.removeAttribute('target');
  }
});

export function sanitize(val: unknown): string {
  // Sanitised snippets never need a stylesheet, so drop any <style> element.
  return DOMPurify.sanitize(val, { ADD_ATTR: ['target'], FORBID_TAGS: ['style'] });
}

const TOOLTIP_TAGS = ['b', 'br', 'em', 'i', 'small', 'span', 'strong', 'u'];

// DOMPurify keeps the last config it was given on the instance, and `isValidAttribute()` reads it,
// so the tooltip profile below would answer for `sanitizeUrl()` too. It gets its own instance.
const tooltipPurify = DOMPurify(window);

function asText(value: string): string {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function withLineBreaks(value: string): string {
  return value.replace(/\n/g, '<br />');
}

// A textarea's content is text, so this decodes entities without parsing any element.
function decodeEntities(value: string): string {
  const textarea = document.createElement('textarea');
  textarea.innerHTML = value;

  return textarea.value;
}

// Looks for anything that opens a tag, a comment or a declaration, rather than predicting what the
// HTML parser will make of it: it discards some tags outright (`<td>` outside a table, `<html>`),
// which would otherwise leave the title looking like plain text while its characters are dropped.
function carriesOtherMarkup(html: string): boolean {
  if (html.indexOf('<!') !== -1) {
    return true;
  }

  const tags = /<(\/?)([a-zA-Z][^\s/>]*)([^>]*)>?/g;
  const open: string[] = [];
  let tag = tags.exec(html);

  while (tag !== null) {
    const name = tag[2].toLowerCase();
    const attributes = (tag[3] || '').replace(/\/\s*$/, '').trim();

    if (!TOOLTIP_TAGS.includes(name) || attributes !== '') {
      return true;
    }

    if (tag[1] === '/') {
      // an end tag the parser cannot pair up is discarded along with its characters
      if (open.pop() !== name) {
        return true;
      }
    } else if (name !== 'br') {
      open.push(name);
    }

    tag = tags.exec(html);
  }

  return false;
}

/**
 * Renders the text of a `title` attribute as tooltip content.
 *
 * A title is read back after the browser has decoded the attribute, so it is parsed as HTML a
 * second time. Tooltips only need line breaks and simple formatting, so only a minimal set of
 * inline elements and no attributes at all are kept: content shown in a tooltip has no reason to
 * carry styling or UI state, and tooltips track the cursor and close once it leaves their target,
 * so it is never interactive either.
 *
 * A title that carries anything else was not written for a tooltip - eg. it holds a value that
 * happens to look like markup, or comes from a plugin that predates this. Rather than dropping the
 * part that is not allowed, the whole title is then shown as text, so nothing is lost from it.
 * Entities are resolved for that, so both cases display the same characters.
 *
 * The result is HTML for an element's content and must not be put into an attribute value.
 */
export function sanitizeTooltip(val: unknown): string {
  const title = val === null || val === undefined ? '' : String(val);
  const content = withLineBreaks(title);

  if (carriesOtherMarkup(content)) {
    return withLineBreaks(asText(decodeEntities(title)));
  }

  return tooltipPurify.sanitize(content, {
    ALLOWED_TAGS: TOOLTIP_TAGS,
    ALLOWED_ATTR: [],
    // both are checked separately from ALLOWED_ATTR, so they would survive on an allowed element
    ALLOW_DATA_ATTR: false,
    ALLOW_ARIA_ATTR: false,
    // keep the whole title in the body, as the check above does
    FORCE_BODY: true,
  });
}

// Returns the given URL if DOMPurify considers it a valid `href` value (i.e. it uses an allowed
// scheme such as http(s)/mailto/tel and contains no dangerous payload), otherwise an empty string.
// Use it to guard dynamic `:href`/`:src` bindings, e.g. `:href="$sanitizeUrl(url)"`.
export function sanitizeUrl(url: string): string {
  return DOMPurify.isValidAttribute('a', 'href', url) ? url : '';
}
