import type {Rule} from '../formRule';
import {isEmpty} from './helpers';
import {markMandatory} from '../formField';

/**
 * For a list: at least `min` items must be filled in. Blank items (e.g. unused rows of a repeater) don't count, and
 * a list that was never set (e.g. checkboxes nobody touched, with no default) is an empty one.
 */
export const minItems = (min: number, message: string): Rule => markMandatory(
    (v) => (v !== undefined && v !== null && !Array.isArray(v))
        || (Array.isArray(v) ? v.filter((item) => !isEmpty(item)).length : 0) >= min || message,
    min >= 1,
);
