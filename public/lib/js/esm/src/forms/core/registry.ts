import type {ComponentType} from 'react';

export type FormExtension = {
    slot: string;
    plugin: string;
    // Distinguishes several extensions of one plugin in the same slot; also their React key.
    id?: string;
    order?: number;
    // Rendered inside the form; use useMoodleFormField or the core fields so values, rules and errors are shared.
    component: ComponentType<{control: any}>;
};

const extensions: FormExtension[] = [];

const sameExtension = (a: FormExtension, b: FormExtension) =>
    a.slot === b.slot && a.plugin === b.plugin && (a.id ?? '') === (b.id ?? '');

// Registering the same slot, plugin and id again replaces the earlier entry.
export function registerFormExtension(extension: FormExtension): void {
    const existing = extensions.findIndex((e) => sameExtension(e, extension));
    if (existing >= 0) {
        extensions.splice(existing, 1);
    }
    extensions.push(extension);
}

export function getFormExtensions(slot: string): FormExtension[] {
    return extensions.filter((e) => e.slot === slot);
}
