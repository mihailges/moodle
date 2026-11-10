import type {Rule} from './types';
import {stringRule} from './helpers';

export const url = (message: string): Rule => stringRule((v) => URL.canParse(v), message);
