import {Textarea} from '@moodlehq/design-system';
import {useDefinedField} from '../useDefinedField';
import type {FieldProps} from './types';

export function TextareaField({name, label, rules, hideIf, disableIf, ...rest}: FieldProps) {
    const f = useDefinedField(name, {rules, hideIf, disableIf});
    return f.hidden ? null : <Textarea label={label} {...rest} {...f.registration} />;
}
