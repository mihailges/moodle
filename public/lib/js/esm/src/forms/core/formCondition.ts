/**
 * A pure, synchronous predicate over the current form values, e.g. (v) => !v.flows?.auth_code.
 */
export type Condition<V = any> = (values: V) => boolean;
