import {Checkbox} from '@moodlehq/design-system';
import {useDefinedField} from '../useDefinedField';
import type {FieldProps, Option} from './types';

// Pass null for options while they are still loading.
export function CheckboxGroupField({name, label, options, rules, hideIf, disableIf, id}: FieldProps & {options: Option[] | null}) {
    const f = useDefinedField(name, {rules, hideIf, disableIf});
    const prefix = id ?? `field_${name.replace(/\W+/g, '_')}`;
    if (f.hidden) {
        return null;
    }
    return (
        <fieldset aria-describedby={f.invalid ? `${prefix}_error` : undefined}>
            <legend className="fs-6 mb-2">{label}</legend>
            {options === null ? (
                <p className="text-muted">Loading…</p>
            ) : options.map((option) => (
                <Checkbox
                    key={option.value}
                    id={`${prefix}_${option.value.replace(/\W+/g, '_')}`}
                    label={option.label}
                    value={option.value}
                    {...f.registration}
                />
            ))}
            {f.invalid && <div id={`${prefix}_error`} className="text-danger small mt-1">{f.message}</div>}
        </fieldset>
    );
}
