var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useEffect } from "react";
import { useFormContext, get } from "react-hook-form";
import { Checkbox } from "@moodlehq/design-system";
import { useFormField } from "../formField";
function CheckboxField({
  name,
  label,
  supportingText,
  rules,
  hideIf,
  disableIf,
  defaultValue,
  defaultIf,
  advanced,
  id,
  value
}) {
  const mergeIntoList = /* @__PURE__ */ __name((wanted, current) => {
    const list = Array.isArray(current) ? current.filter((v) => v !== value) : [];
    return wanted ? [...list, value] : list;
  }, "mergeIntoList");
  const f = useFormField(
    name,
    { rules, hideIf, disableIf, defaultValue, defaultIf, advanced },
    value === void 0 ? void 0 : mergeIntoList
  );
  const { control, setValue } = useFormContext();
  useEffect(() => {
    if (value !== void 0 && !Array.isArray(get(control._formValues, name))) {
      setValue(name, []);
    }
  }, []);
  if (f.hidden) {
    return null;
  }
  return /* @__PURE__ */ jsxDEV(
    Checkbox,
    {
      id: id ?? `field_${name.replace(/\W+/g, "_")}${value ? `_${value.replace(/\W+/g, "_")}` : ""}`,
      value,
      label,
      required: f.required,
      supportingText,
      invalid: f.invalid,
      invalidFeedback: f.message,
      ...f.registration
    },
    void 0,
    false,
    {
      fileName: "public/lib/js/esm/src/forms/core/elements/CheckboxField.tsx",
      lineNumber: 39,
      columnNumber: 9
    },
    this
  );
}
__name(CheckboxField, "CheckboxField");
export {
  CheckboxField
};
//# sourceMappingURL=CheckboxField.dev.js.map
