import {useRef, type ReactNode} from 'react';
import {useController, useFormContext} from 'react-hook-form';
import {Button} from '@moodlehq/design-system';
import {useFieldConditions, type FieldBehaviour} from '../useDefinedField';
import {all} from '../rules/all';

type RepeaterFieldProps = FieldBehaviour & {
    name: string;
    label: string;
    addLabel: string;
    // The value of a new row, e.g. ''. Rows are plain values, stored as name = [row0, row1, …].
    emptyItem?: unknown;
    minRows?: number;
    // Render the field of one row, using `${name}.${index}` as its name.
    children: (index: number) => ReactNode;
};

let nextKey = 0;

export function RepeaterField({
    name, label, addLabel, emptyItem = '', minRows = 1, rules, hideIf, disableIf, children,
}: RepeaterFieldProps) {
    const {clearErrors} = useFormContext();
    // Owns the list as a whole: its value, list-level rules (e.g. minItems) and their error.
    const {field, fieldState} = useController({
        name,
        defaultValue: [],
        rules: {validate: rules?.length ? all(...rules) : undefined},
    });
    const {hidden, disabled} = useFieldConditions(name, {hideIf, disableIf});
    const keys = useRef<number[]>([]);
    if (hidden) {
        return null;
    }

    const rows: unknown[] = Array.isArray(field.value) ? field.value : [];

    // Stable keys, so removing a row keeps the other rows' inputs (and their values) with their data.
    if (keys.current.length !== rows.length) {
        keys.current = rows.map(() => nextKey++);
    }
    const update = (next: unknown[]) => {
        clearErrors(name);
        field.onChange(next);
    };
    const addRow = () => {
        keys.current = [...keys.current, nextKey++];
        update([...rows, emptyItem]);
    };
    const removeRow = (index: number) => {
        keys.current = keys.current.filter((_, i) => i !== index);
        update(rows.filter((_, i) => i !== index));
    };
    const message = fieldState.error?.message;

    return (
        <fieldset>
            <legend className="fs-6 mb-2">{label}</legend>
            <div className="d-flex flex-column gap-2">
                {rows.map((_, index) => (
                    <div key={keys.current[index]} className="d-flex gap-2 align-items-start">
                        {children(index)}
                        <Button
                            type="button"
                            onClick={() => removeRow(index)}
                            disabled={disabled || rows.length <= minRows}
                            aria-label={`Remove ${label} ${index + 1}`}
                            label="Remove"
                            variant="secondary"
                        />
                    </div>
                ))}
            </div>
            {message && <div role="alert" className="text-danger small mt-1">{message}</div>}
            <Button type="button" className="mt-2" onClick={addRow} label={addLabel} disabled={disabled} />
        </fieldset>
    );
}
