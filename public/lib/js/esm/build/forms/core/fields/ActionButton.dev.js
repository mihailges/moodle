var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useContext } from "react";
import { Button } from "@moodlehq/design-system";
import { FormActionContext } from "../context";
function ActionButton({ label, variant = "secondary", inProgressLabel, submit, onClick }) {
  const api = useContext(FormActionContext);
  const run = /* @__PURE__ */ __name(() => {
    if (submit) {
      return api?.submit(typeof submit === "object" ? submit : void 0);
    }
    return api && onClick?.(api);
  }, "run");
  const submitting = !!api?.submitting;
  return /* @__PURE__ */ jsxDEV(
    Button,
    {
      type: submit === true ? "submit" : "button",
      label: submitting && submit && inProgressLabel ? inProgressLabel : label,
      variant,
      size: "lg",
      disabled: submitting,
      onClick: submit === true ? void 0 : run
    },
    void 0,
    false,
    {
      fileName: "public/lib/js/esm/src/forms/core/fields/ActionButton.tsx",
      lineNumber: 32,
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
