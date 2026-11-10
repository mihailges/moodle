import {useFormField, type FieldProps} from '../../core/formField';

type RatingFieldProps = Omit<FieldProps, 'hideLabel'> & {
    name: string;
    max?: number;
};

// A custom field: a 1..max rating made of native radios, so the form registers them like any other input.
export function RatingField({name, label, max = 5, rules, hideIf, disableIf, defaultIf, advanced}: RatingFieldProps) {
    const f = useFormField(name, {rules, hideIf, disableIf, defaultIf, advanced});
    if (f.hidden) {
        return null;
    }
    return (
        <fieldset disabled={f.disabled}>
            <legend className="fs-6 mb-2">{label}{f.requiredIndicator}</legend>
            <div className="btn-group" role="group" aria-label={label}>
                {Array.from({length: max}, (_, i) => String(i + 1)).map((value) => (
                    <label key={value} className="btn btn-outline-primary mb-0">
                        <input type="radio" className="me-1" value={value} {...f.registration} {...f.inputProps} />
                        {value}
                    </label>
                ))}
            </div>
            {f.invalid && <div {...f.errorProps} className="text-danger small mt-1">{f.message}</div>}
        </fieldset>
    );
}
