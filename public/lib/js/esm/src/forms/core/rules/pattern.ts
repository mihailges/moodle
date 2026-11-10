import type {Rule} from './types';
import {stringRule} from './helpers';

export const pattern = (regex: RegExp, message: string): Rule => stringRule((v) => regex.test(v), message);
