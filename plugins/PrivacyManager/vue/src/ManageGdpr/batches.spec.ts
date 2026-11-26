/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mergeExportBatches, postInBatches } from './batches';

describe('PrivacyManager/ManageGdpr/batches', () => {
  it('posts the items in sequential batches of the given size', async () => {
    const sent: number[][] = [];
    const results = await postInBatches([1, 2, 3, 4, 5], (batch) => {
      sent.push(batch);
      return Promise.resolve(batch.length);
    }, 2);

    expect(sent).toEqual([[1, 2], [3, 4], [5]]);
    expect(results).toEqual([2, 2, 1]);
  });

  it('stops at the first batch that fails', async () => {
    const send = vi.fn((batch: number[]) => (batch[0] === 1
      ? Promise.reject(new Error('failed'))
      : Promise.resolve(batch.length)));

    await expect(postInBatches([1, 2, 3], send, 2)).rejects.toThrow('failed');
    expect(send).toHaveBeenCalledTimes(1);
  });

  it('returns a single batch unchanged', () => {
    const batch = { log_visit: [{ idvisit: 1 }], custom: { key: 'value' } };

    expect(mergeExportBatches([batch])).toBe(batch);
  });

  it('appends the rows of each table across batches', () => {
    expect(mergeExportBatches([
      { log_visit: [{ idvisit: 1 }], log_link_visit_action: [{ idlink_va: 1 }] },
      { log_visit: [{ idvisit: 2 }] },
    ])).toEqual({
      log_visit: [{ idvisit: 1 }, { idvisit: 2 }],
      log_link_visit_action: [{ idlink_va: 1 }],
    });
  });

  it('lists each action once, sorted by idaction, as a single export does', () => {
    expect(mergeExportBatches([
      { log_action_log_link_visit_action_idaction_url: [{ idaction: 2, name: 'b' }, { idaction: 10, name: 'j' }] },
      { log_action_log_link_visit_action_idaction_url: [{ idaction: 1, name: 'a' }, { idaction: 10, name: 'j' }] },
    ])).toEqual({
      log_action_log_link_visit_action_idaction_url: [
        { idaction: 1, name: 'a' },
        { idaction: 2, name: 'b' },
        { idaction: 10, name: 'j' },
      ],
    });
  });

  it('sorts the tables in reverse, as a single export does', () => {
    const merged = mergeExportBatches([
      { log_visit: [{ idvisit: 1 }] },
      { log_link_visit_action: [{ idlink_va: 1 }], log_conversion: [{ idvisit: 2 }] },
    ]);

    expect(Object.keys(merged)).toEqual(['log_visit', 'log_link_visit_action', 'log_conversion']);
  });

  it('combines the keys of an entry that is an object in every batch', () => {
    expect(mergeExportBatches([
      { custom: { 1: 'first' } },
      { custom: { 2: 'second' } },
    ])).toEqual({ custom: { 1: 'first', 2: 'second' } });
  });

  it('merges the rows under the fixed keys of an object entry', () => {
    expect(mergeExportBatches([
      { custom: { sessions: [1], version: '2' } },
      { custom: { sessions: [2], version: '2' } },
    ])).toEqual({ custom: { sessions: [1, 2], version: '2' } });
  });

  it('ignores a batch that returned an object entry as an empty array', () => {
    expect(mergeExportBatches([
      { custom: { 1: 'first' } },
      { custom: [] },
    ])).toEqual({ custom: { 1: 'first' } });
  });

  it('keeps each batch value of any other entry that is not a list of rows', () => {
    expect(mergeExportBatches([
      { custom: 'first' },
      { custom: 'second' },
    ])).toEqual({ custom: ['first', 'second'] });
  });
});
