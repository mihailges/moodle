import {get} from 'react-hook-form';

/**
 * A pure, synchronous predicate over the form values.
 * `deps` lists the field paths it reads so the form only watches what it needs.
 */
export type Condition<V = any> = ((values: V) => boolean) & {deps: string[]};

const make = <V>(deps: string[], fn: (values: V) => boolean): Condition<V> => Object.assign(fn, {deps});

const isEmpty = (v: unknown): boolean =>
    v === undefined || v === null || v === '' || (Array.isArray(v) && v.length === 0);

export const eq = <V = any>(field: string, expected: unknown): Condition<V> =>
    make([field], (values) => get(values, field) === expected);

export const neq = <V = any>(field: string, expected: unknown): Condition<V> =>
    make([field], (values) => get(values, field) !== expected);

export const checked = <V = any>(field: string): Condition<V> =>
    make([field], (values) => Boolean(get(values, field)));

export const notEmpty = <V = any>(field: string): Condition<V> =>
    make([field], (values) => !isEmpty(get(values, field)));

export const not = <V = any>(condition: Condition<V>): Condition<V> =>
    make(condition.deps, (values) => !condition(values));

export const anyOf = <V = any>(...conditions: Condition<V>[]): Condition<V> =>
    make(conditions.flatMap((c) => c.deps), (values) => conditions.some((c) => c(values)));

export const allOf = <V = any>(...conditions: Condition<V>[]): Condition<V> =>
    make(conditions.flatMap((c) => c.deps), (values) => conditions.every((c) => c(values)));
