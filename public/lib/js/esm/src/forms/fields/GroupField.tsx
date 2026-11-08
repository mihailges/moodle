import type {ReactNode} from 'react';
import {useController} from 'react-hook-form';
import {useFieldConditions, type FieldBehaviour} from '../useMoodleFormField';
import {GroupContext} from '../context';
import {all} from '../rules/all';

type GroupFieldProps = FieldBehaviour & {
    // The path of the object holding the group's fields, e.g. 'duration' for duration.value and duration.unit.
    name: string;
    label: string;
    // How the fields are arranged: side by side (default) or stacked.
    layout?: 'row' | 'column';
    children: ReactNode;
};

/**
 * Lays out several fields under one label. Fields inside use `${name}.<key>` as their names.
 * hideIf and disableIf apply to every field inside, and rules validate the group's object as a whole.
 */
export function GroupField({name, label, layout = 'row', rules, hideIf, disableIf, children}: GroupFieldProps) {
    const validate = rules?.length ? all(...rules) : undefined;
    const {fieldState} = useController({name, rules: {validate}});
    const {hidden, disabled} = useFieldConditions(name, {hideIf, disableIf});
    if (hidden) {
        return null;
    }

    // Child errors are objects nested under the group's path; only a group-level error has a message.
    const message = fieldState.error?.message;

    return (
        <GroupContext.Provider value={{disabled, name, validate}}>
            <fieldset>
                <legend className="fs-6 mb-2">{label}</legend>
                <div className={layout === 'row' ? 'd-flex gap-2 align-items-start' : 'd-flex flex-column gap-2'}>{children}</div>
                {message && <div role="alert" className="text-danger small mt-1">{message}</div>}
            </fieldset>
        </GroupContext.Provider>
    );
}
