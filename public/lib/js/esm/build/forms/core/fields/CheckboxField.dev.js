var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { Checkbox } from "@moodlehq/design-system";
import { useFormField } from "../formField";
function CheckboxField({ name, label, supportingText, rules, hideIf, disableIf, advanced, id, value }) {
  const f = useFormField(name, { rules, hideIf, disableIf, advanced });
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
      fileName: "public/lib/js/esm/src/forms/core/fields/CheckboxField.tsx",
      lineNumber: 18,
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
