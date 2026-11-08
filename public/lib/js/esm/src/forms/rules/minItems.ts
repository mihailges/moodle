import type {Rule} from './types';
import {isEmpty} from './helpers';

/**
 * For a list: at least `min` items must be filled in. Blank items (e.g. unused rows of a repeater) don't count.
 */
export const minItems = (min: number, message: string): Rule => (v) =>
    !Array.isArray(v) || v.filter((item) => !isEmpty(item)).length >= min || message;
