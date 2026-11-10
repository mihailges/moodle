import {Checkbox} from '@moodlehq/design-system';
import {useFormField, type FieldProps} from '../formField';

type CheckboxFieldProps = Omit<FieldProps, 'name'> & {
    name: string;
    supportingText?: string;
    // With a value, checkboxes sharing a name make a list of the checked values, instead of a true/false each.
    value?: string;
};

// A checkbox holding true or false. With `value`, several checkboxes sharing a name hold the list of checked values.
export function CheckboxField({name, label, supportingText, rules, hideIf, disableIf, advanced, id, value}: CheckboxFieldProps) {
    const f = useFormField(name, {rules, hideIf, disableIf, advanced});
    if (f.hidden) {
        return null;
    }
    return (
        <Checkbox
            id={id ?? `field_${name.replace(/\W+/g, '_')}${value ? `_${value.replace(/\W+/g, '_')}` : ''}`}
            value={value}
            label={label}
            required={f.required}
            supportingText={supportingText}
            invalid={f.invalid}
            invalidFeedback={f.message}
            {...f.registration}
        />
    );
}
