import {Textarea} from '@moodlehq/design-system';
import {useMoodleFormField} from '../useMoodleFormField';
import type {FieldProps} from './types';

export function TextareaField({name, label, rules, hideIf, disableIf, advanced, ...rest}: FieldProps) {
    const f = useMoodleFormField(name, {rules, hideIf, disableIf, advanced});
    return f.hidden ? null : <Textarea label={label} required={f.required} {...rest} {...f.registration} />;
}
