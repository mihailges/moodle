var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useEffect, useMemo, useState } from "react";
import { useForm, FormProvider } from "react-hook-form";
import { Button } from "@moodlehq/design-system";
import { FormStateContext } from "./context";
import { isFormValidationError } from "./FormValidationError";
function DefinedForm({
  defaultValues,
  id,
  load,
  create,
  update,
  toForm,
  toPayload,
  mapErrorPath = /* @__PURE__ */ __name((p) => p, "mapErrorPath"),
  onSaved,
  children
}) {
  const form = useForm({ defaultValues, shouldUnregister: true });
  const { reset, setError, handleSubmit, formState: { dirtyFields, errors, isSubmitting } } = form;
  const [status, setStatus] = useState(id !== void 0 && load ? "loading" : "ready");
  const [serverDisabled, setServerDisabled] = useState([]);
  const asForm = /* @__PURE__ */ __name((payload) => toForm ? toForm(payload) : payload, "asForm");
  const asPayload = /* @__PURE__ */ __name((values) => toPayload ? toPayload(values) : values, "asPayload");
  useEffect(() => {
    if (id === void 0 || !load) {
      return;
    }
    let cancelled = false;
    setStatus("loading");
    load(id).then((record) => {
      if (cancelled) {
        return;
      }
      reset(asForm(record.values));
      setServerDisabled(record.readonly ?? []);
      setStatus("ready");
    }).catch(() => !cancelled && setStatus("error"));
    return () => {
      cancelled = true;
    };
  }, [id]);
  const state = useMemo(() => ({ serverDisabled }), [serverDisabled]);
  const onSubmit = /* @__PURE__ */ __name(async (values) => {
    try {
      const payload = asPayload(values);
      let saved;
      if (id !== void 0 && update) {
        const changes = Object.fromEntries(
          Object.keys(dirtyFields).map((key) => [key, payload[key]])
        );
        if (Object.keys(changes).length === 0) {
          return;
        }
        saved = await update(id, changes);
        reset(asForm(saved));
      } else {
        saved = await create(payload);
      }
      onSaved?.(saved);
    } catch (e) {
      if (!isFormValidationError(e)) {
        setError("root.server", { message: "Something went wrong. Try again." });
        return;
      }
      Object.entries(e.fieldErrors).forEach(([path, messages], i) => {
        setError(mapErrorPath(path), { type: "server", message: messages[0] }, { shouldFocus: i === 0 });
      });
      e.formErrors.forEach((message) => setError("root.server", { message }));
    }
  }, "onSubmit");
  if (status === "loading") {
    return /* @__PURE__ */ jsxDEV("p", { children: "Loading..." }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/DefinedForm.tsx",
      lineNumber: 93,
      columnNumber: 16
    }, this);
  }
  if (status === "error") {
    return /* @__PURE__ */ jsxDEV("div", { role: "alert", className: "alert alert-danger", children: "This record could not be loaded." }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/DefinedForm.tsx",
      lineNumber: 96,
      columnNumber: 16
    }, this);
  }
  return /* @__PURE__ */ jsxDEV(FormProvider, { ...form, children: /* @__PURE__ */ jsxDEV(FormStateContext.Provider, { value: state, children: /* @__PURE__ */ jsxDEV("form", { onSubmit: handleSubmit(onSubmit), noValidate: true, children: [
    children,
    errors.root?.server && /* @__PURE__ */ jsxDEV("div", { role: "alert", className: "alert alert-danger", children: errors.root.server.message }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/DefinedForm.tsx",
      lineNumber: 105,
      columnNumber: 25
    }, this),
    /* @__PURE__ */ jsxDEV(Button, { type: "submit", label: isSubmitting ? "Saving\u2026" : "Save", size: "lg", disabled: isSubmitting }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/DefinedForm.tsx",
      lineNumber: 107,
      columnNumber: 21
    }, this)
  ] }, void 0, true, {
    fileName: "public/lib/js/esm/src/forms/DefinedForm.tsx",
    lineNumber: 102,
    columnNumber: 17
  }, this) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/DefinedForm.tsx",
    lineNumber: 101,
    columnNumber: 13
  }, this) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/DefinedForm.tsx",
    lineNumber: 100,
    columnNumber: 9
  }, this);
}
__name(DefinedForm, "DefinedForm");
export {
  DefinedForm
};
//# sourceMappingURL=DefinedForm.dev.js.map
