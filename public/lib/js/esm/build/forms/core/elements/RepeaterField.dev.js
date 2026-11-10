var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useContext, useEffect, useRef, useState } from "react";
import { useFieldArray, useFormContext, useWatch } from "react-hook-form";
import { Button } from "@moodlehq/design-system";
import { getString } from "@moodle/lms/core/stringUtils";
import { useFieldConditions, useIsRequired, toValidate, requiredIndicator } from "../formField";
import { FieldRegistryContext, RowContext } from "../formContext";
function RepeaterField({
  name,
  label,
  addLabel,
  emptyItem = "",
  minRows = 1,
  removeLabel,
  rules,
  hideIf,
  disableIf,
  defaultValue,
  advanced,
  children
}) {
  const { control, clearErrors, getFieldState, formState, getValues, setValue, trigger } = useFormContext();
  const registry = useContext(FieldRegistryContext);
  const [defaults, setDefaults] = useState({ add: "Add", remove: "Remove" });
  useEffect(() => {
    Promise.all([getString("add"), getString("remove")]).then(([add, remove2]) => setDefaults({ add, remove: remove2 })).catch(() => void 0);
  }, []);
  registry.addRepeater(name);
  const initial = getValues(name) ?? defaultValue;
  if (Array.isArray(initial) && initial.some((row) => row === null || typeof row !== "object" || !("value" in row))) {
    const rows2 = toRows(initial.map((row) => row?.value ?? row));
    setValue(name, rows2);
    const parts = name.split(".");
    const parent = parts.slice(0, -1).reduce((o, k) => o?.[k], control._defaultValues);
    if (parent) {
      parent[parts[parts.length - 1]] = rows2;
    }
  }
  const { fields, append, remove } = useFieldArray({
    control,
    name,
    // Rules see the plain list of values, not the {value} rows used internally.
    rules: {
      validate: toValidate(rules?.map(
        (rule) => (rows2, values) => rule(rows2.map((row) => row.value), values)
      ))
    }
  });
  const addButton = useRef(null);
  const { hidden, collapsed, disabled } = useFieldConditions(name, { hideIf, disableIf, advanced });
  const required = useIsRequired(rules, !hidden && !disabled);
  const message = getFieldState(name, formState).error?.message ?? formState.errors?.[name]?.root?.message;
  const rows = useWatch({ control, name });
  useEffect(() => {
    if (message && !hidden) {
      trigger(name);
    }
  }, [rows]);
  if (hidden) {
    return null;
  }
  const addRow = /* @__PURE__ */ __name(() => {
    clearErrors(name);
    append({ value: emptyItem });
  }, "addRow");
  const removeRow = /* @__PURE__ */ __name((index) => {
    clearErrors(name);
    remove(index);
    addButton.current?.querySelector("button")?.focus();
  }, "removeRow");
  const errorId = `field_${name.replace(/\W+/g, "_")}_error`;
  return /* @__PURE__ */ jsxDEV(
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
          fileName: "public/lib/js/esm/src/forms/core/elements/RepeaterField.tsx",
          lineNumber: 90,
          columnNumber: 13
        }, this),
        /* @__PURE__ */ jsxDEV("div", { className: "d-flex flex-column gap-2", children: fields.map((field, index) => /* @__PURE__ */ jsxDEV("div", { className: "d-flex gap-2 align-items-start", children: [
          /* @__PURE__ */ jsxDEV("div", { className: "flex-grow-1", children: /* @__PURE__ */ jsxDEV(RowContext.Provider, { value: `${name}.${index}.value`, children }, void 0, false, {
            fileName: "public/lib/js/esm/src/forms/core/elements/RepeaterField.tsx",
            lineNumber: 95,
            columnNumber: 29
          }, this) }, void 0, false, {
            fileName: "public/lib/js/esm/src/forms/core/elements/RepeaterField.tsx",
            lineNumber: 94,
            columnNumber: 25
          }, this),
          /* @__PURE__ */ jsxDEV(
            Button,
            {
              type: "button",
              onClick: () => removeRow(index),
              disabled: disabled || fields.length <= minRows,
              "aria-label": `${removeLabel ?? defaults.remove} ${label} ${index + 1}`,
              label: removeLabel ?? "",
              size: "lg",
              startIcon: removeLabel ? void 0 : /* @__PURE__ */ jsxDEV("i", { "aria-hidden": "true", className: "fa-solid fa-xmark" }, void 0, false, {
                fileName: "public/lib/js/esm/src/forms/core/elements/RepeaterField.tsx",
                lineNumber: 104,
                columnNumber: 66
              }, this),
              variant: "outline-secondary"
            },
            void 0,
            false,
            {
              fileName: "public/lib/js/esm/src/forms/core/elements/RepeaterField.tsx",
              lineNumber: 97,
              columnNumber: 25
            },
            this
          )
        ] }, field.id, true, {
          fileName: "public/lib/js/esm/src/forms/core/elements/RepeaterField.tsx",
          lineNumber: 93,
          columnNumber: 21
        }, this)) }, void 0, false, {
          fileName: "public/lib/js/esm/src/forms/core/elements/RepeaterField.tsx",
          lineNumber: 91,
          columnNumber: 13
        }, this),
        message && /* @__PURE__ */ jsxDEV("div", { id: errorId, role: "alert", className: "text-danger small mt-1", children: message }, void 0, false, {
          fileName: "public/lib/js/esm/src/forms/core/elements/RepeaterField.tsx",
          lineNumber: 110,
          columnNumber: 25
        }, this),
        /* @__PURE__ */ jsxDEV("div", { ref: addButton, children: /* @__PURE__ */ jsxDEV(
          Button,
          {
            type: "button",
            className: "mt-2",
            onClick: addRow,
            label: addLabel ?? defaults.add,
            startIcon: addLabel ? void 0 : /* @__PURE__ */ jsxDEV("i", { "aria-hidden": "true", className: "fa-solid fa-plus" }, void 0, false, {
              fileName: "public/lib/js/esm/src/forms/core/elements/RepeaterField.tsx",
              lineNumber: 117,
              columnNumber: 55
            }, this),
            disabled
          },
          void 0,
          false,
          {
            fileName: "public/lib/js/esm/src/forms/core/elements/RepeaterField.tsx",
            lineNumber: 112,
            columnNumber: 17
          },
          this
        ) }, void 0, false, {
          fileName: "public/lib/js/esm/src/forms/core/elements/RepeaterField.tsx",
          lineNumber: 111,
          columnNumber: 13
        }, this)
      ]
    },
    void 0,
    true,
    {
      fileName: "public/lib/js/esm/src/forms/core/elements/RepeaterField.tsx",
      lineNumber: 84,
      columnNumber: 9
    },
    this
  );
}
__name(RepeaterField, "RepeaterField");
const toRows = /* @__PURE__ */ __name((items) => items.map((value) => ({ value })), "toRows");
export {
  RepeaterField
};
//# sourceMappingURL=RepeaterField.dev.js.map
