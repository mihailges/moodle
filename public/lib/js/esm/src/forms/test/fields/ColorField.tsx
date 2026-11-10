import {useWatch, useFormContext} from 'react-hook-form';
import {useMoodleFormField} from '../../core/useMoodleFormField';
import type {FieldProps} from '../../core/fields/types';

type ColorFieldProps = Omit<FieldProps, 'hideLabel' | 'name'> & {name: string};

// A custom field: a native colour picker that also shows the chosen value.
export function ColorField({name, label, rules, hideIf, disableIf, advanced}: ColorFieldProps) {
    const f = useMoodleFormField(name, {rules, hideIf, disableIf, advanced});
    const {control} = useFormContext();
    const value = useWatch({control, name});
    if (f.hidden) {
        return null;
    }
    return (
        <div>
            <label className="form-label d-block" htmlFor={`field_${name}`}>{label}{f.requiredIndicator}</label>
            <div className="d-flex align-items-center gap-2">
                <input id={`field_${name}`} type="color" className="form-control form-control-color" {...f.registration} />
                <code>{value}</code>
            </div>
            {f.invalid && <div role="alert" className="text-danger small mt-1">{f.message}</div>}
        </div>
    );
}
