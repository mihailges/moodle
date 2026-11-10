var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { Input } from "@moodlehq/design-system";
import { useFormField } from "../formField";
function TextField({ name, label, rules, hideIf, disableIf, defaultIf, advanced, ...rest }) {
  const f = useFormField(name, { rules, hideIf, disableIf, defaultIf, advanced });
  return f.hidden ? null : /* @__PURE__ */ jsxDEV(
    Input,
    {
      label,
      required: f.required,
      ...rest,
      ...f.registration,
      invalid: f.invalid,
      invalidFeedback: f.message
    },
    void 0,
    false,
    {
      fileName: "public/lib/js/esm/src/forms/core/elements/TextField.tsx",
      lineNumber: 7,
      columnNumber: 9
    },
    this
  );
}
__name(TextField, "TextField");
export {
  TextField
};
//# sourceMappingURL=TextField.dev.js.map
