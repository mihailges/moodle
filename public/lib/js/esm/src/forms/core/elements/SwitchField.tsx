import {Switch} from '@moodlehq/design-system';
import {useFormField, type FieldProps} from '../formField';

export function SwitchField({name, label, rules, hideIf, disableIf, defaultIf, advanced, ...rest}: FieldProps) {
    const f = useFormField(name, {rules, hideIf, disableIf, defaultIf, advanced});
    return f.hidden ? null : <Switch label={label} {...rest} {...f.registration} />;
}
