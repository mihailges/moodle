import type {Rule} from './types';
import {stringRule} from './helpers';

export const minLength = (min: number, message: string): Rule => stringRule((v) => v.length >= min, message);
