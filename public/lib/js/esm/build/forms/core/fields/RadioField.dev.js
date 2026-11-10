var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { Radio } from "@moodlehq/design-system";
import { useMoodleFormField } from "../useMoodleFormField";
function RadioField({ name, value, label, supportingText, rules, hideIf, disableIf, advanced, id }) {
  const f = useMoodleFormField(name, { rules, hideIf, disableIf, advanced });
  if (f.hidden) {
    return null;
  }
  return /* @__PURE__ */ jsxDEV("div", { children: [
    /* @__PURE__ */ jsxDEV(
      Radio,
      {
        id: id ?? `field_${name.replace(/\W+/g, "_")}_${value.replace(/\W+/g, "_")}`,
        label,
        value,
        ...f.registration
      },
      void 0,
      false,
      {
        fileName: "public/lib/js/esm/src/forms/core/fields/RadioField.tsx",
        lineNumber: 20,
        columnNumber: 13
      },
      this
    ),
    supportingText && /* @__PURE__ */ jsxDEV("div", { className: "text-muted small ms-4", children: supportingText }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/core/fields/RadioField.tsx",
      lineNumber: 26,
      columnNumber: 32
    }, this)
  ] }, void 0, true, {
    fileName: "public/lib/js/esm/src/forms/core/fields/RadioField.tsx",
    lineNumber: 19,
    columnNumber: 9
  }, this);
}
__name(RadioField, "RadioField");
export {
  RadioField
};
//# sourceMappingURL=RadioField.dev.js.map
