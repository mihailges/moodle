import type {Rule} from '../formRule';
import {stringRule} from './helpers';

export const url = (message: string): Rule => stringRule((v) => URL.canParse(v), message);
