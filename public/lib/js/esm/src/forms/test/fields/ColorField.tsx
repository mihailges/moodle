import {useWatch, useFormContext} from 'react-hook-form';
import {useFormField, type FieldProps} from '../../core/formField';

type ColorFieldProps = Omit<FieldProps, 'hideLabel' | 'name'> & {name: string};

// A custom field: a native colour picker that also shows the chosen value.
export function ColorField({name, label, rules, hideIf, disableIf, defaultValue, defaultIf, advanced}: ColorFieldProps) {
    const f = useFormField(name, {rules, hideIf, disableIf, defaultValue, defaultIf, advanced});
    const {control} = useFormContext();
    const value = useWatch({control, name});
    if (f.hidden) {
        return null;
    }
    return (
        <div>
            <label className="form-label d-block" htmlFor={`field_${name}`}>{label}{f.requiredIndicator}</label>
            <div className="d-flex align-items-center gap-2">
                <input
                    id={`field_${name}`}
                    type="color"
                    className="form-control form-control-color"
                    {...f.registration}
                    {...f.inputProps}
                />
                <code>{value}</code>
            </div>
            {f.invalid && <div {...f.errorProps} className="text-danger small mt-1">{f.message}</div>}
        </div>
    );
}
