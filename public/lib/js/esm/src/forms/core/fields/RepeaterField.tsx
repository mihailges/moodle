import {useContext, type ReactElement} from 'react';
import {useFieldArray, useFormContext} from 'react-hook-form';
import {Button} from '@moodlehq/design-system';
import {useFieldConditions, useIsRequired, toValidate, requiredIndicator, type FieldBehaviour} from '../formField';
import {FieldRegistryContext, RowContext} from '../formContext';

type RepeaterFieldProps = FieldBehaviour & {
    name: string;
    label: string;
    addLabel: string;
    // The value of a new row, e.g. ''. The form's values stay a plain array; rows are wrapped as {value} internally.
    emptyItem?: unknown;
    minRows?: number;
    removeLabel?: string;
    // The single field repeated for each row, without a name: the row names it. For example
    // <TextField label="URL" rules={[url('Invalid')]} />. Rules on the repeater itself get the list of rows.
    children: ReactElement;
};

export function RepeaterField({
    name, label, addLabel, emptyItem = '', minRows = 1, removeLabel = 'Remove', rules, hideIf, disableIf, advanced, children,
}: RepeaterFieldProps) {
    const {control, clearErrors, getFieldState, formState, getValues, setValue} = useFormContext();
    const registry = useContext(FieldRegistryContext);
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
    const {hidden, collapsed, disabled} = useFieldConditions(name, {hideIf, disableIf, advanced});
    const required = useIsRequired(rules, !hidden && !disabled);
    if (hidden) {
        return null;
    }
    const message = getFieldState(name, formState).error?.message ?? (formState.errors as any)?.[name]?.root?.message;
    const addRow = () => {
        clearErrors(name);
        append({value: emptyItem});
    };
    const removeRow = (index: number) => {
        clearErrors(name);
        remove(index);
    };

    return (
        <fieldset hidden={collapsed}>
            <legend className="fs-6 mb-2">{label}{required && requiredIndicator}</legend>
            <div className="d-flex flex-column gap-2">
                {fields.map((field, index) => (
                    <div key={field.id} className="d-flex gap-2 align-items-center">
                        <RowContext.Provider value={`${name}.${index}.value`}>{children}</RowContext.Provider>
                        <Button
                            type="button"
                            onClick={() => removeRow(index)}
                            disabled={disabled || fields.length <= minRows}
                            aria-label={`${removeLabel} ${label} ${index + 1}`}
                            label=""
                            size="lg"
                            startIcon={<i aria-hidden="true" className="fa-solid fa-xmark"/>}
                            variant="outline-secondary"
                        />
                    </div>
                ))}
            </div>
            {message && <div role="alert" className="text-danger small mt-1">{message}</div>}
            <Button
                type="button"
                className="mt-2"
                onClick={addRow}
                label={addLabel} disabled={disabled} />
        </fieldset>
    );
}

const toRows = <T,>(items: T[]): {value: T}[] => items.map((value) => ({value}));
