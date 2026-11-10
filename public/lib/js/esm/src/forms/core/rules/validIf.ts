import type {Rule} from './types';

/**
 * A rule from a condition and a message: valid when `isValid` returns true, otherwise the message is the error.
 * For custom checks, e.g. validIf((flows) => flows.auth_code || flows.client_credentials, 'Select a flow').
 */
export const validIf = <T = any, V = any>(isValid: (value: T, values: V) => unknown, message: string): Rule<T, V> =>
    (value, values) => Boolean(isValid(value, values)) || message;
