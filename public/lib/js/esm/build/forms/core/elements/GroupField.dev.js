var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useController } from "react-hook-form";
import { useFieldConditions, useIsRequired, toValidate, requiredIndicator } from "../formField";
import { GroupContext } from "../formContext";
function GroupField({ name, label, layout = "row", rules, hideIf, disableIf, advanced, children }) {
  const validate = toValidate(rules);
  const { fieldState } = useController({ name, rules: { validate } });
  const { hidden, collapsed, disabled } = useFieldConditions(name, { hideIf, disableIf, advanced });
  const required = useIsRequired(rules, !hidden && !disabled);
  if (hidden) {
    return null;
  }
  const message = fieldState.error?.message;
  const errorId = `field_${name.replace(/\W+/g, "_")}_error`;
  return /* @__PURE__ */ jsxDEV(GroupContext.Provider, { value: { disabled, name, validate }, children: /* @__PURE__ */ jsxDEV(
    "fieldset",
    {
      hidden: collapsed,
      tabIndex: message ? -1 : void 0,
      "data-invalid": message ? true : void 0,
      "aria-describedby": message ? errorId : void 0,
      children: [
        /* @__PURE__ */ jsxDEV("legend", { className: "fs-6 mb-2", children: [
          label,
          required && requiredIndicator
        ] }, void 0, true, {
          fileName: "public/lib/js/esm/src/forms/core/elements/GroupField.tsx",
          lineNumber: 40,
          columnNumber: 17
        }, this),
        /* @__PURE__ */ jsxDEV("div", { className: layout === "row" ? "d-flex gap-2 align-items-start" : "d-flex flex-column gap-2", children }, void 0, false, {
          fileName: "public/lib/js/esm/src/forms/core/elements/GroupField.tsx",
          lineNumber: 41,
          columnNumber: 17
        }, this),
        message && /* @__PURE__ */ jsxDEV("div", { id: errorId, role: "alert", className: "text-danger small mt-1", children: message }, void 0, false, {
          fileName: "public/lib/js/esm/src/forms/core/elements/GroupField.tsx",
          lineNumber: 42,
          columnNumber: 29
        }, this)
      ]
    },
    void 0,
    true,
    {
      fileName: "public/lib/js/esm/src/forms/core/elements/GroupField.tsx",
      lineNumber: 34,
      columnNumber: 13
    },
    this
  ) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/elements/GroupField.tsx",
    lineNumber: 33,
    columnNumber: 9
  }, this);
}
__name(GroupField, "GroupField");
export {
  GroupField
};
//# sourceMappingURL=GroupField.dev.js.map
