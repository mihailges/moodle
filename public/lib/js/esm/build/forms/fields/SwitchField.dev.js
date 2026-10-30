var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { Switch } from "@moodlehq/design-system";
import { useDefinedField } from "../useDefinedField";
function SwitchField({ name, label, rules, hideIf, disableIf, ...rest }) {
  const f = useDefinedField(name, { rules, hideIf, disableIf });
  return f.hidden ? null : /* @__PURE__ */ jsxDEV(Switch, { label, ...rest, ...f.registration }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/fields/SwitchField.tsx",
    lineNumber: 7,
    columnNumber: 30
  }, this);
}
__name(SwitchField, "SwitchField");
export {
  SwitchField
};
//# sourceMappingURL=SwitchField.dev.js.map
