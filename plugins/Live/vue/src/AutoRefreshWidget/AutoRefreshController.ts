/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

type RefreshResult = {
  updated: boolean;
};

type RefreshHandlerResult = RefreshResult | boolean;

const DEFAULT_INTERVAL_MS = 3000;
const DEFAULT_MAX_INTERVAL_MS = 300000;

export type AutoRefreshControllerOptions<TResponse> = {
  getBaseInterval?: () => number | null | undefined;
  getMaxInterval?: () => number | null | undefined;
  shouldRun: () => boolean;
  request: () => Promise<TResponse>;
  handleResponse: (response: TResponse) => Promise<RefreshHandlerResult> | RefreshHandlerResult;
  onError?: (error: unknown) => void;
};

export class AutoRefreshController<TResponse> {
  private options: AutoRefreshControllerOptions<TResponse>;

  private currentInterval: number;

  private updateInterval: number | null = null;

  private visibilityListenerId: number | null = null;

  constructor(options: AutoRefreshControllerOptions<TResponse>) {
    this.options = options;
    this.currentInterval = this.resolveBaseInterval();
    this.setupVisibilityHandling();
  }

  private getUpdatedResult(result: RefreshHandlerResult): boolean {
    if (typeof result === 'boolean') {
      return result;
    }

    return result.updated;
  }

  private resolveBaseInterval(): number {
    if (this.options.getBaseInterval) {
      const interval = Number(this.options.getBaseInterval());
      if (Number.isFinite(interval) && interval > 0) {
        return interval;
      }
    }

    return DEFAULT_INTERVAL_MS;
  }

  private resolveMaxInterval(): number {
    if (this.options.getMaxInterval) {
      const interval = Number(this.options.getMaxInterval());
      if (Number.isFinite(interval) && interval > 0) {
        return interval;
      }
    }

    return DEFAULT_MAX_INTERVAL_MS;
  }

  private clearUpdate(): void {
    if (this.updateInterval) {
      window.clearTimeout(this.updateInterval);
      this.updateInterval = null;
    }
  }

  private getVisibility(): VisibilityGlobal | null {
    const { Visibility: visibility } = window;
    if (!visibility || !visibility.isSupported || !visibility.isSupported()) {
      return null;
    }

    return visibility;
  }

  private isTabHidden(): boolean {
    const visibility = this.getVisibility();
    return Boolean(visibility && visibility.hidden());
  }

  private setupVisibilityHandling(): void {
    const visibility = this.getVisibility();
    if (!visibility) {
      return;
    }

    this.visibilityListenerId = visibility.change(() => {
      if (visibility.hidden()) {
        this.clearUpdate();
      } else if (this.options.shouldRun()) {
        this.update();
      }
    });
  }

  private teardownVisibilityHandling(): void {
    const visibility = this.getVisibility();
    if (!visibility || typeof this.visibilityListenerId !== 'number') {
      return;
    }

    visibility.unbind(this.visibilityListenerId);
    this.visibilityListenerId = null;
  }

  schedule(delayMs: number): void {
    const nextDelay = Number.isFinite(delayMs) && delayMs > 0
      ? delayMs
      : this.resolveBaseInterval();

    this.clearUpdate();
    if (!this.options.shouldRun()) {
      return;
    }

    this.updateInterval = window.setTimeout(() => {
      this.update();
    }, nextDelay);
  }

  update(): void {
    if (!this.options.shouldRun()) {
      return;
    }
    if (this.isTabHidden()) {
      return;
    }

    this.options.request()
      .then((response) => Promise.resolve(this.options.handleResponse(response)))
      .then((result) => {
        const baseInterval = this.resolveBaseInterval();
        const isUpdated = this.getUpdatedResult(result);

        if (isUpdated) {
          this.currentInterval = baseInterval;
        } else {
          this.currentInterval += baseInterval;
        }

        if (this.currentInterval > this.resolveMaxInterval()) {
          this.currentInterval = this.resolveMaxInterval();
        }

        this.schedule(this.currentInterval);
      })
      .catch((error) => {
        if (this.options.onError) {
          this.options.onError(error);
        }

        this.schedule(this.resolveBaseInterval());
      });
  }

  start(): void {
    this.currentInterval = 0;
    this.update();
  }

  stop(): void {
    this.clearUpdate();
  }

  destroy(): void {
    this.stop();
    this.teardownVisibilityHandling();
  }
}
