import type {Rule} from './types';

/**
 * Combine rules; the first failing rule wins.
 */
export const all = <T, V>(...rules: Rule<T, V>[]): Rule<T, V> => async (value, values) => {
    for (const rule of rules) {
        const result = await rule(value, values);
        if (result !== true) {
            return result;
        }
    }
    return true;
};
