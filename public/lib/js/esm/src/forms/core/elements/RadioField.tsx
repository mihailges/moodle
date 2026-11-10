import {Radio} from '@moodlehq/design-system';
import {useFormField, type FieldProps} from '../formField';

type RadioFieldProps = Omit<FieldProps, 'hideLabel' | 'name'> & {
    name: string;
    // The value the form holds when this radio is selected. Radios sharing a name form one choice.
    value: string;
    supportingText?: string;
};

// One radio button; render several with the same name, usually inside a GroupField for the shared label.
export function RadioField({
    name, value, label, supportingText, rules, hideIf, disableIf, defaultValue, defaultIf, advanced, id,
}: RadioFieldProps) {
    const f = useFormField(name, {rules, hideIf, disableIf, defaultValue, defaultIf, advanced});
    if (f.hidden) {
        return null;
    }
    return (
        <div>
            <Radio
                id={id ?? `field_${name.replace(/\W+/g, '_')}_${value.replace(/\W+/g, '_')}`}
                label={label}
                value={value}
                {...f.registration}
            />
            {supportingText && <div className="text-muted small ms-4">{supportingText}</div>}
        </div>
    );
}
