import {useContext, useEffect} from 'react';
import {useFormContext, useWatch, get} from 'react-hook-form';
import type {Condition} from './conditions';
import {all} from './rules/all';
import type {Rule} from './rules/types';
import {FormStateContext, GroupContext, FieldRegistryContext, RowContext} from './context';

/**
 * Behaviour every field accepts as props.
 */
export type FieldBehaviour = {
    rules?: Rule[];
    // Hidden fields are not rendered, not validated and not submitted.
    hideIf?: Condition;
    // Disabled fields are not editable and not validated.
    disableIf?: Condition;
};

/**
 * Resolves whether a field is hidden or disabled from its conditions and the server's disabled list.
 * For fields that manage their own registration (e.g. repeaters); simple fields use useMoodleFormField.
 */
export function useFieldConditions(name: string, {hideIf, disableIf}: Pick<FieldBehaviour, 'hideIf' | 'disableIf'> = {}) {
    const {serverDisabled} = useContext(FormStateContext);
    const {disabled: groupDisabled} = useContext(GroupContext);
    const {control} = useFormContext();
    const registry = useContext(FieldRegistryContext);
    useEffect(() => {
        registry.add(name);
        return () => registry.remove(name);
    }, [registry, name]);
    const values = useWatch({control, disabled: !hideIf && !disableIf});

    return {
        hidden: hideIf?.(values) ?? false,
        disabled: (disableIf?.(values) ?? false) || groupDisabled || serverDisabled.includes(name),
    };
}

/**
 * The building block for form fields. Applies rules, conditions and error state from the field's props,
 * so a field (core or plugin) only has to render its element with the result.
 */
export function useMoodleFormField(fieldName: string | undefined, {rules, hideIf, disableIf}: FieldBehaviour = {}) {
    // Inside a repeater the name comes from the row.
    const rowName = useContext(RowContext);
    const name = fieldName ?? rowName;
    if (name === null || name === undefined) {
        throw new Error('A field needs a name, unless it is the field of a RepeaterField.');
    }
    const {register, formState: {errors}} = useFormContext();
    const {hidden, disabled} = useFieldConditions(name, {hideIf, disableIf});
    // The group shows the error of the name it shares with its fields, so the fields don't repeat it.
    const group = useContext(GroupContext);
    const error = group.name === name ? undefined : get(errors, name);

    // A hidden field is not registered, so it is not validated.
    // Registering a name again drops rules not repeated, so a field sharing its group's name repeats the group's.
    const validate = rules?.length ? all(...rules) : (group.name === name ? group.validate : undefined);
    const registration = hidden ? undefined : register(name, {
        validate,
        disabled,
    });

    return {
        hidden,
        disabled,
        registration,
        invalid: Boolean(error),
        message: error?.message as string | undefined,
    };
}
