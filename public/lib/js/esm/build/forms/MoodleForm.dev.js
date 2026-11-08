var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useEffect, useMemo, useRef, useState, Children } from "react";
import { useForm, FormProvider } from "react-hook-form";
import { getString } from "@moodle/lms/core/stringUtils";
import { FormStateContext, FieldRegistry, FieldRegistryContext, FormActionContext } from "./context";
import { ActionButtons, SubmitButton, CancelButton } from "./fields/ActionButtons";
import { isFormValidationError } from "./FormValidationError";
import { formApi } from "./restApi";
function MoodleForm({
  defaultValues,
  endpoint,
  resourceId,
  load,
  submit,
  cancel,
  toForm,
  toPayload,
  mapErrorPath = /* @__PURE__ */ __name((p) => p, "mapErrorPath"),
  children
}) {
  const rest = useMemo(() => endpoint ? formApi(endpoint) : null, [endpoint]);
  const form = useForm({ defaultValues: defaultValues ?? {} });
  const { reset, setError, handleSubmit, formState: { errors, isSubmitting } } = form;
  const [status, setStatus] = useState(
    resourceId !== void 0 && (load || rest) ? "loading" : "ready"
  );
  const registry = useRef(new FieldRegistry()).current;
  const [serverDisabled, setServerDisabled] = useState([]);
  const [strings, setStrings] = useState({ save: "Save", cancel: "Cancel", error: "Something went wrong. Try again." });
  useEffect(() => {
    Promise.all([getString("save"), getString("cancel"), getString("unknownerror")]).then(([save, cancelLabel, error]) => setStrings({ save, cancel: cancelLabel, error })).catch(() => void 0);
  }, []);
  const asForm = /* @__PURE__ */ __name((payload) => toForm ? toForm(payload) : payload, "asForm");
  const asPayload = /* @__PURE__ */ __name((values) => toPayload ? toPayload(values) : values, "asPayload");
  useEffect(() => {
    const fromApi = /* @__PURE__ */ __name(async (key) => ({ values: await rest.get(key) }), "fromApi");
    const loader = load ?? (rest ? fromApi : null);
    if (resourceId === void 0 || !loader) {
      return;
    }
    let cancelled = false;
    setStatus("loading");
    loader(resourceId).then((record) => {
      if (cancelled) {
        return;
      }
      reset({ ...defaultValues, ...asForm(record.values) });
      setServerDisabled(record.readonly ?? []);
      setStatus("ready");
    }).catch(() => !cancelled && setStatus("error"));
    return () => {
      cancelled = true;
    };
  }, [resourceId]);
  const state = useMemo(() => ({ serverDisabled }), [serverDisabled]);
  const onSubmit = /* @__PURE__ */ __name(async (values, override) => {
    const action = override?.action ?? submit?.action ?? (rest ? (payload) => resourceId !== void 0 ? rest.update(resourceId, payload) : rest.create(payload) : void 0);
    if (!action) {
      return;
    }
    try {
      const payload = asPayload(registry.unwrap(values));
      const saved = await action(payload);
      if (resourceId !== void 0) {
        reset(registry.wrap(asForm(saved)));
      }
      (override?.onSuccess ?? submit?.onSuccess)?.(saved);
    } catch (e) {
      if (!isFormValidationError(e)) {
        setError("root.server", { message: strings.error });
        return;
      }
      Object.entries(e.fieldErrors).forEach(([path, messages], i) => {
        const field = registry.resolve(mapErrorPath(path));
        if (field) {
          setError(field, { type: "server", message: messages[0] }, { shouldFocus: i === 0 });
        } else {
          setError("root.server", { message: messages[0] });
        }
      });
      e.formErrors.forEach((message) => setError("root.server", { message }));
    }
  }, "onSubmit");
  const actionApi = {
    submit: /* @__PURE__ */ __name((override) => handleSubmit((values) => onSubmit(values, override))(), "submit"),
    submitting: isSubmitting,
    getValues: /* @__PURE__ */ __name(() => registry.unwrap(form.getValues()), "getValues"),
    setValue: /* @__PURE__ */ __name((name, value) => form.setValue(name, value, { shouldDirty: true }), "setValue")
  };
  if (status === "loading") {
    return /* @__PURE__ */ jsxDEV("p", { children: "Loading..." }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/MoodleForm.tsx",
      lineNumber: 131,
      columnNumber: 16
    }, this);
  }
  if (status === "error") {
    return /* @__PURE__ */ jsxDEV("div", { role: "alert", className: "alert alert-danger", children: "This record could not be loaded." }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/MoodleForm.tsx",
      lineNumber: 134,
      columnNumber: 16
    }, this);
  }
  return /* @__PURE__ */ jsxDEV(FormProvider, { ...form, children: /* @__PURE__ */ jsxDEV(FieldRegistryContext.Provider, { value: registry, children: /* @__PURE__ */ jsxDEV(FormStateContext.Provider, { value: state, children: /* @__PURE__ */ jsxDEV(FormActionContext.Provider, { value: actionApi, children: /* @__PURE__ */ jsxDEV("form", { onSubmit: handleSubmit((values) => onSubmit(values)), noValidate: true, children: [
    Children.map(children, (child) => /* @__PURE__ */ jsxDEV("div", { className: "mt-3", children: child }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/MoodleForm.tsx",
      lineNumber: 144,
      columnNumber: 25
    }, this)),
    errors.root?.server && /* @__PURE__ */ jsxDEV("div", { role: "alert", className: "alert alert-danger", children: errors.root.server.message }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/MoodleForm.tsx",
      lineNumber: 149,
      columnNumber: 25
    }, this),
    (submit || rest || cancel) && /* @__PURE__ */ jsxDEV(ActionButtons, { children: [
      (submit || rest) && /* @__PURE__ */ jsxDEV(SubmitButton, { label: submit?.label ?? strings.save, savingLabel: submit?.savingLabel }, void 0, false, {
        fileName: "public/lib/js/esm/src/forms/MoodleForm.tsx",
        lineNumber: 154,
        columnNumber: 33
      }, this),
      cancel && /* @__PURE__ */ jsxDEV(CancelButton, { label: cancel.label ?? strings.cancel, onClick: cancel.action }, void 0, false, {
        fileName: "public/lib/js/esm/src/forms/MoodleForm.tsx",
        lineNumber: 156,
        columnNumber: 40
      }, this)
    ] }, void 0, true, {
      fileName: "public/lib/js/esm/src/forms/MoodleForm.tsx",
      lineNumber: 152,
      columnNumber: 25
    }, this)
  ] }, void 0, true, {
    fileName: "public/lib/js/esm/src/forms/MoodleForm.tsx",
    lineNumber: 142,
    columnNumber: 17
  }, this) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/MoodleForm.tsx",
    lineNumber: 141,
    columnNumber: 17
  }, this) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/MoodleForm.tsx",
    lineNumber: 140,
    columnNumber: 13
  }, this) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/MoodleForm.tsx",
    lineNumber: 139,
    columnNumber: 13
  }, this) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/MoodleForm.tsx",
    lineNumber: 138,
    columnNumber: 9
  }, this);
}
__name(MoodleForm, "MoodleForm");
export {
  MoodleForm
};
//# sourceMappingURL=MoodleForm.dev.js.map
