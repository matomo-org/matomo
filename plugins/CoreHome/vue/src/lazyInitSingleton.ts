/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable @typescript-eslint/ban-types */

class LazySingletonHandler<T extends object, C extends { new(): T }> implements ProxyHandler<T> {
  private instance?: T;

  constructor(private type: C) {}

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  get(target: T, key: string | symbol, recv: any): any {
    if (!this.instance) {
      const Type = this.type;
      this.instance = new Type();
    }

    return Reflect.get(this.instance!, key, recv);
  }
}

export default function lazyInitSingleton<
  T extends object,
  C extends { new(): T },
>(type: C): InstanceType<C> {
  return new Proxy<InstanceType<C>>(
    {} as unknown as InstanceType<C>,
    new LazySingletonHandler<T, C>(type),
  );
}
