import type {Rule} from './types';
import {isEmpty} from './helpers';

export const required = (message: string): Rule => (v) => !isEmpty(v) || message;
