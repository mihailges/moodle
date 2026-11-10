var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { Textarea } from "@moodlehq/design-system";
import { useMoodleFormField } from "../useMoodleFormField";
function TextareaField({ name, label, rules, hideIf, disableIf, advanced, ...rest }) {
  const f = useMoodleFormField(name, { rules, hideIf, disableIf, advanced });
  return f.hidden ? null : /* @__PURE__ */ jsxDEV(Textarea, { label, required: f.required, ...rest, ...f.registration }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/fields/TextareaField.tsx",
    lineNumber: 7,
    columnNumber: 30
  }, this);
}
__name(TextareaField, "TextareaField");
export {
  TextareaField
};
//# sourceMappingURL=TextareaField.dev.js.map
