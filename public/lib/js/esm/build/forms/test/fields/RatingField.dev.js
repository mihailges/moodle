var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useFormField } from "../../core/formField";
function RatingField({ name, label, max = 5, rules, hideIf, disableIf, defaultIf, advanced }) {
  const f = useFormField(name, { rules, hideIf, disableIf, defaultIf, advanced });
  if (f.hidden) {
    return null;
  }
  return /* @__PURE__ */ jsxDEV("fieldset", { disabled: f.disabled, children: [
    /* @__PURE__ */ jsxDEV("legend", { className: "fs-6 mb-2", children: [
      label,
      f.requiredIndicator
    ] }, void 0, true, {
      fileName: "public/lib/js/esm/src/forms/test/fields/RatingField.tsx",
      lineNumber: 16,
      columnNumber: 13
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "btn-group", role: "group", "aria-label": label, children: Array.from({ length: max }, (_, i) => String(i + 1)).map((value) => /* @__PURE__ */ jsxDEV("label", { className: "btn btn-outline-primary mb-0", children: [
      /* @__PURE__ */ jsxDEV("input", { type: "radio", className: "me-1", value, ...f.registration, ...f.inputProps }, void 0, false, {
        fileName: "public/lib/js/esm/src/forms/test/fields/RatingField.tsx",
        lineNumber: 20,
        columnNumber: 25
      }, this),
      value
    ] }, value, true, {
      fileName: "public/lib/js/esm/src/forms/test/fields/RatingField.tsx",
      lineNumber: 19,
      columnNumber: 21
    }, this)) }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/test/fields/RatingField.tsx",
      lineNumber: 17,
      columnNumber: 13
    }, this),
    f.invalid && /* @__PURE__ */ jsxDEV("div", { ...f.errorProps, className: "text-danger small mt-1", children: f.message }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/test/fields/RatingField.tsx",
      lineNumber: 25,
      columnNumber: 27
    }, this)
  ] }, void 0, true, {
    fileName: "public/lib/js/esm/src/forms/test/fields/RatingField.tsx",
    lineNumber: 15,
    columnNumber: 9
  }, this);
}
__name(RatingField, "RatingField");
export {
  RatingField
};
//# sourceMappingURL=RatingField.dev.js.map
