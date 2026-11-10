var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useWatch, useFormContext } from "react-hook-form";
import { useFormField } from "../../core/formField";
function CountedTextareaField({ name, label, maxLength, rules, hideIf, disableIf, advanced }) {
  const f = useFormField(name, { rules, hideIf, disableIf, advanced });
  const { control } = useFormContext();
  const value = useWatch({ control, name }) ?? "";
  if (f.hidden) {
    return null;
  }
  const left = maxLength - String(value).length;
  return /* @__PURE__ */ jsxDEV("div", { children: [
    /* @__PURE__ */ jsxDEV("label", { className: "form-label", htmlFor: `field_${name}`, children: [
      label,
      f.requiredIndicator
    ] }, void 0, true, {
      fileName: "public/lib/js/esm/src/forms/test/fields/CountedTextareaField.tsx",
      lineNumber: 20,
      columnNumber: 13
    }, this),
    /* @__PURE__ */ jsxDEV("textarea", { id: `field_${name}`, className: "form-control", rows: 3, ...f.registration }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/test/fields/CountedTextareaField.tsx",
      lineNumber: 21,
      columnNumber: 13
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: `small mt-1 ${left < 0 ? "text-danger" : "text-muted"}`, children: [
      left,
      " characters left"
    ] }, void 0, true, {
      fileName: "public/lib/js/esm/src/forms/test/fields/CountedTextareaField.tsx",
      lineNumber: 22,
      columnNumber: 13
    }, this),
    f.invalid && /* @__PURE__ */ jsxDEV("div", { role: "alert", className: "text-danger small mt-1", children: f.message }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/test/fields/CountedTextareaField.tsx",
      lineNumber: 23,
      columnNumber: 27
    }, this)
  ] }, void 0, true, {
    fileName: "public/lib/js/esm/src/forms/test/fields/CountedTextareaField.tsx",
    lineNumber: 19,
    columnNumber: 9
  }, this);
}
__name(CountedTextareaField, "CountedTextareaField");
export {
  CountedTextareaField
};
//# sourceMappingURL=CountedTextareaField.dev.js.map
