import {useWatch, useFormContext} from 'react-hook-form';
import {useFormField, type FieldProps} from '../../core/formField';

type CountedTextareaFieldProps = Omit<FieldProps, 'hideLabel' | 'name'> & {
    name: string;
    maxLength: number;
};

// A custom field: a textarea that counts the characters left. The limit itself is enforced by the field's rules.
export function CountedTextareaField({
    name, label, maxLength, rules, hideIf, disableIf, defaultIf, advanced,
}: CountedTextareaFieldProps) {
    const f = useFormField(name, {rules, hideIf, disableIf, defaultIf, advanced});
    const {control} = useFormContext();
    const value = useWatch({control, name}) ?? '';
    if (f.hidden) {
        return null;
    }
    const left = maxLength - String(value).length;
    return (
        <div>
            <label className="form-label" htmlFor={`field_${name}`}>{label}{f.requiredIndicator}</label>
            <textarea id={`field_${name}`} className="form-control" rows={3} {...f.registration} {...f.inputProps} />
            <div className={`small mt-1 ${left < 0 ? 'text-danger' : 'text-muted'}`}>{left} characters left</div>
            {f.invalid && <div {...f.errorProps} className="text-danger small mt-1">{f.message}</div>}
        </div>
    );
}
