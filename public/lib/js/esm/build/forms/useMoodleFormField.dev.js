var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { useContext, useEffect } from "react";
import { useFormContext, useWatch, get } from "react-hook-form";
import { all } from "./rules/all";
import { FormStateContext, GroupContext, FieldRegistryContext, RowContext } from "./context";
function useFieldConditions(name, { hideIf, disableIf } = {}) {
  const { serverDisabled } = useContext(FormStateContext);
  const { disabled: groupDisabled } = useContext(GroupContext);
  const { control } = useFormContext();
  const registry = useContext(FieldRegistryContext);
  useEffect(() => {
    registry.add(name);
    return () => registry.remove(name);
  }, [registry, name]);
  const values = useWatch({ control, disabled: !hideIf && !disableIf });
  return {
    hidden: hideIf?.(values) ?? false,
    disabled: (disableIf?.(values) ?? false) || groupDisabled || serverDisabled.includes(name)
  };
}
__name(useFieldConditions, "useFieldConditions");
function useMoodleFormField(fieldName, { rules, hideIf, disableIf } = {}) {
  const rowName = useContext(RowContext);
  const name = fieldName ?? rowName;
  if (name === null || name === void 0) {
    throw new Error("A field needs a name, unless it is the field of a RepeaterField.");
  }
  const { register, formState: { errors } } = useFormContext();
  const { hidden, disabled } = useFieldConditions(name, { hideIf, disableIf });
  const group = useContext(GroupContext);
  const error = group.name === name ? void 0 : get(errors, name);
  const validate = rules?.length ? all(...rules) : group.name === name ? group.validate : void 0;
  const registration = hidden ? void 0 : register(name, {
    validate,
    disabled
  });
  return {
    hidden,
    disabled,
    registration,
    invalid: Boolean(error),
    message: error?.message
  };
}
__name(useMoodleFormField, "useMoodleFormField");
export {
  useFieldConditions,
  useMoodleFormField
};
//# sourceMappingURL=useMoodleFormField.dev.js.map
