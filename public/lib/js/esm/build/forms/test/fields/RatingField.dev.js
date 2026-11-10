var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useMoodleFormField } from "../../core/useMoodleFormField";
function RatingField({ name, label, max = 5, rules, hideIf, disableIf, advanced }) {
  const f = useMoodleFormField(name, { rules, hideIf, disableIf, advanced });
  if (f.hidden) {
    return null;
  }
  return /* @__PURE__ */ jsxDEV("fieldset", { disabled: f.disabled, children: [
    /* @__PURE__ */ jsxDEV("legend", { className: "fs-6 mb-2", children: [
      label,
      f.requiredIndicator
    ] }, void 0, true, {
      fileName: "public/lib/js/esm/src/forms/test/fields/RatingField.tsx",
      lineNumber: 17,
      columnNumber: 13
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "btn-group", role: "group", "aria-label": label, children: Array.from({ length: max }, (_, i) => String(i + 1)).map((value) => /* @__PURE__ */ jsxDEV("label", { className: "btn btn-outline-primary mb-0", children: [
      /* @__PURE__ */ jsxDEV("input", { type: "radio", className: "me-1", value, ...f.registration }, void 0, false, {
        fileName: "public/lib/js/esm/src/forms/test/fields/RatingField.tsx",
        lineNumber: 21,
        columnNumber: 25
      }, this),
      value
    ] }, value, true, {
      fileName: "public/lib/js/esm/src/forms/test/fields/RatingField.tsx",
      lineNumber: 20,
      columnNumber: 21
    }, this)) }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/test/fields/RatingField.tsx",
      lineNumber: 18,
      columnNumber: 13
    }, this),
    f.invalid && /* @__PURE__ */ jsxDEV("div", { role: "alert", className: "text-danger small mt-1", children: f.message }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/test/fields/RatingField.tsx",
      lineNumber: 26,
      columnNumber: 27
    }, this)
  ] }, void 0, true, {
    fileName: "public/lib/js/esm/src/forms/test/fields/RatingField.tsx",
    lineNumber: 16,
    columnNumber: 9
  }, this);
}
__name(RatingField, "RatingField");
export {
  RatingField
};
//# sourceMappingURL=RatingField.dev.js.map
