import type {Rule} from '../formRule';
import {stringRule} from './helpers';

export const pattern = (regex: RegExp, message: string): Rule => stringRule((v) => regex.test(v), message);
