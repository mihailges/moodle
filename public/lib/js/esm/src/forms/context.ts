import {createContext} from 'react';
import type {Rule} from './rules/types';

export type FormState = {
    // Fields disabled by the server (e.g. capability driven), on top of each field's disableIf.
    serverDisabled: string[];
};

export const FormStateContext = createContext<FormState>({serverDisabled: []});

// Set by a group, so every field inside it is disabled with it, and fields sharing the group's name
// (e.g. radios or checkboxes of one choice) keep the group's rules when they register.
export const GroupContext = createContext<{disabled: boolean; name?: string; validate?: Rule}>({disabled: false});

const clone = (value: any): any => {
    if (Array.isArray(value)) {
        return value.map(clone);
    }
    if (value && typeof value === 'object' && Object.getPrototypeOf(value) === Object.prototype) {
        return Object.fromEntries(Object.entries(value).map(([k, v]) => [k, clone(v)]));
    }
    return value;
};

/**
 * Remembers which fields are on the form, so the engine can place server errors on the right field
 * and convert repeater rows, without each form describing it.
 */
export class FieldRegistry {
    private names = new Map<string, number>();
    private repeaters = new Set<string>();

    add(name: string) {
        this.names.set(name, (this.names.get(name) ?? 0) + 1);
    }

    remove(name: string) {
        const count = (this.names.get(name) ?? 0) - 1;
        if (count > 0) {
            this.names.set(name, count);
        } else {
            this.names.delete(name);
        }
    }

    // Repeaters stay known once mounted, so their rows are still converted while the repeater is hidden.
    addRepeater(name: string) {
        this.repeaters.add(name);
    }

    /**
     * The field a server error path belongs to: the field itself, a repeater row's value,
     * or the nearest enclosing field (e.g. `scopes.read` shows on the `scopes` group). Null if none.
     */
    resolve(path: string): string | null {
        const parts = path.split('.');
        for (let end = parts.length; end > 0; end--) {
            const candidate = parts.slice(0, end).join('.');
            for (const name of [candidate, `${candidate}.value`]) {
                if (this.names.has(name)) {
                    return name;
                }
            }
        }
        return null;
    }

    // Repeaters hold their rows as {value}; the form's values and payload use plain arrays.
    wrap<T>(values: T): T {
        return this.convert(values, (rows) => rows.map((value) => ({value})));
    }

    unwrap<T>(values: T): T {
        const plain = (row: any) => (row && typeof row === 'object' && 'value' in row ? row.value : row);
        return this.convert(values, (rows) => rows.map(plain));
    }

    private convert<T>(values: T, change: (rows: any[]) => any[]): T {
        const result = clone(values);
        this.repeaters.forEach((name) => {
            const parts = name.split('.');
            const last = parts.pop()!;
            const parent = parts.reduce((o, key) => o?.[key], result as any);
            if (parent && Array.isArray(parent[last])) {
                parent[last] = change(parent[last]);
            }
        });
        return result;
    }
}

export const FieldRegistryContext = createContext(new FieldRegistry());

// What a custom button changes about a save: where the payload goes, and what happens afterwards.
export type SubmitOverride = {action?: (payload: any) => Promise<any>; onSuccess?: (saved: any) => void};

export type FormActionApi = {
    // Validate and save; `override` replaces the form's own submit action and/or onSuccess.
    submit: (override?: SubmitOverride) => Promise<void>;
    submitting: boolean;
    getValues: () => any;
    setValue: (name: string, value: unknown) => void;
};

export const FormActionContext = createContext<FormActionApi | null>(null);

// Set by a repeater row, so the field inside it takes its name from the row.
export const RowContext = createContext<string | null>(null);
