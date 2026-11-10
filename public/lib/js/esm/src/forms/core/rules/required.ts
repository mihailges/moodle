import type {Rule} from './types';
import {isEmpty} from './helpers';
import {markMandatory} from '../useMoodleFormField';

export const required = (message: string): Rule => markMandatory((v) => !isEmpty(v) || message, true);
