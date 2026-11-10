import type {Rule} from './types';

export const isEmpty = (v: unknown): boolean =>
    v === undefined || v === null || (typeof v === 'string' && v.trim() === '') || (Array.isArray(v) && v.length === 0);

// Rules are not tied to field types, as in mform. A rule paired with a value it can't evaluate
// (e.g. maxLength on a switch) passes instead of throwing or failing.
// String rules also ignore empty values; combine with `required` to make a field mandatory.
export const stringRule = (check: (v: string) => boolean, message: string): Rule => (v) =>
    typeof v !== 'string' || v === '' || check(v) || message;
