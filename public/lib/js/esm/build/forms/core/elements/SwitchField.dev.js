var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { Switch } from "@moodlehq/design-system";
import { useFormField } from "../formField";
function SwitchField({ name, label, rules, hideIf, disableIf, defaultIf, advanced, ...rest }) {
  const f = useFormField(name, { rules, hideIf, disableIf, defaultIf, advanced });
  return f.hidden ? null : /* @__PURE__ */ jsxDEV(Switch, { label, ...rest, ...f.registration }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/elements/SwitchField.tsx",
    lineNumber: 6,
    columnNumber: 30
  }, this);
}
__name(SwitchField, "SwitchField");
export {
  SwitchField
};
//# sourceMappingURL=SwitchField.dev.js.map
