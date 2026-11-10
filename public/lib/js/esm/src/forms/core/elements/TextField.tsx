import {Input} from '@moodlehq/design-system';
import {useFormField, type FieldProps} from '../formField';

export function TextField({name, label, rules, hideIf, disableIf, advanced, ...rest}: FieldProps) {
    const f = useFormField(name, {rules, hideIf, disableIf, advanced});
    return f.hidden ? null : (
        <Input
            label={label}
            required={f.required}
            {...rest}
            {...f.registration}
            invalid={f.invalid}
            invalidFeedback={f.message}
        />
    );
}
