var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useContext } from "react";
import { useFieldArray, useFormContext } from "react-hook-form";
import { Button } from "@moodlehq/design-system";
import { useFieldConditions } from "../useMoodleFormField";
import { all } from "../rules/all";
import { FieldRegistryContext, RowContext } from "../context";
function RepeaterField({
  name,
  label,
  addLabel,
  emptyItem = "",
  minRows = 1,
  removeLabel = "Remove",
  rules,
  hideIf,
  disableIf,
  children
}) {
  const { control, clearErrors, getFieldState, formState, getValues, setValue } = useFormContext();
  const registry = useContext(FieldRegistryContext);
  registry.addRepeater(name);
  const initial = getValues(name);
  if (Array.isArray(initial) && initial.some((row) => row === null || typeof row !== "object" || !("value" in row))) {
    const rows = toRows(initial.map((row) => row?.value ?? row));
    setValue(name, rows);
    const parts = name.split(".");
    const parent = parts.slice(0, -1).reduce((o, k) => o?.[k], control._defaultValues);
    if (parent) {
      parent[parts[parts.length - 1]] = rows;
    }
  }
  const { fields, append, remove } = useFieldArray({
    control,
    name,
    // Rules see the plain list of values, not the {value} rows used internally.
    rules: {
      validate: rules?.length ? (rows, values) => all(...rules)(rows.map((row) => row.value), values) : void 0
    }
  });
  const { hidden, disabled } = useFieldConditions(name, { hideIf, disableIf });
  if (hidden) {
    return null;
  }
  const message = getFieldState(name, formState).error?.message ?? formState.errors?.[name]?.root?.message;
  const addRow = /* @__PURE__ */ __name(() => {
    clearErrors(name);
    append({ value: emptyItem });
  }, "addRow");
  const removeRow = /* @__PURE__ */ __name((index) => {
    clearErrors(name);
    remove(index);
  }, "removeRow");
  return /* @__PURE__ */ jsxDEV("fieldset", { children: [
    /* @__PURE__ */ jsxDEV("legend", { className: "fs-6 mb-2", children: label }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
      lineNumber: 65,
      columnNumber: 13
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "d-flex flex-column gap-2", children: fields.map((field, index) => /* @__PURE__ */ jsxDEV("div", { className: "d-flex gap-2 align-items-center", children: [
      /* @__PURE__ */ jsxDEV(RowContext.Provider, { value: `${name}.${index}.value`, children }, void 0, false, {
        fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
        lineNumber: 69,
        columnNumber: 25
      }, this),
      /* @__PURE__ */ jsxDEV(
        Button,
        {
          type: "button",
          onClick: () => removeRow(index),
          disabled: disabled || fields.length <= minRows,
          "aria-label": `${removeLabel} ${label} ${index + 1}`,
          label: "",
          size: "lg",
          startIcon: /* @__PURE__ */ jsxDEV("i", { "aria-hidden": "true", className: "fa-solid fa-xmark" }, void 0, false, {
            fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
            lineNumber: 77,
            columnNumber: 40
          }, this),
          variant: "outline-secondary"
        },
        void 0,
        false,
        {
          fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
          lineNumber: 70,
          columnNumber: 25
        },
        this
      )
    ] }, field.id, true, {
      fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
      lineNumber: 68,
      columnNumber: 21
    }, this)) }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
      lineNumber: 66,
      columnNumber: 13
    }, this),
    message && /* @__PURE__ */ jsxDEV("div", { role: "alert", className: "text-danger small mt-1", children: message }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
      lineNumber: 83,
      columnNumber: 25
    }, this),
    /* @__PURE__ */ jsxDEV(
      Button,
      {
        type: "button",
        className: "mt-2",
        onClick: addRow,
        label: addLabel,
        disabled
      },
      void 0,
      false,
      {
        fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
        lineNumber: 84,
        columnNumber: 13
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/lib/js/esm/src/forms/fields/RepeaterField.tsx",
    lineNumber: 64,
    columnNumber: 9
  }, this);
}
__name(RepeaterField, "RepeaterField");
const toRows = /* @__PURE__ */ __name((items) => items.map((value) => ({ value })), "toRows");
export {
  RepeaterField
};
//# sourceMappingURL=RepeaterField.dev.js.map
