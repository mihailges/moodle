import {Input} from '@moodlehq/design-system';
import {useDefinedField} from '../useDefinedField';
import type {FieldProps} from './types';

export function TextField({name, label, rules, hideIf, disableIf, ...rest}: FieldProps) {
    const f = useDefinedField(name, {rules, hideIf, disableIf});
    return f.hidden ? null : (
        <Input label={label} {...rest} {...f.registration} invalid={f.invalid} invalidFeedback={f.message} />
    );
}
