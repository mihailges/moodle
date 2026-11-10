var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { createElement, useContext, useEffect, useRef, useState } from "react";
import { useFormContext, useWatch, get } from "react-hook-form";
import { getString } from "@moodle/lms/core/stringUtils";
import { FormStateContext, GroupContext, FieldRegistryContext, RowContext, ContainerContext, AdvancedContext } from "./formContext";
function useFieldConditions(name, { hideIf, disableIf, advanced } = {}) {
  const { serverDisabled } = useContext(FormStateContext);
  const { disabled: groupDisabled } = useContext(GroupContext);
  const { control, clearErrors, formState: { errors } } = useFormContext();
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
  const isDisabled = (disableIf?.(values) ?? false) || groupDisabled || serverDisabled.includes(name);
  useEffect(() => {
    if (isDisabled) {
      clearErrors(name);
    }
  }, [isDisabled, clearErrors, name]);
  return {
    hidden: hideIf?.(values) ?? false,
    // Not shown, but still part of the form: fields hide it with CSS or by rendering nothing.
    collapsed: Boolean(advanced) && !show && !hasError && !revealed,
    disabled: isDisabled
  };
}
__name(useFieldConditions, "useFieldConditions");
function useDefaultIf(name, defaultIf, merge = (wanted) => wanted) {
  const { control, setValue, getValues } = useFormContext();
  const values = useWatch({ control, disabled: !defaultIf });
  const rules = defaultIf === void 0 ? [] : [defaultIf].flat();
  let wanted;
  for (const rule of rules) {
    wanted = rule(values);
    if (wanted !== void 0) {
      break;
    }
  }
  const key = JSON.stringify(wanted ?? null);
  const previous = useRef(null);
  useEffect(() => {
    if (!defaultIf) {
      return;
    }
    const first = previous.current === null;
    const changed = previous.current !== key;
    previous.current = key;
    if (!first && changed && wanted !== void 0) {
      setValue(name, merge(wanted, getValues(name)), { shouldDirty: true });
    }
  }, [key]);
}
__name(useDefaultIf, "useDefaultIf");
const toValidate = /* @__PURE__ */ __name((rules) => rules?.length ? Object.fromEntries(rules.map((rule, i) => [i, rule])) : void 0, "toValidate");
function RequiredMark() {
  const [text, setText] = useState("Required");
  useEffect(() => {
    getString("required").then(setText).catch(() => void 0);
  }, []);
  return createElement(
    "span",
    { style: { color: "var(--mds-text-danger)", marginInlineStart: "var(--mds-spacing-xxs)" } },
    createElement("span", { "aria-hidden": true }, "*"),
    createElement("span", { className: "visually-hidden" }, text)
  );
}
__name(RequiredMark, "RequiredMark");
const requiredIndicator = createElement(RequiredMark);
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
function useFormField(fieldName, { rules, hideIf, disableIf, defaultValue, defaultIf, advanced } = {}, mergeDefault) {
  const rowName = useContext(RowContext);
  const name = fieldName ?? rowName;
  if (name === null || name === void 0) {
    throw new Error("A field needs a name, unless it is the field of a RepeaterField.");
  }
  const { register, formState: { errors } } = useFormContext();
  const { hidden, collapsed, disabled } = useFieldConditions(name, { hideIf, disableIf, advanced });
  useDefaultIf(name, defaultIf, mergeDefault);
  const group = useContext(GroupContext);
  const error = group.name === name ? void 0 : get(errors, name);
  const validate = toValidate(rules) ?? (group.name === name ? group.validate : void 0);
  const registration = hidden ? void 0 : register(name, {
    validate,
    disabled,
    // Only applies when the form holds no value for the field yet.
    value: defaultValue
  });
  const required = useIsRequired(rules, !hidden && !disabled);
  useEffect(() => {
    if (collapsed && !hidden) {
      register(name, { validate, disabled });
    }
  });
  const errorId = `field_${name.replace(/\W+/g, "_")}_error`;
  return {
    hidden: hidden || collapsed,
    disabled,
    // Whether the field is mandatory right now: fields show it next to the label.
    required,
    // For fields that draw their own label: place it next to the label.
    requiredIndicator: required ? requiredIndicator : null,
    registration,
    invalid: Boolean(error),
    message: error?.message,
    // For fields that draw their own input and error: put `inputProps` on the input and `errorProps` on the
    // message, so assistive technology knows the input is invalid and reads the message with it.
    inputProps: error ? { "aria-invalid": true, "aria-describedby": errorId } : {},
    errorProps: { id: errorId, role: "alert" }
  };
}
__name(useFormField, "useFormField");
export {
  markMandatory,
  requiredIndicator,
  toValidate,
  useDefaultIf,
  useFieldConditions,
  useFormField,
  useIsRequired
};
//# sourceMappingURL=formField.dev.js.map
