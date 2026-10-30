var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { Input } from "@moodlehq/design-system";
import { useDefinedField } from "../useDefinedField";
function TextField({ name, label, rules, hideIf, disableIf, ...rest }) {
  const f = useDefinedField(name, { rules, hideIf, disableIf });
  return f.hidden ? null : /* @__PURE__ */ jsxDEV(Input, { label, ...rest, ...f.registration, invalid: f.invalid, invalidFeedback: f.message }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/fields/TextField.tsx",
    lineNumber: 8,
    columnNumber: 9
  }, this);
}
__name(TextField, "TextField");
export {
  TextField
};
//# sourceMappingURL=TextField.dev.js.map
