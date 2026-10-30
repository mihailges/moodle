var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useRef } from "react";
import { useController, useFormContext } from "react-hook-form";
import { Button } from "@moodlehq/design-system";
import { useFieldConditions } from "../useDefinedField";
import { all } from "../rules/all";
let nextKey = 0;
function RepeaterField({
  name,
  label,
  addLabel,
  emptyItem = "",
  minRows = 1,
  rules,
  hideIf,
  disableIf,
  children
}) {
  const { clearErrors } = useFormContext();
  const { field, fieldState } = useController({
    name,
    defaultValue: [],
    rules: { validate: rules?.length ? all(...rules) : void 0 }
  });
  const { hidden, disabled } = useFieldConditions(name, { hideIf, disableIf });
  const keys = useRef([]);
  if (hidden) {
    return null;
  }
  const rows = Array.isArray(field.value) ? field.value : [];
  if (keys.current.length !== rows.length) {
    keys.current = rows.map(() => nextKey++);
  }
  const update = /* @__PURE__ */ __name((next) => {
    clearErrors(name);
    field.onChange(next);
  }, "update");
  const addRow = /* @__PURE__ */ __name(() => {
    keys.current = [...keys.current, nextKey++];
    update([...rows, emptyItem]);
  }, "addRow");
  const removeRow = /* @__PURE__ */ __name((index) => {
    keys.current = keys.current.filter((_, i) => i !== index);
    update(rows.filter((_, i) => i !== index));
  }, "removeRow");
  const message = fieldState.error?.message;
  return /* @__PURE__ */ jsxDEV("fieldset", { children: [
    /* @__PURE__ */ jsxDEV("legend", { className: "fs-6 mb-2", children: label }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
      lineNumber: 58,
      columnNumber: 13
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "d-flex flex-column gap-2", children: rows.map((_, index) => /* @__PURE__ */ jsxDEV("div", { className: "d-flex gap-2 align-items-start", children: [
      children(index),
      /* @__PURE__ */ jsxDEV(
        Button,
        {
          type: "button",
          onClick: () => removeRow(index),
          disabled: disabled || rows.length <= minRows,
          "aria-label": `Remove ${label} ${index + 1}`,
          label: "Remove",
          variant: "secondary"
        },
        void 0,
        false,
        {
          fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
          lineNumber: 63,
          columnNumber: 25
        },
        this
      )
    ] }, keys.current[index], true, {
      fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
      lineNumber: 61,
      columnNumber: 21
    }, this)) }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
      lineNumber: 59,
      columnNumber: 13
    }, this),
    message && /* @__PURE__ */ jsxDEV("div", { role: "alert", className: "text-danger small mt-1", children: message }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
      lineNumber: 74,
      columnNumber: 25
    }, this),
    /* @__PURE__ */ jsxDEV(Button, { type: "button", className: "mt-2", onClick: addRow, label: addLabel, disabled }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
      lineNumber: 75,
      columnNumber: 13
    }, this)
  ] }, void 0, true, {
    fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
    lineNumber: 57,
    columnNumber: 9
  }, this);
}
__name(RepeaterField, "RepeaterField");
export {
  RepeaterField
};
//# sourceMappingURL=RepeaterField.dev.js.map
