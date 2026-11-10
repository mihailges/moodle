var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useWatch, useFormContext } from "react-hook-form";
import { useFormField } from "../../core/formField";
function ColorField({ name, label, rules, hideIf, disableIf, advanced }) {
  const f = useFormField(name, { rules, hideIf, disableIf, advanced });
  const { control } = useFormContext();
  const value = useWatch({ control, name });
  if (f.hidden) {
    return null;
  }
  return /* @__PURE__ */ jsxDEV("div", { children: [
    /* @__PURE__ */ jsxDEV("label", { className: "form-label d-block", htmlFor: `field_${name}`, children: [
      label,
      f.requiredIndicator
    ] }, void 0, true, {
      fileName: "public/lib/js/esm/src/forms/test/fields/ColorField.tsx",
      lineNumber: 16,
      columnNumber: 13
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "d-flex align-items-center gap-2", children: [
      /* @__PURE__ */ jsxDEV("input", { id: `field_${name}`, type: "color", className: "form-control form-control-color", ...f.registration }, void 0, false, {
        fileName: "public/lib/js/esm/src/forms/test/fields/ColorField.tsx",
        lineNumber: 18,
        columnNumber: 17
      }, this),
      /* @__PURE__ */ jsxDEV("code", { children: value }, void 0, false, {
        fileName: "public/lib/js/esm/src/forms/test/fields/ColorField.tsx",
        lineNumber: 19,
        columnNumber: 17
      }, this)
    ] }, void 0, true, {
      fileName: "public/lib/js/esm/src/forms/test/fields/ColorField.tsx",
      lineNumber: 17,
      columnNumber: 13
    }, this),
    f.invalid && /* @__PURE__ */ jsxDEV("div", { role: "alert", className: "text-danger small mt-1", children: f.message }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/test/fields/ColorField.tsx",
      lineNumber: 21,
      columnNumber: 27
    }, this)
  ] }, void 0, true, {
    fileName: "public/lib/js/esm/src/forms/test/fields/ColorField.tsx",
    lineNumber: 15,
    columnNumber: 9
  }, this);
}
__name(ColorField, "ColorField");
export {
  ColorField
};
//# sourceMappingURL=ColorField.dev.js.map
