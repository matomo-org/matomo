/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// each visit is posted as two input variables, so a request stays below PHP's default
// max_input_vars of 1000, past which PHP silently drops the rest of the visits. It is above the
// default gdpr_tools_max_visits of 400, so that a default export is one request and comes back
// exactly as the server orders it
export const VISITS_PER_REQUEST = 450;

export type ExportBatch = Record<string, unknown>;

export function postInBatches<T, R>(
  items: T[],
  send: (batch: T[]) => Promise<R>,
  perRequest = VISITS_PER_REQUEST,
): Promise<R[]> {
  const batches: T[][] = [];
  for (let i = 0; i < items.length; i += perRequest) {
    batches.push(items.slice(i, i + perRequest));
  }

  // one request at a time, so that a failed batch stops the ones after it
  return batches.reduce(
    (previous, batch) => previous.then((results) => send(batch).then((r) => [...results, r])),
    Promise.resolve([] as R[]),
  );
}

interface ActionRow {
  idaction: number|string;
}

// the server lists each action once per export, sorted by idaction, so batches that share
// actions must not repeat them
function mergeActionRows(rows: ActionRow[]): ActionRow[] {
  const byId = new Map<string, ActionRow>();
  rows.forEach((row) => {
    if (!byId.has(`${row.idaction}`)) {
      byId.set(`${row.idaction}`, row);
    }
  });

  return [...byId.values()].sort((a, b) => Number(a.idaction) - Number(b.idaction));
}

function isPlainObject(value: unknown): value is Record<string, unknown> {
  return !!value && typeof value === 'object' && !Array.isArray(value);
}

function groupByKey(objects: Record<string, unknown>[]): Record<string, unknown[]> {
  const valuesByKey: Record<string, unknown[]> = {};
  objects.forEach((object) => {
    Object.entries(object).forEach(([key, value]) => {
      valuesByKey[key] = valuesByKey[key] || [];
      valuesByKey[key].push(value);
    });
  });

  return valuesByKey;
}

// merges what several batches returned for one entry into what a single request would return
function mergeValues(values: unknown[]): unknown {
  // PHP encodes an empty array as [], also where it is otherwise an object
  const nonEmpty = values.filter((value) => !(Array.isArray(value) && value.length === 0));
  if (nonEmpty.length <= 1) {
    return nonEmpty.length ? nonEmpty[0] : values[0];
  }

  if (nonEmpty.every((value) => Array.isArray(value))) {
    return ([] as unknown[]).concat(...nonEmpty);
  }

  if (nonEmpty.every(isPlainObject)) {
    const merged: Record<string, unknown> = {};
    Object.entries(groupByKey(nonEmpty)).forEach(([key, keyValues]) => {
      merged[key] = mergeValues(keyValues);
    });
    return merged;
  }

  if (nonEmpty.every((value) => value === nonEmpty[0])) {
    return nonEmpty[0];
  }

  // values that cannot be combined: keep what each batch returned
  return nonEmpty;
}

export function mergeExportBatches(batches: ExportBatch[]): ExportBatch {
  if (batches.length === 1) {
    return batches[0];
  }

  const valuesByKey = groupByKey(batches);
  const merged: ExportBatch = {};
  // the server sorts the keys in reverse (krsort)
  Object.keys(valuesByKey).sort().reverse().forEach((key) => {
    const value = mergeValues(valuesByKey[key]);
    merged[key] = key.startsWith('log_action_') && Array.isArray(value)
      ? mergeActionRows(value as ActionRow[])
      : value;
  });

  return merged;
}
