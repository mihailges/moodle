import {useEffect, useState, type ReactNode} from 'react';

const SPECIFIER = /^@moodle\/lms\/[a-z][a-z0-9_]*(\/[A-Za-z0-9_.-]+)+$/;

/**
 * Imports the modules plugins asked to load with a form, so they can register their form extensions.
 * A module that fails to load is logged and skipped; it must not stop the form.
 */
export async function loadFormExtensionModules(modules: string[]): Promise<void> {
    await Promise.all(modules.map(async(specifier) => {
        if (!SPECIFIER.test(specifier) || specifier.includes('..')) {
            window.console.error(`[forms] Ignoring invalid form extension module: ${specifier}`);
            return;
        }
        try {
            await import(specifier);
        } catch (e) {
            window.console.error(`[forms] Failed to load form extension module: ${specifier}`, e);
        }
    }));
}

/**
 * Renders its children once the extension modules have loaded, so FormSlot sees every registration.
 */
export function ExtensionModules({modules = [], children}: {modules?: string[]; children: ReactNode}) {
    const [ready, setReady] = useState(modules.length === 0);
    useEffect(() => {
        let cancelled = false;
        loadFormExtensionModules(modules).then(() => !cancelled && setReady(true));
        return () => {
            cancelled = true;
        };
    }, [modules.join('|')]);
    return ready ? <>{children}</> : null;
}
