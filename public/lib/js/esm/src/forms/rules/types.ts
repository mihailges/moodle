/**
 * The contract every validation rule fulfils: return true when valid, or an error message.
 * Matches react-hook-form's `validate` option, so rules can be async.
 * Plugins add rules by exporting a factory that returns a Rule.
 */
export type Rule<T = any, V = any> = (value: T, values: V) => true | string | Promise<true | string>;
