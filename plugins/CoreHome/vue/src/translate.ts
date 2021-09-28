export default function translate(translationStringId: string, values: string[] = []): string {
  return window._pk_translate(translationStringId, values); // eslint-disable-line
}
