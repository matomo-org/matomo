/*
 * General utils for managing cookies in Typescript.
 */
// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
export function setCookie(name: string, val: string, day: number) {
  const date = new Date();

  // Set it expire in n days
  date.setTime(date.getTime() + (day * 24 * 60 * 60 * 1000));

  // Set it
  document.cookie = `${name}=${val}; expires=${date.toUTCString()}; path=/`;
}

// eslint-disable-next-line consistent-return,@typescript-eslint/explicit-module-boundary-types
export function getCookie(name: string) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);

  // eslint-disable-next-line eqeqeq
  if (parts.length == 2) {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore
    return parts.pop().split(';').shift();
  }
}

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
export function deleteCookie(name: string) {
  const date = new Date();

  // Set it expire in -1 days
  date.setTime(date.getTime() + (-1 * 24 * 60 * 60 * 1000));

  // Set it
  document.cookie = `${name}=; expires=${date.toUTCString()}; path=/`;
}
