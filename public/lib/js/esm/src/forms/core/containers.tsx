import {useCallback, useContext, useEffect, useRef, type ReactNode} from 'react';
import {useFormContext, get} from 'react-hook-form';
import {AdvancedContext, ContainerContext} from './context';

/**
 * Tracks the fields inside a container and whether any of them has an error, so a collapsed
 * or advanced container can show itself rather than hide the message.
 */
export function useContainer(advanced?: boolean) {
    const names = useRef(new Map<string, number>());
    const {formState: {errors}} = useFormContext();
    const add = useCallback((name: string) => {
        names.current.set(name, (names.current.get(name) ?? 0) + 1);
        return () => {
            const count = (names.current.get(name) ?? 1) - 1;
            if (count > 0) {
                names.current.set(name, count);
            } else {
                names.current.delete(name);
            }
        };
    }, []);
    const hasError = [...names.current.keys()].some((name) => Boolean(get(errors, name)));

    const {show, register} = useContext(AdvancedContext);
    useEffect(() => (advanced ? register() : undefined), [advanced, register]);

    return {
        add,
        hasError,
        // Advanced content is hidden, not removed: its fields stay registered, so they keep their values and rules.
        hidden: Boolean(advanced) && !show && !hasError,
    };
}

export function ContainerProvider({add, children}: {add: (name: string) => () => void; children: ReactNode}) {
    return <ContainerContext.Provider value={{add}}>{children}</ContainerContext.Provider>;
}
