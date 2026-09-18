/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
// only the error classification is needed here, and pulling in all of CoreHome starts requests
vi.mock('CoreHome', async () => {
  const requestError = await vi.importActual(
    '../../../../CoreHome/vue/src/AjaxHelper/requestError',
  ) as { default: (error: unknown) => boolean };

  return { isRetryableRequestError: requestError.default };
});

import { AutoRefreshController } from './AutoRefreshController';

describe('Live/AutoRefreshController', () => {
  const BASE_INTERVAL = 1000;
  const MAX_INTERVAL = 8000;

  function failure(status: number, statusText = 'error') {
    return { status, statusText };
  }

  function makeController(
    overrides: Partial<ConstructorParameters<typeof AutoRefreshController>[0]> = {},
  ) {
    const request = vi.fn().mockResolvedValue('response');
    const handleResponse = vi.fn().mockReturnValue({ updated: true });
    const shouldRun = vi.fn().mockReturnValue(true);

    const controller = new AutoRefreshController<string>({
      getBaseInterval: () => BASE_INTERVAL,
      getMaxInterval: () => MAX_INTERVAL,
      shouldRun,
      request,
      handleResponse,
      ...overrides,
    });

    return {
      controller, request, handleResponse, shouldRun,
    };
  }

  /**
   * Lets pending promises settle, and any timer due within the given time fire.
   */
  async function tick(ms = 0) {
    await vi.advanceTimersByTimeAsync(ms);
  }

  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.runOnlyPendingTimers();
    vi.useRealTimers();
  });

  it('keeps refreshing after a failed request', async () => {
    const request = vi.fn().mockRejectedValue(failure(500));
    const { controller } = makeController({ request });

    controller.update();
    await tick();
    expect(request).toHaveBeenCalledTimes(1);

    await tick(BASE_INTERVAL);
    expect(request).toHaveBeenCalledTimes(2);
  });

  it('waits twice as long after each consecutive failure', async () => {
    const request = vi.fn().mockRejectedValue(failure(503));
    const { controller } = makeController({ request });

    controller.update();
    await tick();

    await tick(BASE_INTERVAL);
    expect(request).toHaveBeenCalledTimes(2);

    await tick(BASE_INTERVAL * 2 - 1);
    expect(request).toHaveBeenCalledTimes(2);
    await tick(1);
    expect(request).toHaveBeenCalledTimes(3);

    await tick(BASE_INTERVAL * 4 - 1);
    expect(request).toHaveBeenCalledTimes(3);
    await tick(1);
    expect(request).toHaveBeenCalledTimes(4);
  });

  it('never waits longer than the maximum interval', async () => {
    const request = vi.fn().mockRejectedValue(failure(500));
    const { controller } = makeController({ request });

    controller.update();
    await tick();

    // 1000, 2000, 4000, then clamped at 8000
    await tick(BASE_INTERVAL + BASE_INTERVAL * 2 + BASE_INTERVAL * 4);
    expect(request).toHaveBeenCalledTimes(4);

    await tick(MAX_INTERVAL);
    expect(request).toHaveBeenCalledTimes(5);
    await tick(MAX_INTERVAL);
    expect(request).toHaveBeenCalledTimes(6);
  });

  it('goes back to the normal interval once a request succeeds again', async () => {
    const request = vi.fn()
      .mockRejectedValueOnce(failure(500))
      .mockRejectedValueOnce(failure(500))
      .mockResolvedValueOnce('response')
      .mockRejectedValue(failure(500));
    const { controller } = makeController({ request });

    controller.update();
    await tick();
    await tick(BASE_INTERVAL);
    await tick(BASE_INTERVAL * 2);
    expect(request).toHaveBeenCalledTimes(3);

    // the third request succeeded, so the fourth is due after the regular interval again
    await tick(BASE_INTERVAL);
    expect(request).toHaveBeenCalledTimes(4);

    // and that failure starts counting from the beginning
    await tick(BASE_INTERVAL);
    expect(request).toHaveBeenCalledTimes(5);
  });

  it('never retries sooner than the normal interval', async () => {
    const request = vi.fn().mockRejectedValue(failure(500));
    const { controller } = makeController({
      request,
      getBaseInterval: () => MAX_INTERVAL * 2,
    });

    controller.update();
    await tick();

    await tick(MAX_INTERVAL * 2 - 1);
    expect(request).toHaveBeenCalledTimes(1);
    await tick(1);
    expect(request).toHaveBeenCalledTimes(2);
  });

  it('recovers from an aborted request', async () => {
    const request = vi.fn().mockRejectedValue(failure(0, 'abort'));
    const { controller } = makeController({ request });

    controller.update();
    await tick();

    await tick(BASE_INTERVAL);
    expect(request).toHaveBeenCalledTimes(2);
  });

  it('stops refreshing when the server rejected the request', async () => {
    const onError = vi.fn();
    const request = vi.fn().mockRejectedValue(failure(401));
    const { controller } = makeController({ request, onError });

    controller.update();
    await tick();
    expect(request).toHaveBeenCalledTimes(1);
    expect(onError).toHaveBeenCalledTimes(1);

    await tick(MAX_INTERVAL * 4);
    expect(request).toHaveBeenCalledTimes(1);
  });

  it('keeps refreshing when handling a successful response fails', async () => {
    const handleResponse = vi.fn(() => {
      throw new Error('could not render');
    });
    const { controller, request } = makeController({ handleResponse });

    controller.update();
    await tick();
    expect(request).toHaveBeenCalledTimes(1);

    await tick(BASE_INTERVAL);
    expect(request).toHaveBeenCalledTimes(2);
  });

  it('still slows down while nothing changes', async () => {
    const handleResponse = vi.fn().mockReturnValue({ updated: false });
    const { controller, request } = makeController({ handleResponse });

    controller.update();
    await tick();

    await tick(BASE_INTERVAL * 2);
    expect(request).toHaveBeenCalledTimes(2);

    await tick(BASE_INTERVAL * 3);
    expect(request).toHaveBeenCalledTimes(3);
  });

  it('does not schedule a retry once it should no longer run', async () => {
    const shouldRun = vi.fn().mockReturnValue(true);
    const request = vi.fn().mockRejectedValue(failure(500));
    const { controller } = makeController({ request, shouldRun });

    controller.update();
    await tick();
    expect(request).toHaveBeenCalledTimes(1);

    shouldRun.mockReturnValue(false);

    await tick(MAX_INTERVAL * 4);
    expect(request).toHaveBeenCalledTimes(1);
  });
});
