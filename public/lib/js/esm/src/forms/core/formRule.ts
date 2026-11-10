/**
 * The contract every validation rule fulfils: return true when valid, or an error message.
 * Matches react-hook-form's `validate` option, so rules can be async.
 * Plugins add rules by exporting a factory that returns a Rule.
 */
// A field's rules as react-hook-form's `validate` option: it runs them in order and stops at the first failure.
export type Validate = Record<string, Rule>;

export type Rule<T = any, V = any> = (value: T, values: V) => true | string | Promise<true | string>;
