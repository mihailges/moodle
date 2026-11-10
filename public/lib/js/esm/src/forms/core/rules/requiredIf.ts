import type {Condition} from '../formCondition';
import type {Rule} from '../formRule';
import {isEmpty} from './helpers';
import {markMandatory} from '../formField';

// Only applies when the condition is met by the current form values.
export const requiredIf = <V>(condition: Condition<V>, message: string): Rule<unknown, V> =>
    markMandatory((v, values) => !condition(values) || !isEmpty(v) || message, condition);
