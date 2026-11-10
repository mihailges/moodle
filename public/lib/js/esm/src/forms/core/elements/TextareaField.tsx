import {Textarea} from '@moodlehq/design-system';
import {useFormField, type FieldProps} from '../formField';

export function TextareaField({name, label, rules, hideIf, disableIf, defaultValue, defaultIf, advanced, ...rest}: FieldProps) {
    const f = useFormField(name, {rules, hideIf, disableIf, defaultValue, defaultIf, advanced});
    return f.hidden ? null : <Textarea label={label} required={f.required} {...rest} {...f.registration} />;
}
