function getStorage(): Storage | null {
  return typeof sessionStorage === 'undefined' ? null : sessionStorage;
}

export function getStoredValue(key: string): string | null {
  return getStorage()?.getItem(key) ?? null;
}

export function setStoredValue(key: string, value: string): void {
  const storage = getStorage();
  if (storage) {
    storage.setItem(key, value);
  }
}

export function removeStoredValue(key: string): void {
  const storage = getStorage();
  if (storage) {
    storage.removeItem(key);
  }
}

export function consumeStoredValue(key: string): string | null {
  const storage = getStorage();
  const value = storage?.getItem(key) ?? null;
  if (value !== null && storage) {
    storage.removeItem(key);
  }

  return value;
}
