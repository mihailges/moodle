import type {FieldBehaviour} from '../useDefinedField';

/**
 * Props shared by every form field. Custom fields should accept these and call useDefinedField.
 */
export type FieldProps = FieldBehaviour & {name: string; label: string; hideLabel?: boolean; id?: string};

export type Option = {value: string; label: string};
