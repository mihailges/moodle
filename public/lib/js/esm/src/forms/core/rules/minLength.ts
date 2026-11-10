import type {Rule} from '../formRule';
import {stringRule} from './helpers';

export const minLength = (min: number, message: string): Rule => stringRule((v) => v.length >= min, message);
