import {useEffect} from 'react';
import {useFormContext} from 'react-hook-form';
import {Checkbox} from '@moodlehq/design-system';
import {useFormField, type FieldProps} from '../formField';

type CheckboxFieldProps = Omit<FieldProps, 'name'> & {
    name: string;
    supportingText?: string;
    // With a value, checkboxes sharing a name make a list of the checked values, instead of a true/false each.
    value?: string;
};

// A checkbox holding true or false. With `value`, several checkboxes sharing a name hold the list of checked values.
export function CheckboxField({
    name, label, supportingText, rules, hideIf, disableIf, defaultIf, advanced, id, value,
}: CheckboxFieldProps) {
    const mergeIntoList = (wanted: unknown, current: unknown) => {
        const list = Array.isArray(current) ? current.filter((v) => v !== value) : [];
        return wanted ? [...list, value] : list;
    };
    const f = useFormField(name, {rules, hideIf, disableIf, defaultIf, advanced}, value === undefined ? undefined : mergeIntoList);
    // Checkboxes with a value make a list. Untouched and with no default, react-hook-form would hold false for it,
    // which no list rule can count, so it starts as an empty list.
    const {getValues, setValue} = useFormContext();
    useEffect(() => {
        if (value !== undefined && !Array.isArray(getValues(name))) {
            setValue(name, []);
        }
    }, []);
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
