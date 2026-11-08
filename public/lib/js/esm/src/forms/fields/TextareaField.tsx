import {Textarea} from '@moodlehq/design-system';
import {useMoodleFormField} from '../useMoodleFormField';
import type {FieldProps} from './types';

export function TextareaField({name, label, rules, hideIf, disableIf, ...rest}: FieldProps) {
    const f = useMoodleFormField(name, {rules, hideIf, disableIf});
    return f.hidden ? null : <Textarea label={label} {...rest} {...f.registration} />;
}
