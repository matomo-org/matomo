/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// Reject unsupported or ambiguous request parameter names.
const NULL_BYTE = '\u0000';

const APPENDS_LIKE_EMPTY_SUBSCRIPT = /\[[ \t\n\v\f\r]\]/;

const COMPLETE_SUBSCRIPTS = /^[^[]+(?:\[[^\]]*\])*$/;
const UNTERMINATED_FIRST_SUBSCRIPT = /^[^[]+\[[^\]]*$/;

const REWRITTEN_IN_A_BASE_NAME = /[. ]/g;
const REWRITTEN_IN_A_WHOLE_NAME = /[. []/g;

export function isCanonicalQueryParameterName(name: string): boolean {
  if (name === ''
    || name.includes(NULL_BYTE)
    || name.startsWith(' ')
    || APPENDS_LIKE_EMPTY_SUBSCRIPT.test(name)
  ) {
    return false;
  }

  return !name.includes('[')
    || COMPLETE_SUBSCRIPTS.test(name)
    || UNTERMINATED_FIRST_SUBSCRIPT.test(name);
}

function getServerParameterName(name: string): string {
  const firstSubscript = name.indexOf('[');

  if (firstSubscript === -1) {
    return name.replace(REWRITTEN_IN_A_BASE_NAME, '_');
  }

  if (!COMPLETE_SUBSCRIPTS.test(name)) {
    return name.replace(REWRITTEN_IN_A_WHOLE_NAME, '_');
  }

  return name.substring(0, firstSubscript).replace(REWRITTEN_IN_A_BASE_NAME, '_')
    + name.substring(firstSubscript);
}

export function reportUnsupportedQueryParameterName(name: string): void {
  console.error(`Dropping request parameter with an unsupported name: ${name}`);
}

function decodeParameterName(encodedName: string): string|null {
  try {
    return decodeURIComponent(encodedName.replace(/\+/g, '%20'));
  } catch (e) {
    return null;
  }
}

interface QueryParameter {
  pair: string;
  encodedName: string;
  name: string;
  serverName: string;
}

function parseQueryParameter(pair: string): QueryParameter|null {
  if (!pair) {
    return null;
  }

  const separatorIndex = pair.indexOf('=');
  const encodedName = separatorIndex === -1 ? pair : pair.substring(0, separatorIndex);
  const name = decodeParameterName(encodedName);

  if (name === null || !isCanonicalQueryParameterName(name)) {
    reportUnsupportedQueryParameterName(encodedName);
    return null;
  }

  return {
    pair,
    encodedName,
    name,
    serverName: getServerParameterName(name),
  };
}

// The same name repeated is one parameter, so only differing names are ambiguous.
function findAmbiguousServerNames(parameters: QueryParameter[]): Set<string> {
  const firstNameByServerName = new Map<string, string>();
  const ambiguous = new Set<string>();

  parameters.forEach(({ name, serverName }) => {
    const firstName = firstNameByServerName.get(serverName);

    if (firstName === undefined) {
      firstNameByServerName.set(serverName, name);
    } else if (firstName !== name) {
      ambiguous.add(serverName);
    }
  });

  return ambiguous;
}

/**
 * Filters unsupported parameter names from a serialized query string.
 */
export default function dropMalformedQueryParameters(queryString: string): string {
  if (!queryString) {
    return queryString;
  }

  const parameters = queryString.split('&')
    .map(parseQueryParameter)
    .filter((parameter) => parameter !== null);
  const ambiguousServerNames = findAmbiguousServerNames(parameters);

  const keptParameters = parameters.filter(({ encodedName, name, serverName }) => {
    if (serverName !== name && ambiguousServerNames.has(serverName)) {
      reportUnsupportedQueryParameterName(encodedName);
      return false;
    }

    return true;
  });

  return keptParameters.map(({ pair }) => pair).join('&');
}
