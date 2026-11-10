var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useCallback, useContext, useEffect, useRef, useState } from "react";
import { useFormContext, get } from "react-hook-form";
import { AdvancedContext, ContainerContext } from "../formContext";
function SectionField({ title, defaultOpen = true, advanced, children }) {
  const [open, setOpen] = useState(defaultOpen);
  const names = useRef(/* @__PURE__ */ new Map());
  const { formState: { errors } } = useFormContext();
  const add = useCallback((name) => {
    names.current.set(name, (names.current.get(name) ?? 0) + 1);
    return () => {
      const count = (names.current.get(name) ?? 1) - 1;
      if (count > 0) {
        names.current.set(name, count);
      } else {
        names.current.delete(name);
      }
    };
  }, []);
  const hasError = [...names.current.keys()].some((name) => Boolean(get(errors, name)));
  const { show, register } = useContext(AdvancedContext);
  useEffect(() => advanced ? register() : void 0, [advanced, register]);
  const hidden = Boolean(advanced) && !show && !hasError;
  return /* @__PURE__ */ jsxDEV(ContainerContext.Provider, { value: { add }, children: /* @__PURE__ */ jsxDEV(
    "details",
    {
      className: "border rounded p-3",
      hidden,
      open: open || hasError,
      onToggle: (e) => setOpen(e.currentTarget.open),
      children: [
        /* @__PURE__ */ jsxDEV("summary", { className: "fs-5 fw-bold", children: title }, void 0, false, {
          fileName: "public/lib/js/esm/src/forms/core/fields/SectionField.tsx",
          lineNumber: 50,
          columnNumber: 17
        }, this),
        /* @__PURE__ */ jsxDEV("div", { className: "d-flex flex-column gap-3 mt-3", children }, void 0, false, {
          fileName: "public/lib/js/esm/src/forms/core/fields/SectionField.tsx",
          lineNumber: 51,
          columnNumber: 17
        }, this)
      ]
    },
    void 0,
    true,
    {
      fileName: "public/lib/js/esm/src/forms/core/fields/SectionField.tsx",
      lineNumber: 44,
      columnNumber: 13
    },
    this
  ) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/fields/SectionField.tsx",
    lineNumber: 43,
    columnNumber: 9
  }, this);
}
__name(SectionField, "SectionField");
export {
  SectionField
};
//# sourceMappingURL=SectionField.dev.js.map
