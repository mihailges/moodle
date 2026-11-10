import type {Rule} from './types';
import {stringRule} from './helpers';

export const maxLength = (max: number, message: string): Rule => stringRule((v) => v.length <= max, message);
