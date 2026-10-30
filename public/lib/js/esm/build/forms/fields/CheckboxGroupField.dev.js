var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { Checkbox } from "@moodlehq/design-system";
import { useDefinedField } from "../useDefinedField";
function CheckboxGroupField({ name, label, options, rules, hideIf, disableIf, id }) {
  const f = useDefinedField(name, { rules, hideIf, disableIf });
  const prefix = id ?? `field_${name.replace(/\W+/g, "_")}`;
  if (f.hidden) {
    return null;
  }
  return /* @__PURE__ */ jsxDEV("fieldset", { "aria-describedby": f.invalid ? `${prefix}_error` : void 0, children: [
    /* @__PURE__ */ jsxDEV("legend", { className: "fs-6 mb-2", children: label }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/fields/CheckboxGroupField.tsx",
      lineNumber: 14,
      columnNumber: 13
    }, this),
    options === null ? /* @__PURE__ */ jsxDEV("p", { className: "text-muted", children: "Loading\u2026" }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/fields/CheckboxGroupField.tsx",
      lineNumber: 16,
      columnNumber: 17
    }, this) : options.map((option) => /* @__PURE__ */ jsxDEV(
      Checkbox,
      {
        id: `${prefix}_${option.value.replace(/\W+/g, "_")}`,
        label: option.label,
        value: option.value,
        ...f.registration
      },
      option.value,
      false,
      {
        fileName: "public/lib/js/esm/src/forms/fields/CheckboxGroupField.tsx",
        lineNumber: 18,
        columnNumber: 17
      },
      this
    )),
    f.invalid && /* @__PURE__ */ jsxDEV("div", { id: `${prefix}_error`, className: "text-danger small mt-1", children: f.message }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/fields/CheckboxGroupField.tsx",
      lineNumber: 26,
      columnNumber: 27
    }, this)
  ] }, void 0, true, {
    fileName: "public/lib/js/esm/src/forms/fields/CheckboxGroupField.tsx",
    lineNumber: 13,
    columnNumber: 9
  }, this);
}
__name(CheckboxGroupField, "CheckboxGroupField");
export {
  CheckboxGroupField
};
//# sourceMappingURL=CheckboxGroupField.dev.js.map
