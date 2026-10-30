var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { useContext, useMemo } from "react";
import { useFormContext, useWatch, set, get } from "react-hook-form";
import { all } from "./rules/all";
import { FormStateContext } from "./context";
function useFieldConditions(name, { hideIf, disableIf } = {}) {
  const { serverDisabled } = useContext(FormStateContext);
  const { control } = useFormContext();
  const deps = [...hideIf?.deps ?? [], ...disableIf?.deps ?? []];
  const depsKey = deps.join("|");
  const stableDeps = useMemo(() => deps, [depsKey]);
  const watched = useWatch({ control, name: stableDeps, disabled: stableDeps.length === 0 });
  const values = useMemo(() => {
    const result = {};
    stableDeps.forEach((dep, i) => set(result, dep, watched?.[i]));
    return result;
  }, [stableDeps, watched]);
  return {
    hidden: hideIf?.(values) ?? false,
    disabled: (disableIf?.(values) ?? false) || serverDisabled.includes(name)
  };
}
__name(useFieldConditions, "useFieldConditions");
function useDefinedField(name, { rules, hideIf, disableIf } = {}) {
  const { register, formState: { errors } } = useFormContext();
  const { hidden, disabled } = useFieldConditions(name, { hideIf, disableIf });
  const error = get(errors, name);
  const registration = hidden ? void 0 : register(name, {
    validate: rules?.length ? all(...rules) : void 0,
    disabled
  });
  return { hidden, disabled, registration, invalid: Boolean(error), message: error?.message };
}
__name(useDefinedField, "useDefinedField");
export {
  useDefinedField,
  useFieldConditions
};
//# sourceMappingURL=useDefinedField.dev.js.map
