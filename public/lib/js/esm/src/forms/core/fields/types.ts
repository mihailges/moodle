import type {FieldBehaviour} from '../useMoodleFormField';

/**
 * Props shared by every form field. Custom fields should accept these and call useMoodleFormField.
 */
// `name` is only left out for the field inside a RepeaterField, which names it after the row.
export type FieldProps = FieldBehaviour & {name?: string; label: string; hideLabel?: boolean; id?: string};

export type Option = {value: string; label: string; description?: string};
