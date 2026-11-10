var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useState } from "react";
import { useContainer, ContainerProvider } from "../containers";
function SectionField({ title, defaultOpen = true, advanced, children }) {
  const [open, setOpen] = useState(defaultOpen);
  const { add, hasError, hidden } = useContainer(advanced);
  return /* @__PURE__ */ jsxDEV(ContainerProvider, { add, children: /* @__PURE__ */ jsxDEV(
    "details",
    {
      className: "border rounded p-3",
      hidden,
      open: open || hasError,
      onToggle: (e) => setOpen(e.currentTarget.open),
      children: [
        /* @__PURE__ */ jsxDEV("summary", { className: "fs-5 fw-bold", children: title }, void 0, false, {
          fileName: "public/lib/js/esm/src/forms/core/fields/SectionField.tsx",
          lineNumber: 28,
          columnNumber: 17
        }, this),
        /* @__PURE__ */ jsxDEV("div", { className: "d-flex flex-column gap-3 mt-3", children }, void 0, false, {
          fileName: "public/lib/js/esm/src/forms/core/fields/SectionField.tsx",
          lineNumber: 29,
          columnNumber: 17
        }, this)
      ]
    },
    void 0,
    true,
    {
      fileName: "public/lib/js/esm/src/forms/core/fields/SectionField.tsx",
      lineNumber: 22,
      columnNumber: 13
    },
    this
  ) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/fields/SectionField.tsx",
    lineNumber: 21,
    columnNumber: 9
  }, this);
}
__name(SectionField, "SectionField");
export {
  SectionField
};
//# sourceMappingURL=SectionField.dev.js.map
