import {useContext, useEffect, useRef, useState, type ReactElement} from 'react';
import {useFieldArray, useFormContext, useWatch} from 'react-hook-form';
import {Button} from '@moodlehq/design-system';
import {getString} from '@moodle/lms/core/stringUtils';
import {useFieldConditions, useIsRequired, toValidate, requiredIndicator, type FieldBehaviour} from '../formField';
import {FieldRegistryContext, RowContext} from '../formContext';

type RepeaterFieldProps = FieldBehaviour & {
    name: string;
    label: string;
    // Text of the add button. Without it the button is the standard "Add" with a plus icon.
    addLabel?: string;
    // The value of a new row, e.g. ''. The form's values stay a plain array; rows are wrapped as {value} internally.
    emptyItem?: unknown;
    minRows?: number;
    // Text of each remove button. Without it the buttons are the standard icon-only cross.
    removeLabel?: string;
    // The single field repeated for each row, without a name: the row names it. For example
    // <TextField label="URL" rules={[url('Invalid')]} />. Rules on the repeater itself get the list of rows.
    children: ReactElement;
};

export function RepeaterField({
    name, label, addLabel, emptyItem = '', minRows = 1, removeLabel, rules, hideIf, disableIf, advanced, children,
}: RepeaterFieldProps) {
    const {control, clearErrors, getFieldState, formState, getValues, setValue, trigger} = useFormContext();
    const registry = useContext(FieldRegistryContext);
    const [defaults, setDefaults] = useState({add: 'Add', remove: 'Remove'});
    useEffect(() => {
        Promise.all([getString('add'), getString('remove')])
            .then(([add, remove]) => setDefaults({add, remove}))
            .catch(() => undefined);
    }, []);
    registry.addRepeater(name);
    // Forms hold plain arrays; useFieldArray needs object rows, so convert them before it first reads them.
    const initial = getValues(name);
    if (Array.isArray(initial) && initial.some((row) => row === null || typeof row !== 'object' || !('value' in row))) {
        const rows = toRows(initial.map((row) => row?.value ?? row));
        setValue(name, rows);
        // Until the field array mounts, react-hook-form reads its rows from the default values.
        const parts = name.split('.');
        const parent = parts.slice(0, -1).reduce((o: any, k) => o?.[k], (control as any)._defaultValues);
        if (parent) {
            parent[parts[parts.length - 1]] = rows;
        }
    }
    const {fields, append, remove} = useFieldArray({
        control,
        name,
        // Rules see the plain list of values, not the {value} rows used internally.
        rules: {
            validate: toValidate(rules?.map(
                (rule) => (rows: {value: unknown}[], values: unknown) => rule(rows.map((row) => row.value), values),
            )),
        },
    } as any);
    const addButton = useRef<HTMLDivElement>(null);
    const {hidden, collapsed, disabled} = useFieldConditions(name, {hideIf, disableIf, advanced});
    const required = useIsRequired(rules, !hidden && !disabled);
    const message = getFieldState(name, formState).error?.message ?? (formState.errors as any)?.[name]?.root?.message;
    // Editing a row only revalidates that row's field, so the list's own error (e.g. at least one item) is rechecked here.
    const rows = useWatch({control, name});
    useEffect(() => {
        if (message && !hidden) {
            trigger(name);
        }
    }, [rows]);
    if (hidden) {
        return null;
    }
    const addRow = () => {
        clearErrors(name);
        append({value: emptyItem});
    };
    // Removing the focused row's button would leave the focus nowhere, so it moves to the add button.
    const removeRow = (index: number) => {
        clearErrors(name);
        remove(index);
        addButton.current?.querySelector('button')?.focus();
    };
    const errorId = `field_${name.replace(/\W+/g, '_')}_error`;

    return (
        <fieldset
            hidden={collapsed}
            tabIndex={message ? -1 : undefined}
            data-invalid={message ? true : undefined}
            aria-describedby={message ? errorId : undefined}
        >
            <legend className="fs-6 mb-2">{label}{required && requiredIndicator}</legend>
            <div className="d-flex flex-column gap-2">
                {fields.map((field, index) => (
                    <div key={field.id} className="d-flex gap-2 align-items-start">
                        <div className="flex-grow-1">
                            <RowContext.Provider value={`${name}.${index}.value`}>{children}</RowContext.Provider>
                        </div>
                        <Button
                            type="button"
                            onClick={() => removeRow(index)}
                            disabled={disabled || fields.length <= minRows}
                            aria-label={`${removeLabel ?? defaults.remove} ${label} ${index + 1}`}
                            label={removeLabel ?? ''}
                            size="lg"
                            startIcon={removeLabel ? undefined : <i aria-hidden="true" className="fa-solid fa-xmark"/>}
                            variant="outline-secondary"
                        />
                    </div>
                ))}
            </div>
            {message && <div id={errorId} role="alert" className="text-danger small mt-1">{message}</div>}
            <div ref={addButton}>
                <Button
                    type="button"
                    className="mt-2"
                    onClick={addRow}
                    label={addLabel ?? defaults.add}
                    startIcon={addLabel ? undefined : <i aria-hidden="true" className="fa-solid fa-plus"/>}
                    disabled={disabled}
                />
            </div>
        </fieldset>
    );
}

const toRows = <T,>(items: T[]): {value: T}[] => items.map((value) => ({value}));
