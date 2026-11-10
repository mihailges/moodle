import type {Rule} from '../formRule';
import {isEmpty} from './helpers';
import {markMandatory} from '../formField';

export const required = (message: string): Rule => markMandatory((v) => !isEmpty(v) || message, true);
