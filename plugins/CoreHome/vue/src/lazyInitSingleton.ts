/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

class LazySingletonHandler<T> implements ProxyHandler<T> {
  private instance?: T;

  get(target: T, key, recv): ReturnType<ProxyHandler<T>['get']> {
    if (!this.instance) {
      this.instance = new T();
    }

    return Reflect.get(this.instance, key, recv);
  }
}

export default function lazyInitSingleton<T>(): T {
  return new Proxy<T>(null, new LazySingletonHandler<T>());
}
