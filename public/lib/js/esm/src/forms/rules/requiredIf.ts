import type {Condition} from '../conditions';
import type {Rule} from './types';
import {isEmpty} from './helpers';

// Only applies when the condition is met by the current form values.
export const requiredIf = <V>(condition: Condition<V>, message: string): Rule<unknown, V> =>
    (v, values) => !condition(values) || !isEmpty(v) || message;
