import {Textarea} from '@moodlehq/design-system';
import {useFormField, type FieldProps} from '../formField';

export function TextareaField({name, label, rules, hideIf, disableIf, defaultIf, advanced, ...rest}: FieldProps) {
    const f = useFormField(name, {rules, hideIf, disableIf, defaultIf, advanced});
    return f.hidden ? null : <Textarea label={label} required={f.required} {...rest} {...f.registration} />;
}
