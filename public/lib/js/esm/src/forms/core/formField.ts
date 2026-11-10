import {createElement, useContext, useEffect, useRef, useState} from 'react';
import {useFormContext, useWatch, get} from 'react-hook-form';
import {getString} from '@moodle/lms/core/stringUtils';
import type {Condition} from './formCondition';
import type {Rule, Validate} from './formRule';
import {FormStateContext, GroupContext, FieldRegistryContext, RowContext, ContainerContext, AdvancedContext} from './formContext';

/**
 * Behaviour every field accepts as props.
 */
export type FieldBehaviour = {
    rules?: Rule[];
    // Hidden fields are not rendered, not validated and not submitted.
    hideIf?: Condition;
    // Disabled fields are not editable and not validated.
    disableIf?: Condition;
    // The value this field should take for the current form values, or undefined for no opinion. Give a list to
    // cover several cases: the first one with an answer wins. Whenever that answer changes (e.g. another field
    // changes), the field is set to it; the user can still change it afterwards.
    // It does not run when the form first renders, so saved or default values are kept. For a checkbox with a `value`
    // it answers whether that checkbox is checked (true or false).
    defaultIf?: DefaultRule | DefaultRule[];
    // Only shown after the form's "Show more" button, like an mform advanced element. Unlike hideIf, a collapsed
    // field is still validated and submitted, and it shows itself when it has an error.
    advanced?: boolean;
};

/**
 * Props shared by every form field. Custom fields should accept these and call useFormField.
 */
// `name` is only left out for the field inside a RepeaterField, which names it after the row.
// Answers the value a field should take for the form values, or undefined for no opinion.
export type DefaultRule = (values: any) => unknown;

export type FieldProps = FieldBehaviour & {name?: string; label: string; hideLabel?: boolean; id?: string};

export type Option = {value: string; label: string; description?: string};

/**
 * Resolves whether a field is hidden or disabled from its conditions and the server's disabled list.
 * For fields that manage their own registration (e.g. repeaters); simple fields use useFormField.
 */
export function useFieldConditions(
    name: string,
    {hideIf, disableIf, advanced}: Pick<FieldBehaviour, 'hideIf' | 'disableIf' | 'advanced'> = {},
) {
    const {serverDisabled} = useContext(FormStateContext);
    const {disabled: groupDisabled} = useContext(GroupContext);
    const {control, clearErrors, formState: {errors}} = useFormContext();
    const registry = useContext(FieldRegistryContext);
    const container = useContext(ContainerContext);
    const {show, register: registerAdvanced} = useContext(AdvancedContext);
    useEffect(() => {
        registry.add(name);
        const leave = container?.add(name);
        return () => {
            registry.remove(name);
            leave?.();
        };
    }, [registry, container, name]);
    useEffect(() => (advanced ? registerAdvanced() : undefined), [advanced, registerAdvanced]);

    // An advanced field with an error shows itself, and stays shown while the user corrects it.
    const hasError = Boolean(get(errors, name));
    const [revealed, setRevealed] = useState(false);
    useEffect(() => {
        if (hasError) {
            setRevealed(true);
        }
    }, [hasError]);
    useEffect(() => setRevealed(false), [show]);

    const values = useWatch({control, disabled: !hideIf && !disableIf});

    const isDisabled = (disableIf?.(values) ?? false) || groupDisabled || serverDisabled.includes(name);
    // A disabled field is not validated or submitted, so a message it showed earlier no longer applies.
    useEffect(() => {
        if (isDisabled) {
            clearErrors(name);
        }
    }, [isDisabled, clearErrors, name]);

    return {
        hidden: hideIf?.(values) ?? false,
        // Not shown, but still part of the form: fields hide it with CSS or by rendering nothing.
        collapsed: Boolean(advanced) && !show && !hasError && !revealed,
        disabled: isDisabled,
    };
}

/**
 * Sets a field to what its `defaultIf` answers each time that answer changes. `merge` turns the answer into the
 * stored value, for fields that hold only part of it (a checkbox in a list).
 */
export function useDefaultIf(
    name: string,
    defaultIf: FieldBehaviour['defaultIf'],
    merge: (wanted: unknown, current: unknown) => unknown = (wanted) => wanted,
) {
    const {control, setValue, getValues} = useFormContext();
    const values = useWatch({control, disabled: !defaultIf});
    const rules = defaultIf === undefined ? [] : [defaultIf].flat();
    let wanted: unknown;
    for (const rule of rules) {
        wanted = rule(values);
        if (wanted !== undefined) {
            break;
        }
    }
    const key = JSON.stringify(wanted ?? null);
    const previous = useRef<string | null>(null);
    useEffect(() => {
        if (!defaultIf) {
            return;
        }
        const first = previous.current === null;
        const changed = previous.current !== key;
        previous.current = key;
        if (!first && changed && wanted !== undefined) {
            setValue(name, merge(wanted, getValues(name)) as any, {shouldDirty: true});
        }
    }, [key]);
}

// Turns a field's rules into react-hook-form's `validate`, or nothing when there are none.
export const toValidate = (rules?: Rule[]): Validate | undefined =>
    (rules?.length ? Object.fromEntries(rules.map((rule, i) => [i, rule])) : undefined);

// The asterisk is only visual, so screen readers get the word "Required" in its place.
function RequiredMark() {
    const [text, setText] = useState('Required');
    useEffect(() => {
        getString('required').then(setText).catch(() => undefined);
    }, []);
    return createElement(
        'span',
        {style: {color: 'var(--mds-text-danger)', marginInlineStart: 'var(--mds-spacing-xxs)'}},
        createElement('span', {'aria-hidden': true}, '*'),
        createElement('span', {className: 'visually-hidden'}, text),
    );
}

// The red asterisk of a mandatory field, for fields that draw their own label. It matches the design system's,
// which fields built on its components get by passing them `required`.
export const requiredIndicator = createElement(RequiredMark);

type Mandatory = boolean | ((values: any) => boolean);

// Kept here rather than on the rules, so a Rule stays a plain validation function.
const mandatory = new WeakMap<Rule, Mandatory>();

/**
 * Declares that a rule makes its field mandatory, so the field can show it: always (true), or depending on the
 * form values (a function, as requiredIf does). Returns the rule.
 */
export const markMandatory = <R extends Rule>(rule: R, when: Mandatory): R => {
    mandatory.set(rule, when);
    return rule;
};

const mandatoryWhen = (rules: Rule[] | undefined) => (rules ?? []).map((rule) => mandatory.get(rule));

/**
 * Whether the rules make a field mandatory right now (e.g. required, or requiredIf whose condition is met),
 * so the field can show it next to its label. Fields that are disabled or hidden are not mandatory.
 */
export function useIsRequired(rules: Rule[] | undefined, active = true): boolean {
    const {control} = useFormContext();
    const when = mandatoryWhen(rules);
    const values = useWatch({control, disabled: !when.some((w) => typeof w === 'function')});
    return active && when.some((w) => (typeof w === 'function' ? w(values) : w));
}

/**
 * The building block for form fields. Applies rules, conditions and error state from the field's props,
 * so a field (core or plugin) only has to render its element with the result.
 */
export function useFormField(
    fieldName: string | undefined,
    {rules, hideIf, disableIf, defaultIf, advanced}: FieldBehaviour = {},
    mergeDefault?: (wanted: unknown, current: unknown) => unknown,
) {
    // Inside a repeater the name comes from the row.
    const rowName = useContext(RowContext);
    const name = fieldName ?? rowName;
    if (name === null || name === undefined) {
        throw new Error('A field needs a name, unless it is the field of a RepeaterField.');
    }
    const {register, formState: {errors}} = useFormContext();
    const {hidden, collapsed, disabled} = useFieldConditions(name, {hideIf, disableIf, advanced});
    useDefaultIf(name, defaultIf, mergeDefault);
    // The group shows the error of the name it shares with its fields, so the fields don't repeat it.
    const group = useContext(GroupContext);
    const error = group.name === name ? undefined : get(errors, name);

    // A hidden field is not registered, so it is not validated.
    // Registering a name again drops rules not repeated, so a field sharing its group's name repeats the group's.
    const validate = toValidate(rules) ?? (group.name === name ? group.validate : undefined);
    const registration = hidden ? undefined : register(name, {
        validate,
        disabled,
    });

    const required = useIsRequired(rules, !hidden && !disabled);

    // A collapsed field renders nothing but keeps its registration, so it is still validated and submitted.
    // Its input is detached from the form while nothing renders it, so register it again once that has happened.
    useEffect(() => {
        if (collapsed && !hidden) {
            register(name, {validate, disabled});
        }
    });

    const errorId = `field_${name.replace(/\W+/g, '_')}_error`;
    return {
        hidden: hidden || collapsed,
        disabled,
        // Whether the field is mandatory right now: fields show it next to the label.
        required,
        // For fields that draw their own label: place it next to the label.
        requiredIndicator: required ? requiredIndicator : null,
        registration,
        invalid: Boolean(error),
        message: error?.message as string | undefined,
        // For fields that draw their own input and error: put `inputProps` on the input and `errorProps` on the
        // message, so assistive technology knows the input is invalid and reads the message with it.
        inputProps: error ? {'aria-invalid': true as const, 'aria-describedby': errorId} : {},
        errorProps: {id: errorId, role: 'alert' as const},
    };
}
