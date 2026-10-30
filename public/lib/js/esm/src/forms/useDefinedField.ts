import {useContext, useMemo} from 'react';
import {useFormContext, useWatch, set, get} from 'react-hook-form';
import type {Condition} from './conditions';
import {all} from './rules/all';
import type {Rule} from './rules/types';
import {FormStateContext} from './context';

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
 * For fields that manage their own registration (e.g. repeaters); simple fields use useDefinedField.
 */
export function useFieldConditions(name: string, {hideIf, disableIf}: Pick<FieldBehaviour, 'hideIf' | 'disableIf'> = {}) {
    const {serverDisabled} = useContext(FormStateContext);
    const {control} = useFormContext();

    // Watch only the fields the conditions read.
    const deps = [...(hideIf?.deps ?? []), ...(disableIf?.deps ?? [])];
    const depsKey = deps.join('|');
    const stableDeps = useMemo(() => deps, [depsKey]);
    const watched = useWatch({control, name: stableDeps, disabled: stableDeps.length === 0});
    const values = useMemo(() => {
        const result = {};
        stableDeps.forEach((dep, i) => set(result, dep, watched?.[i]));
        return result;
    }, [stableDeps, watched]);

    return {
        hidden: hideIf?.(values) ?? false,
        disabled: (disableIf?.(values) ?? false) || serverDisabled.includes(name),
    };
}

/**
 * The building block for form fields. Applies rules, conditions and error state from the field's props,
 * so a field (core or plugin) only has to render its element with the result.
 */
export function useDefinedField(name: string, {rules, hideIf, disableIf}: FieldBehaviour = {}) {
    const {register, formState: {errors}} = useFormContext();
    const {hidden, disabled} = useFieldConditions(name, {hideIf, disableIf});
    const error = get(errors, name);

    // A hidden field is not registered, so it is neither validated nor submitted.
    const registration = hidden ? undefined : register(name, {
        validate: rules?.length ? all(...rules) : undefined,
        disabled,
    });

    return {hidden, disabled, registration, invalid: Boolean(error), message: error?.message as string | undefined};
}
