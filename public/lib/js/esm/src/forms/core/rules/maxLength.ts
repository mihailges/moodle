import type {Rule} from '../formRule';
import {stringRule} from './helpers';

export const maxLength = (max: number, message: string): Rule => stringRule((v) => v.length <= max, message);
