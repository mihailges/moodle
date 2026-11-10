import {useMoodleFormField} from '../../core/useMoodleFormField';
import type {FieldProps} from '../../core/fields/types';

type RatingFieldProps = Omit<FieldProps, 'hideLabel'> & {
    name: string;
    max?: number;
};

// A custom field: a 1..max rating made of native radios, so the form registers them like any other input.
export function RatingField({name, label, max = 5, rules, hideIf, disableIf, advanced}: RatingFieldProps) {
    const f = useMoodleFormField(name, {rules, hideIf, disableIf, advanced});
    if (f.hidden) {
        return null;
    }
    return (
        <fieldset disabled={f.disabled}>
            <legend className="fs-6 mb-2">{label}{f.requiredIndicator}</legend>
            <div className="btn-group" role="group" aria-label={label}>
                {Array.from({length: max}, (_, i) => String(i + 1)).map((value) => (
                    <label key={value} className="btn btn-outline-primary mb-0">
                        <input type="radio" className="me-1" value={value} {...f.registration} />
                        {value}
                    </label>
                ))}
            </div>
            {f.invalid && <div role="alert" className="text-danger small mt-1">{f.message}</div>}
        </fieldset>
    );
}
