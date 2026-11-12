var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useCallback, useEffect, useMemo, useRef, useState, Children } from "react";
import { useForm, FormProvider } from "react-hook-form";
import { getString } from "@moodle/lms/core/stringUtils";
import {
  FormStateContext,
  FieldRegistry,
  FieldRegistryContext,
  FormActionContext,
  AdvancedContext
} from "./formContext";
import { ActionButton } from "./elements/ActionButton";
import { isFormValidationError } from "./FormValidationError";
function MoodleForm(props) {
  const { submit, cancel, toForm, toPayload, mapErrorPath = /* @__PURE__ */ __name((p) => p, "mapErrorPath"), children } = props;
  const loader = typeof props.defaultValues === "function" ? props.defaultValues : null;
  const defaultValues = loader ? void 0 : props.defaultValues;
  const form = useForm({ defaultValues: defaultValues ?? {}, mode: "onTouched", shouldFocusError: false });
  const { reset, setError, handleSubmit, formState: { errors, isSubmitting } } = form;
  const [status, setStatus] = useState(loader ? "loading" : "ready");
  const registry = useRef(new FieldRegistry()).current;
  const [serverDisabled, setServerDisabled] = useState([]);
  const [showAdvanced, setShowAdvanced] = useState(false);
  const [advancedCount, setAdvancedCount] = useState(0);
  const registerAdvanced = useCallback(() => {
    setAdvancedCount((count) => count + 1);
    return () => setAdvancedCount((count) => count - 1);
  }, []);
  const advanced = useMemo(() => ({ show: showAdvanced, register: registerAdvanced }), [showAdvanced, registerAdvanced]);
  const [strings, setStrings] = useState({
    save: "Save",
    cancel: "Cancel",
    error: "Something went wrong. Try again.",
    showMore: "Show more",
    showLess: "Show less"
  });
  useEffect(() => {
    Promise.all([
      getString("save"),
      getString("cancel"),
      getString("unknownerror"),
      getString("showmore"),
      getString("showless")
    ]).then(([save, cancelLabel, error, showMore, showLess]) => {
      setStrings({ save, cancel: cancelLabel, error, showMore, showLess });
    }).catch(() => void 0);
  }, []);
  const asForm = /* @__PURE__ */ __name((payload) => toForm ? toForm(payload) : payload, "asForm");
  const asPayload = /* @__PURE__ */ __name((values) => toPayload ? toPayload(values) : values, "asPayload");
  useEffect(() => {
    if (!loader) {
      return;
    }
    let cancelled = false;
    setStatus("loading");
    loader().then((record) => {
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
  }, []);
  const state = useMemo(() => ({ serverDisabled }), [serverDisabled]);
  const formElement = useRef(null);
  const focusPending = useRef(false);
  const focusFirstInvalid = /* @__PURE__ */ __name(() => {
    focusPending.current = true;
    setTimeout(() => {
      focusPending.current = false;
    }, 500);
  }, "focusFirstInvalid");
  useEffect(() => {
    if (!focusPending.current) {
      return;
    }
    const invalid = formElement.current?.querySelector('[aria-invalid="true"], [data-invalid]');
    if (invalid) {
      focusPending.current = false;
      invalid.focus();
    }
  });
  const onSubmit = /* @__PURE__ */ __name(async (values, override) => {
    const action = override?.action ?? submit?.action;
    if (!action) {
      return;
    }
    try {
      const payload = asPayload(registry.unwrap(values));
      const saved = await action(payload);
      if (loader) {
        reset(registry.wrap(asForm(saved)));
      }
      (override ? override.onSuccess : submit?.onSuccess)?.(saved);
    } catch (e) {
      if (!isFormValidationError(e)) {
        setError("root.server", { message: strings.error });
        return;
      }
      Object.entries(e.fieldErrors).forEach(([path, messages]) => {
        const field = registry.resolve(mapErrorPath(path));
        if (field) {
          setError(field, { type: "server", message: messages[0] });
        } else {
          setError("root.server", { message: messages[0] });
        }
      });
      e.formErrors.forEach((message) => setError("root.server", { message }));
      focusFirstInvalid();
    }
  }, "onSubmit");
  const actionApi = {
    submit: /* @__PURE__ */ __name((override) => handleSubmit((values) => onSubmit(values, override), focusFirstInvalid)(), "submit"),
    submitting: isSubmitting,
    getValues: /* @__PURE__ */ __name(() => registry.unwrap(form.getValues()), "getValues"),
    setValue: /* @__PURE__ */ __name((name, value) => form.setValue(name, value, { shouldDirty: true }), "setValue")
  };
  if (status === "loading") {
    return /* @__PURE__ */ jsxDEV("p", { role: "status", children: "Loading..." }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
      lineNumber: 160,
      columnNumber: 16
    }, this);
  }
  if (status === "error") {
    return /* @__PURE__ */ jsxDEV("div", { role: "alert", className: "alert alert-danger", children: "This record could not be loaded." }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
      lineNumber: 163,
      columnNumber: 16
    }, this);
  }
  return /* @__PURE__ */ jsxDEV(FormProvider, { ...form, children: /* @__PURE__ */ jsxDEV(FieldRegistryContext.Provider, { value: registry, children: /* @__PURE__ */ jsxDEV(FormStateContext.Provider, { value: state, children: /* @__PURE__ */ jsxDEV(FormActionContext.Provider, { value: actionApi, children: /* @__PURE__ */ jsxDEV(AdvancedContext.Provider, { value: advanced, children: /* @__PURE__ */ jsxDEV("form", { ref: formElement, onSubmit: handleSubmit((values) => onSubmit(values), focusFirstInvalid), noValidate: true, children: [
    Children.map(children, (child) => /* @__PURE__ */ jsxDEV("div", { className: "mt-4", children: child }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
      lineNumber: 174,
      columnNumber: 25
    }, this)),
    advancedCount > 0 && /* @__PURE__ */ jsxDEV(
      "button",
      {
        type: "button",
        className: "btn btn-link px-0 mt-4",
        "aria-expanded": showAdvanced,
        onClick: () => setShowAdvanced(!showAdvanced),
        children: showAdvanced ? strings.showLess : strings.showMore
      },
      void 0,
      false,
      {
        fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
        lineNumber: 179,
        columnNumber: 25
      },
      this
    ),
    errors.root?.server && /* @__PURE__ */ jsxDEV("div", { role: "alert", className: "alert alert-danger", children: errors.root.server.message }, void 0, false, {
      fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
      lineNumber: 185,
      columnNumber: 25
    }, this),
    (submit || cancel) && /* @__PURE__ */ jsxDEV("div", { className: "d-flex gap-2 mt-4", children: [
      submit && /* @__PURE__ */ jsxDEV(
        ActionButton,
        {
          label: submit.label ?? strings.save,
          variant: "primary",
          inProgressLabel: submit.inProgressLabel ?? "Saving\u2026",
          formSubmit: true
        },
        void 0,
        false,
        {
          fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
          lineNumber: 190,
          columnNumber: 33
        },
        this
      ),
      cancel && /* @__PURE__ */ jsxDEV(ActionButton, { label: cancel.label ?? strings.cancel, onClick: cancel.action }, void 0, false, {
        fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
        lineNumber: 198,
        columnNumber: 33
      }, this)
    ] }, void 0, true, {
      fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
      lineNumber: 188,
      columnNumber: 25
    }, this)
  ] }, void 0, true, {
    fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
    lineNumber: 172,
    columnNumber: 17
  }, this) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
    lineNumber: 171,
    columnNumber: 17
  }, this) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
    lineNumber: 170,
    columnNumber: 17
  }, this) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
    lineNumber: 169,
    columnNumber: 13
  }, this) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
    lineNumber: 168,
    columnNumber: 13
  }, this) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/MoodleForm.tsx",
    lineNumber: 167,
    columnNumber: 9
  }, this);
}
__name(MoodleForm, "MoodleForm");
export {
  MoodleForm
};
//# sourceMappingURL=MoodleForm.dev.js.map
