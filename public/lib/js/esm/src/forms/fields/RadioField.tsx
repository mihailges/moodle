import {Radio} from '@moodlehq/design-system';
import {useMoodleFormField} from '../useMoodleFormField';
import type {FieldProps} from './types';

type RadioFieldProps = Omit<FieldProps, 'hideLabel' | 'name'> & {
    name: string;
    // The value the form holds when this radio is selected. Radios sharing a name form one choice.
    value: string;
    supportingText?: string;
};

// One radio button; render several with the same name, usually inside a GroupField for the shared label.
export function RadioField({name, value, label, supportingText, rules, hideIf, disableIf, id}: RadioFieldProps) {
    const f = useMoodleFormField(name, {rules, hideIf, disableIf});
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
