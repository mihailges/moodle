var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
import { registerFormExtension } from "@moodle/lms/core/forms";
import { TextField, SwitchField } from "@moodle/lms/core/forms/fields";
import { required, pattern, validIf } from "@moodle/lms/core/forms/rules";
const MAX_LIMIT = 10;
function GeneralExtension() {
  return /* @__PURE__ */ jsxDEV(Fragment, { children: [
    /* @__PURE__ */ jsxDEV(
      TextField,
      {
        name: "extensions.local_formtest.reference",
        label: "Reference (plugin)",
        rules: [required("The reference is required"), pattern(/^[A-Z]{3}-\d{3}$/, "Use a format like ABC-123")]
      },
      void 0,
      false,
      {
        fileName: "public/local/formtest/js/esm/src/extension.tsx",
        lineNumber: 10,
        columnNumber: 13
      },
      this
    ),
    /* @__PURE__ */ jsxDEV(
      TextField,
      {
        name: "extensions.local_formtest.limit",
        label: "Limit (plugin)",
        rules: [validIf((limit) => Number(limit) <= MAX_LIMIT, `The limit cannot be more than ${MAX_LIMIT}`)]
      },
      void 0,
      false,
      {
        fileName: "public/local/formtest/js/esm/src/extension.tsx",
        lineNumber: 15,
        columnNumber: 13
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/local/formtest/js/esm/src/extension.tsx",
    lineNumber: 9,
    columnNumber: 9
  }, this);
}
__name(GeneralExtension, "GeneralExtension");
function FooterExtension() {
  return /* @__PURE__ */ jsxDEV(SwitchField, { name: "extensions.local_formtest.sendcopy", label: "Send me a copy (plugin)" }, void 0, false, {
    fileName: "public/local/formtest/js/esm/src/extension.tsx",
    lineNumber: 25,
    columnNumber: 12
  }, this);
}
__name(FooterExtension, "FooterExtension");
registerFormExtension({ slot: "core_test_form.general", plugin: "local_formtest", component: GeneralExtension });
registerFormExtension({ slot: "core_test_form.footer", plugin: "local_formtest", component: FooterExtension });
//# sourceMappingURL=extension.dev.js.map
