import type {Rule} from './types';

export const minItems = (min: number, message: string): Rule => (v) => !Array.isArray(v) || v.length >= min || message;
