var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { createElement, useContext, useEffect, useState } from "react";
import { useFormContext, useWatch, get } from "react-hook-form";
import { FormStateContext, GroupContext, FieldRegistryContext, RowContext, ContainerContext, AdvancedContext } from "./context";
function useFieldConditions(name, { hideIf, disableIf, advanced } = {}) {
  const { serverDisabled } = useContext(FormStateContext);
  const { disabled: groupDisabled } = useContext(GroupContext);
  const { control, formState: { errors } } = useFormContext();
  const registry = useContext(FieldRegistryContext);
  const container = useContext(ContainerContext);
  const { show, register: registerAdvanced } = useContext(AdvancedContext);
  useEffect(() => {
    registry.add(name);
    const leave = container?.add(name);
    return () => {
      registry.remove(name);
      leave?.();
    };
  }, [registry, container, name]);
  useEffect(() => advanced ? registerAdvanced() : void 0, [advanced, registerAdvanced]);
  const hasError = Boolean(get(errors, name));
  const [revealed, setRevealed] = useState(false);
  useEffect(() => {
    if (hasError) {
      setRevealed(true);
    }
  }, [hasError]);
  useEffect(() => setRevealed(false), [show]);
  const values = useWatch({ control, disabled: !hideIf && !disableIf });
  return {
    hidden: hideIf?.(values) ?? false,
    // Not shown, but still part of the form: fields hide it with CSS or by rendering nothing.
    collapsed: Boolean(advanced) && !show && !hasError && !revealed,
    disabled: (disableIf?.(values) ?? false) || groupDisabled || serverDisabled.includes(name)
  };
}
__name(useFieldConditions, "useFieldConditions");
const toValidate = /* @__PURE__ */ __name((rules) => rules?.length ? Object.fromEntries(rules.map((rule, i) => [i, rule])) : void 0, "toValidate");
const requiredIndicator = createElement(
  "span",
  { "aria-hidden": true, style: { color: "var(--mds-text-danger)", marginInlineStart: "var(--mds-spacing-xxs)" } },
  "*"
);
const mandatory = /* @__PURE__ */ new WeakMap();
const markMandatory = /* @__PURE__ */ __name((rule, when) => {
  mandatory.set(rule, when);
  return rule;
}, "markMandatory");
const mandatoryWhen = /* @__PURE__ */ __name((rules) => (rules ?? []).map((rule) => mandatory.get(rule)), "mandatoryWhen");
function useIsRequired(rules, active = true) {
  const { control } = useFormContext();
  const when = mandatoryWhen(rules);
  const values = useWatch({ control, disabled: !when.some((w) => typeof w === "function") });
  return active && when.some((w) => typeof w === "function" ? w(values) : w);
}
__name(useIsRequired, "useIsRequired");
function useMoodleFormField(fieldName, { rules, hideIf, disableIf, advanced } = {}) {
  const rowName = useContext(RowContext);
  const name = fieldName ?? rowName;
  if (name === null || name === void 0) {
    throw new Error("A field needs a name, unless it is the field of a RepeaterField.");
  }
  const { register, formState: { errors } } = useFormContext();
  const { hidden, collapsed, disabled } = useFieldConditions(name, { hideIf, disableIf, advanced });
  const group = useContext(GroupContext);
  const error = group.name === name ? void 0 : get(errors, name);
  const validate = toValidate(rules) ?? (group.name === name ? group.validate : void 0);
  const registration = hidden ? void 0 : register(name, {
    validate,
    disabled
  });
  const required = useIsRequired(rules, !hidden && !disabled);
  useEffect(() => {
    if (collapsed && !hidden) {
      register(name, { validate, disabled });
    }
  });
  return {
    hidden: hidden || collapsed,
    disabled,
    // Whether the field is mandatory right now: fields show it next to the label.
    required,
    // For fields that draw their own label: place it next to the label.
    requiredIndicator: required ? requiredIndicator : null,
    registration,
    invalid: Boolean(error),
    message: error?.message
  };
}
__name(useMoodleFormField, "useMoodleFormField");
export {
  markMandatory,
  requiredIndicator,
  toValidate,
  useFieldConditions,
  useIsRequired,
  useMoodleFormField
};
//# sourceMappingURL=useMoodleFormField.dev.js.map
