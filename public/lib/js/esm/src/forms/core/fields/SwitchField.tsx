import {Switch} from '@moodlehq/design-system';
import {useMoodleFormField} from '../useMoodleFormField';
import type {FieldProps} from './types';

export function SwitchField({name, label, rules, hideIf, disableIf, advanced, ...rest}: FieldProps) {
    const f = useMoodleFormField(name, {rules, hideIf, disableIf, advanced});
    return f.hidden ? null : <Switch label={label} {...rest} {...f.registration} />;
}
