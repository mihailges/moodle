var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
import { useFieldConditions } from "../formField";
function StaticField({ name = "static", hideIf, children }) {
  const { hidden } = useFieldConditions(name, { hideIf });
  return hidden ? null : /* @__PURE__ */ jsxDEV(Fragment, { children }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/elements/StaticField.tsx",
    lineNumber: 16,
    columnNumber: 28
  }, this);
}
__name(StaticField, "StaticField");
export {
  StaticField
};
//# sourceMappingURL=StaticField.dev.js.map
