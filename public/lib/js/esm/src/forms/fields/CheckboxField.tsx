import {Checkbox} from '@moodlehq/design-system';
import {useMoodleFormField} from '../useMoodleFormField';
import type {FieldProps} from './types';

type CheckboxFieldProps = Omit<FieldProps, 'name'> & {
    name: string;
    supportingText?: string;
    // With a value, checkboxes sharing a name make a list of the checked values, instead of a true/false each.
    value?: string;
};

// A checkbox holding true or false. With `value`, several checkboxes sharing a name hold the list of checked values.
export function CheckboxField({name, label, supportingText, rules, hideIf, disableIf, id, value}: CheckboxFieldProps) {
    const f = useMoodleFormField(name, {rules, hideIf, disableIf});
    if (f.hidden) {
        return null;
    }
    return (
        <Checkbox
            id={id ?? `field_${name.replace(/\W+/g, '_')}${value ? `_${value.replace(/\W+/g, '_')}` : ''}`}
            value={value}
            label={label}
            supportingText={supportingText}
            invalid={f.invalid}
            invalidFeedback={f.message}
            {...f.registration}
        />
    );
}
