import {useCallback, useContext, useEffect, useRef, useState, type ReactNode} from 'react';
import {useFormContext, get} from 'react-hook-form';
import {AdvancedContext, ContainerContext} from '../formContext';

type SectionFieldProps = {
    title: string;
    // Whether the section starts expanded. Defaults to true.
    defaultOpen?: boolean;
    // Only shown after the form's "Show more" button.
    advanced?: boolean;
    children: ReactNode;
};

/**
 * A collapsible section of fields, like mform's header with collapsed state. Its fields stay on the form
 * while collapsed, so they keep their values and rules, and the section opens if one of them has an error.
 */
export function SectionField({title, defaultOpen = true, advanced, children}: SectionFieldProps) {
    const [open, setOpen] = useState(defaultOpen);

    // The fields inside register their names, so the section knows whether any of them has an error.
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
    // Advanced content is hidden, not removed: its fields stay registered, so they keep their values and rules.
    const hidden = Boolean(advanced) && !show && !hasError;

    return (
        <ContainerContext.Provider value={{add}}>
            <details
                className="border rounded p-3"
                hidden={hidden}
                open={open || hasError}
                onToggle={(e) => setOpen(e.currentTarget.open)}
            >
                <summary className="fs-5 fw-bold">{title}</summary>
                <div className="d-flex flex-column gap-3 mt-3">{children}</div>
            </details>
        </ContainerContext.Provider>
    );
}
