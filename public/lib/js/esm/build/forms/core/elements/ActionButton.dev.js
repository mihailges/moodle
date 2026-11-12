var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useContext } from "react";
import { Button } from "@moodlehq/design-system";
import { FormActionContext } from "../formContext";
function ActionButton({ label, variant = "secondary", inProgressLabel, submit, formSubmit, onClick }) {
  const api = useContext(FormActionContext);
  const run = /* @__PURE__ */ __name(() => {
    if (submit) {
      return api?.submit(submit);
    }
    return api && onClick?.(api);
  }, "run");
  const submitting = !!api?.submitting;
  return /* @__PURE__ */ jsxDEV(
    Button,
    {
      type: formSubmit ? "submit" : "button",
      label: submitting && (submit || formSubmit) && inProgressLabel ? inProgressLabel : label,
      variant,
      size: "lg",
      "aria-disabled": submitting || void 0,
      className: submitting ? "disabled" : void 0,
      onClick: (e) => {
        if (submitting) {
          e.preventDefault();
        } else if (!formSubmit) {
          run();
        }
      }
    },
    void 0,
    false,
    {
      fileName: "public/lib/js/esm/src/forms/core/elements/ActionButton.tsx",
      lineNumber: 34,
      columnNumber: 9
    },
    this
  );
}
__name(ActionButton, "ActionButton");
export {
  ActionButton
};
//# sourceMappingURL=ActionButton.dev.js.map
