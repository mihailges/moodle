var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useContext } from "react";
import { Button } from "@moodlehq/design-system";
import { FormActionContext } from "../context";
function ActionButtons({ children }) {
  return /* @__PURE__ */ jsxDEV("div", { className: "d-flex gap-2 mt-3", children }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/fields/ActionButtons.tsx",
    lineNumber: 7,
    columnNumber: 12
  }, this);
}
__name(ActionButtons, "ActionButtons");
function SubmitButton({ label, savingLabel = "Saving\u2026" }) {
  const api = useContext(FormActionContext);
  return /* @__PURE__ */ jsxDEV(Button, { type: "submit", label: api?.submitting ? savingLabel : label, size: "lg", disabled: api?.submitting }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/fields/ActionButtons.tsx",
    lineNumber: 12,
    columnNumber: 12
  }, this);
}
__name(SubmitButton, "SubmitButton");
function CancelButton({ label, onClick }) {
  const api = useContext(FormActionContext);
  return /* @__PURE__ */ jsxDEV(Button, { type: "button", label, variant: "secondary", size: "lg", disabled: api?.submitting, onClick }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/fields/ActionButtons.tsx",
    lineNumber: 17,
    columnNumber: 12
  }, this);
}
__name(CancelButton, "CancelButton");
function ActionButton({ label, variant = "secondary", submit, onClick }) {
  const api = useContext(FormActionContext);
  const run = /* @__PURE__ */ __name(() => {
    if (submit) {
      return api?.submit(typeof submit === "object" ? submit : void 0);
    }
    return api && onClick?.(api);
  }, "run");
  return /* @__PURE__ */ jsxDEV(Button, { type: "button", label, variant, size: "lg", disabled: api?.submitting, onClick: run }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/fields/ActionButtons.tsx",
    lineNumber: 45,
    columnNumber: 12
  }, this);
}
__name(ActionButton, "ActionButton");
export {
  ActionButton,
  ActionButtons,
  CancelButton,
  SubmitButton
};
//# sourceMappingURL=ActionButtons.dev.js.map
