var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useEffect, useState } from "react";
import { DefinedForm } from "@moodle/lms/core/forms";
import { TextField, TextareaField, SwitchField, CheckboxGroupField, RepeaterField } from "@moodle/lms/core/forms/fields";
import { required, maxLength, url, minItems } from "@moodle/lms/core/forms/rules";
import { checked } from "@moodle/lms/core/forms/conditions";
import {
  getScopes,
  createClient,
  getClient,
  patchClient
} from "@moodle/lms/core_admin/oauth2/clientApi";
function ClientForm({ clientId }) {
  const [scopeOptions, setScopeOptions] = useState(null);
  useEffect(() => {
    getScopes().then(setScopeOptions);
  }, []);
  return /* @__PURE__ */ jsxDEV(
    DefinedForm,
    {
      defaultValues: { name: "Test client", description: "", ispkcerequired: false, scopes: [], redirecturis: [""] },
      id: clientId,
      load: getClient,
      create: createClient,
      update: patchClient,
      children: [
        /* @__PURE__ */ jsxDEV(
          TextField,
          {
            name: "name",
            label: "Name",
            rules: [required("Enter a client name"), maxLength(15, "Name must be 15 characters or fewer")]
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
            lineNumber: 25,
            columnNumber: 13
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(TextareaField, { name: "description", label: "Description" }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
          lineNumber: 30,
          columnNumber: 13
        }, this),
        /* @__PURE__ */ jsxDEV(SwitchField, { name: "ispkcerequired", label: "Is PKCE required" }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
          lineNumber: 31,
          columnNumber: 13
        }, this),
        /* @__PURE__ */ jsxDEV(
          RepeaterField,
          {
            name: "redirecturis",
            label: "Redirect URIs",
            addLabel: "Add redirect URI",
            hideIf: checked("ispkcerequired"),
            rules: [minItems(1, "Enter at least one redirect URI")],
            children: (index) => /* @__PURE__ */ jsxDEV(
              TextField,
              {
                id: `oauth2client_redirecturis_${index}`,
                name: `redirecturis.${index}`,
                label: `Redirect URI ${index + 1}`,
                hideLabel: true,
                rules: [required("Enter a redirect URI"), url("Enter a valid URL")]
              },
              void 0,
              false,
              {
                fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
                lineNumber: 41,
                columnNumber: 21
              },
              this
            )
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
            lineNumber: 33,
            columnNumber: 13
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(
          CheckboxGroupField,
          {
            name: "scopes",
            label: "Scopes",
            options: scopeOptions,
            rules: [minItems(1, "Select at least one scope")]
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
            lineNumber: 51,
            columnNumber: 13
          },
          this
        )
      ]
    },
    void 0,
    true,
    {
      fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
      lineNumber: 18,
      columnNumber: 9
    },
    this
  );
}
__name(ClientForm, "ClientForm");
export {
  ClientForm as default
};
//# sourceMappingURL=clientForm.dev.js.map
