var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useEffect, useState } from "react";
import { useForm, useFieldArray } from "react-hook-form";
import { Input, Textarea, Switch, Checkbox, Button } from "@moodlehq/design-system";
import { getScopes, createClient, FormValidationError, getClient, patchClient } from "@moodle/lms/core_admin/oauth2/clientApi";
function ClientForm({ clientId }) {
  const { register, control, handleSubmit, reset, setError, formState: { dirtyFields, errors, isDirty, isSubmitting } } = useForm({
    defaultValues: { name: "Test client", description: "", ispkcerequired: false, scopes: [], redirecturis: [{ uri: "" }] }
  });
  const { fields, append, remove } = useFieldArray({ control, name: "redirecturis" });
  const toFormValues = /* @__PURE__ */ __name((c) => ({ ...c, redirecturis: c.redirecturis.map((uri) => ({ uri })) }), "toFormValues");
  const [scopeOptions, setScopeOptions] = useState(null);
  const [loaded, setLoaded] = useState(false);
  const [readonly, setReadonly] = useState([]);
  const [loadError, setLoadError] = useState(null);
  useEffect(() => {
    Promise.all([getScopes(), clientId ? getClient(clientId) : Promise.resolve(null)]).then(([options, client]) => {
      setScopeOptions(options);
      if (client) {
        reset(toFormValues(client.values));
        setReadonly(client.readonly);
      }
      setLoaded(true);
    }).catch(() => {
      setLoadError("this client could not be loaded");
    }).finally(() => setLoaded(true));
  }, [clientId]);
  if (!loaded) {
    return /* @__PURE__ */ jsxDEV("p", { children: "Loading..." }, void 0, false, {
      fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
      lineNumber: 45,
      columnNumber: 16
    }, this);
  }
  if (loadError) {
    return /* @__PURE__ */ jsxDEV("div", { role: "alert", className: "alert alert-danger", children: loadError }, void 0, false, {
      fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
      lineNumber: 48,
      columnNumber: 16
    }, this);
  }
  const toPayload = /* @__PURE__ */ __name((values) => ({
    ...values,
    redirecturis: values.redirecturis.map((r) => r.uri)
  }), "toPayload");
  const changedValues = /* @__PURE__ */ __name((values) => {
    const payload = toPayload(values);
    return Object.fromEntries(
      Object.keys(dirtyFields).map((key) => [key, payload[key]])
    );
  }, "changedValues");
  const onSubmit = /* @__PURE__ */ __name(async (values) => {
    try {
      if (clientId) {
        const changes = changedValues(values);
        if (Object.keys(changes).length === 0) {
          return;
        }
        const saved = await patchClient(clientId, changes);
        reset(toFormValues(saved));
        console.log("updated", saved);
      } else {
        const client = await createClient(toPayload(values));
        console.log("created", client);
      }
    } catch (e) {
      if (!(e instanceof FormValidationError)) {
        setError("root.server", { message: "Something went wrong. Try again." });
        return;
      }
      Object.entries(e.fieldErrors).forEach(([path, messages], i) => {
        const formPath = path.startsWith("redirecturis.") ? `${path}.uri` : path;
        setError(formPath, { type: "server", message: messages[0] }, { shouldFocus: i === 0 });
      });
      e.formErrors.forEach((message) => setError("root.server", { message }));
    }
  }, "onSubmit");
  return /* @__PURE__ */ jsxDEV("form", { onSubmit: handleSubmit(onSubmit), noValidate: true, children: [
    /* @__PURE__ */ jsxDEV(
      Input,
      {
        label: "Name",
        ...register("name", {
          required: "Enter a client name",
          maxLength: { value: 15, message: "Name must be 15 characters or fewer" }
        }),
        invalid: Boolean(errors.name),
        invalidFeedback: errors.name?.message,
        readOnly: readonly.includes("name")
      },
      void 0,
      false,
      {
        fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
        lineNumber: 94,
        columnNumber: 13
      },
      this
    ),
    /* @__PURE__ */ jsxDEV(
      Textarea,
      {
        label: "Description",
        ...register("description")
      },
      void 0,
      false,
      {
        fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
        lineNumber: 104,
        columnNumber: 13
      },
      this
    ),
    /* @__PURE__ */ jsxDEV(
      Switch,
      {
        label: "Is PKCE required",
        ...register("ispkcerequired"),
        disabled: readonly.includes("ispkcerequired")
      },
      void 0,
      false,
      {
        fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
        lineNumber: 108,
        columnNumber: 13
      },
      this
    ),
    /* @__PURE__ */ jsxDEV("fieldset", { children: [
      /* @__PURE__ */ jsxDEV("legend", { className: "fs-6 mb-2", children: "Redirect URIs" }, void 0, false, {
        fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
        lineNumber: 115,
        columnNumber: 17
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "d-flex flex-column gap-2", children: fields.map((field, index) => /* @__PURE__ */ jsxDEV("div", { className: "d-flex gap-2 align-items-start", children: [
        /* @__PURE__ */ jsxDEV(
          Input,
          {
            id: `oauth2client_redirecturis_${index}`,
            label: `Redirect URI ${index + 1}`,
            hideLabel: true,
            ...register(`redirecturis.${index}.uri`, {
              required: "Enter a redirect URI",
              validate: /* @__PURE__ */ __name((v) => URL.canParse(v) || "Enter a valid URL", "validate")
            }),
            invalid: Boolean(errors.redirecturis?.[index]?.uri),
            invalidFeedback: errors.redirecturis?.[index]?.uri?.message
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
            lineNumber: 119,
            columnNumber: 29
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(
          Button,
          {
            type: "button",
            onClick: () => remove(index),
            disabled: fields.length === 1,
            "aria-label": `Remove redirect URI ${index + 1}`,
            label: "Remove",
            variant: "secondary"
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
            lineNumber: 130,
            columnNumber: 29
          },
          this
        )
      ] }, field.id, true, {
        fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
        lineNumber: 118,
        columnNumber: 25
      }, this)) }, void 0, false, {
        fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
        lineNumber: 116,
        columnNumber: 17
      }, this),
      /* @__PURE__ */ jsxDEV(Button, { type: "button", className: "mt-2", onClick: () => append({ uri: "" }), label: "Add redirect URI" }, void 0, false, {
        fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
        lineNumber: 141,
        columnNumber: 17
      }, this)
    ] }, void 0, true, {
      fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
      lineNumber: 114,
      columnNumber: 13
    }, this),
    /* @__PURE__ */ jsxDEV("fieldset", { "aria-describedby": errors.scopes ? "oauth2client_scopes_error" : void 0, children: [
      /* @__PURE__ */ jsxDEV("legend", { className: "fs-6 mb-2", children: "Scopes" }, void 0, false, {
        fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
        lineNumber: 145,
        columnNumber: 17
      }, this),
      scopeOptions === null ? /* @__PURE__ */ jsxDEV("p", { className: "text-muted", children: "Loading scopes\u2026" }, void 0, false, {
        fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
        lineNumber: 148,
        columnNumber: 21
      }, this) : scopeOptions.map((scope) => /* @__PURE__ */ jsxDEV(
        Checkbox,
        {
          id: `oauth2client_scopes_${scope.value.replace(/\W+/g, "_")}`,
          label: scope.label,
          value: scope.value,
          ...register("scopes", {
            validate: /* @__PURE__ */ __name((v) => Array.isArray(v) && v.length > 0 || "Select at least one scope", "validate")
          })
        },
        scope.value,
        false,
        {
          fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
          lineNumber: 150,
          columnNumber: 21
        },
        this
      )),
      errors.scopes && /* @__PURE__ */ jsxDEV("div", { id: "oauth2client_scopes_error", className: "text-danger small mt-1", children: errors.scopes.message }, void 0, false, {
        fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
        lineNumber: 162,
        columnNumber: 21
      }, this)
    ] }, void 0, true, {
      fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
      lineNumber: 144,
      columnNumber: 13
    }, this),
    errors.root?.server && /* @__PURE__ */ jsxDEV("div", { role: "alert", className: "alert alert-danger", children: errors.root.server.message }, void 0, false, {
      fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
      lineNumber: 168,
      columnNumber: 17
    }, this),
    /* @__PURE__ */ jsxDEV("p", { children: [
      "Dirty: ",
      String(isDirty)
    ] }, void 0, true, {
      fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
      lineNumber: 171,
      columnNumber: 13
    }, this),
    /* @__PURE__ */ jsxDEV(Button, { type: "submit", label: isSubmitting ? "Saving\u2026" : "Save", size: "lg", disabled: isSubmitting }, void 0, false, {
      fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
      lineNumber: 172,
      columnNumber: 13
    }, this)
  ] }, void 0, true, {
    fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
    lineNumber: 93,
    columnNumber: 9
  }, this);
}
__name(ClientForm, "ClientForm");
export {
  ClientForm as default
};
//# sourceMappingURL=clientForm.dev.js.map
