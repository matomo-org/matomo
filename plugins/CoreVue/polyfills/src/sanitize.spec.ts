/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { sanitize, sanitizeTooltip, sanitizeUrl } from './sanitize';

const ALLOWED_TAGS = ['B', 'BR', 'EM', 'I', 'SMALL', 'SPAN', 'STRONG', 'U'];

// the text a viewer ends up seeing, as the browser parses the result
function shownTextOf(html: string): string {
  const container = document.createElement('div');
  container.innerHTML = html;

  return container.textContent || '';
}

// tags outside the tooltip profile and any attribute at all, as the browser parses the result
function activeMarkupIn(html: string): string[] {
  const container = document.createElement('div');
  container.innerHTML = html;

  const active: string[] = [];
  container.querySelectorAll('*').forEach((node) => {
    const tag = node.tagName.toLowerCase();
    if (!ALLOWED_TAGS.includes(node.tagName)) {
      active.push(tag);
    }
    Array.from(node.attributes).forEach((attribute) => active.push(`${tag}[${attribute.name}]`));
  });

  return active;
}

describe('CoreVue/sanitizeTooltip', () => {
  it('turns line breaks into <br /> so multi line titles keep their layout', () => {
    expect(sanitizeTooltip('first\nsecond')).toEqual('first<br>second');
  });

  it('handles missing values', () => {
    expect(sanitizeTooltip(undefined)).toEqual('');
    expect(sanitizeTooltip(null)).toEqual('');
  });

  it('keeps the formatting titles actually use', () => {
    expect(sanitizeTooltip('report ratio<br/><br/> suffix')).toEqual('report ratio<br><br> suffix');
    expect(sanitizeTooltip('Conversions: <b>12</b>')).toEqual('Conversions: <b>12</b>');
  });

  it('leaves already escaped text alone, so double escaped titles read correctly', () => {
    expect(sanitizeTooltip('a &lt;img&gt; b &amp; c')).toEqual('a &lt;img&gt; b &amp; c');
  });

  // a title that does not use the tooltipAttr filter - eg. one from a plugin - arrives with its
  // markup intact; showing all of it as text keeps it readable without rendering any of it
  it('shows a title that carries anything else as text, in full', () => {
    expect(sanitizeTooltip('<img src="x">')).toEqual('&lt;img src=&quot;x&quot;&gt;');
    expect(sanitizeTooltip('<a href="https://matomo.org">link</a>'))
      .toEqual('&lt;a href=&quot;https://matomo.org&quot;&gt;link&lt;/a&gt;');
    expect(sanitizeTooltip('<span data-table-type="JqplotGraph">y</span>'))
      .toEqual('&lt;span data-table-type=&quot;JqplotGraph&quot;&gt;y&lt;/span&gt;');
    expect(sanitizeTooltip('first\n<b onclick="alert(1)">second</b>'))
      .toEqual('first<br />&lt;b onclick=&quot;alert(1)&quot;&gt;second&lt;/b&gt;');
  });

  // markup that leads the title is parsed into the head unless the body is forced, which used to
  // hide it from the check that picks the text branch - and it was then dropped instead of shown
  it('keeps the whole title even when its markup would be parsed into the head', () => {
    const titles = [
      '<style>b{color:red}</style><b>n</b>',
      '<script>alert(1)</script>o',
      '<template><b>r</b></template>q',
      '<title>Report</title>y',
      '<meta charset="utf-8">z',
      '<base href="x">z',
      '<link rel="x">z',
      '<!-- a comment -->',
      '<b>x</b><template>y</template>',
      // an end tag the parser cannot pair up is discarded along with its characters
      'x</span>y',
      'a</b>b',
      'Page URL: /a</div>/b',
      // the parser discards these outright in a body, so inspecting its result cannot see them
      '<td>x</td>',
      '<td>',
      '<tr><th>h</th></tr>',
      '<colgroup><col>z',
      '<html class="a">text',
    ];

    titles.forEach((title) => {
      const shown = shownTextOf(sanitizeTooltip(title));
      expect({ title, shown }).toEqual({ title, shown: title });
    });
  });

  it('displays the same characters in both branches', () => {
    // a value that is escaped twice reads back escaped once, whichever branch it ends up in
    expect(sanitizeTooltip('a &lt;img&gt; b &amp; c')).toEqual('a &lt;img&gt; b &amp; c');
    expect(sanitizeTooltip('a &lt;img&gt; b &amp; c <span class="x"></span>'))
      .toEqual('a &lt;img&gt; b &amp; c &lt;span class=&quot;x&quot;&gt;&lt;/span&gt;');
  });

  it('leaves nothing active in a title that was never escaped', () => {
    const titles = [
      '<img src="x" onerror="alert(1)">',
      '<div class="dataTable" data-report="r" data-table-type="JqplotGraph" data-data="{}">y</div>',
      '<a href="javascript:alert(1)">z</a>',
      '<span style="position:fixed;inset:0">w</span>',
      '<b onclick="alert(1)">v</b>',
      '<iframe src="x"></iframe>u',
      '<form action="x"><input name="y"></form>t',
      '<svg><animate onbegin="alert(1)"></animate></svg>s',
      '<template><b>r</b></template>q',
      '<noscript><p title="</noscript><img src=x onerror=alert(1)>">p</noscript>',
      '<script>alert(1)</script>o',
      '<style>b{color:red}</style><b>n</b>',
    ];

    titles.forEach((title) => {
      const active = activeMarkupIn(sanitizeTooltip(title));
      expect({ title, active }).toEqual({ title, active: [] });
    });
  });

  it('leaves the url check alone, which reads the config the sanitizer keeps', () => {
    expect(sanitizeUrl('https://matomo.org')).toEqual('https://matomo.org');

    sanitizeTooltip('<b>x</b>');

    expect(sanitizeUrl('https://matomo.org')).toEqual('https://matomo.org');
  });

  it('is stricter than the general sanitizer, which is why tooltips have their own', () => {
    const markup = '<div class="aClass" data-some-attribute="1">label</div>';

    expect(sanitize(markup)).toEqual(markup);
    expect(sanitizeTooltip(markup)).not.toContain('<div');
  });
});
