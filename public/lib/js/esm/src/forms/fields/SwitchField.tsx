import {Switch} from '@moodlehq/design-system';
import {useDefinedField} from '../useDefinedField';
import type {FieldProps} from './types';

export function SwitchField({name, label, rules, hideIf, disableIf, ...rest}: FieldProps) {
    const f = useDefinedField(name, {rules, hideIf, disableIf});
    return f.hidden ? null : <Switch label={label} {...rest} {...f.registration} />;
}
