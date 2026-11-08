var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { Suspense, use } from "react";
import { getString } from "@moodle/lms/core/stringUtils";
import { MoodleForm, FormSlot, ExtensionModules } from "@moodle/lms/core/forms";
import {
  GroupField,
  CheckboxField,
  RepeaterField,
  TextField,
  TextareaField
} from "@moodle/lms/core/forms/fields";
import { required, maxLength, url, minItems } from "@moodle/lms/core/forms/rules";
const NAME_MAX_LENGTH = 255;
function EditClientForm({
  clientId,
  hasAuthCode,
  isConfidential,
  scopes,
  listUrl,
  extensionModules
}) {
  const s = {
    name: getString("name"),
    nameRequired: getString("required"),
    nameTooLong: getString("maximumchars", "core", NAME_MAX_LENGTH),
    description: getString("description"),
    redirectUris: getString("oauth2server_clientcallbackuris", "admin"),
    redirectUriRequired: getString("oauth2server_clientcallbackurirequired", "admin"),
    redirectUriInvalid: getString("oauth2server_clientcallbackurisinvalid", "admin"),
    addRedirectUri: getString("oauth2server_clientaddcallbackuri", "admin"),
    remove: getString("remove"),
    pkce: getString("oauth2server_clientpkcerequired", "admin"),
    pkceHelp: getString("oauth2server_clientpkcehelpconfidential", "admin"),
    scopes: getString("oauth2server_scopes", "admin"),
    scopesRequired: getString("oauth2serverclientnoscopes", "error")
  };
  const redirectUris = use(s.redirectUris);
  const redirectUriRequired = use(s.redirectUriRequired);
  const redirectUriInvalid = use(s.redirectUriInvalid);
  const scopesRequired = use(s.scopesRequired);
  return /* @__PURE__ */ jsxDEV(ExtensionModules, { modules: extensionModules, children: /* @__PURE__ */ jsxDEV(
    MoodleForm,
    {
      endpoint: "/admin/oauth2/server/clients",
      resourceId: clientId,
      submit: {
        onSuccess: /* @__PURE__ */ __name(() => {
          window.location.href = listUrl;
        }, "onSuccess")
      },
      cancel: { action: /* @__PURE__ */ __name(() => {
        window.location.href = listUrl;
      }, "action") },
      children: [
        /* @__PURE__ */ jsxDEV(
          TextField,
          {
            name: "name",
            label: use(s.name),
            rules: [required(use(s.nameRequired)), maxLength(NAME_MAX_LENGTH, use(s.nameTooLong))]
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/editClientForm.tsx",
            lineNumber: 75,
            columnNumber: 13
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(TextareaField, { name: "description", label: use(s.description) }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/editClientForm.tsx",
          lineNumber: 80,
          columnNumber: 13
        }, this),
        /* @__PURE__ */ jsxDEV(FormSlot, { name: "core_admin_oauth2_client.general" }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/editClientForm.tsx",
          lineNumber: 81,
          columnNumber: 13
        }, this),
        hasAuthCode && /* @__PURE__ */ jsxDEV(
          RepeaterField,
          {
            name: "redirecturis",
            rules: [minItems(1, redirectUriRequired)],
            label: redirectUris,
            addLabel: use(s.addRedirectUri),
            removeLabel: use(s.remove),
            children: /* @__PURE__ */ jsxDEV(TextField, { label: redirectUris, hideLabel: true, rules: [url(redirectUriInvalid)] }, void 0, false, {
              fileName: "public/admin/js/esm/src/oauth2/editClientForm.tsx",
              lineNumber: 90,
              columnNumber: 21
            }, this)
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/editClientForm.tsx",
            lineNumber: 83,
            columnNumber: 17
          },
          this
        ),
        hasAuthCode && isConfidential && /* @__PURE__ */ jsxDEV(CheckboxField, { name: "ispkcerequired", label: use(s.pkce), supportingText: use(s.pkceHelp) }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/editClientForm.tsx",
          lineNumber: 94,
          columnNumber: 17
        }, this),
        /* @__PURE__ */ jsxDEV(
          GroupField,
          {
            name: "scopes",
            label: use(s.scopes),
            layout: "column",
            rules: [minItems(1, scopesRequired)],
            children: scopes.map((scope) => /* @__PURE__ */ jsxDEV(
              CheckboxField,
              {
                name: "scopes",
                value: scope.value,
                label: scope.label,
                supportingText: scope.description
              },
              scope.value,
              false,
              {
                fileName: "public/admin/js/esm/src/oauth2/editClientForm.tsx",
                lineNumber: 103,
                columnNumber: 21
              },
              this
            ))
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/editClientForm.tsx",
            lineNumber: 96,
            columnNumber: 13
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(FormSlot, { name: "core_admin_oauth2_client.footer" }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/editClientForm.tsx",
          lineNumber: 112,
          columnNumber: 13
        }, this)
      ]
    },
    void 0,
    true,
    {
      fileName: "public/admin/js/esm/src/oauth2/editClientForm.tsx",
      lineNumber: 63,
      columnNumber: 9
    },
    this
  ) }, void 0, false, {
    fileName: "public/admin/js/esm/src/oauth2/editClientForm.tsx",
    lineNumber: 62,
    columnNumber: 9
  }, this);
}
__name(EditClientForm, "EditClientForm");
function EditClientFormLoader(props) {
  return /* @__PURE__ */ jsxDEV(Suspense, { fallback: null, children: /* @__PURE__ */ jsxDEV(EditClientForm, { ...props }, void 0, false, {
    fileName: "public/admin/js/esm/src/oauth2/editClientForm.tsx",
    lineNumber: 122,
    columnNumber: 13
  }, this) }, void 0, false, {
    fileName: "public/admin/js/esm/src/oauth2/editClientForm.tsx",
    lineNumber: 121,
    columnNumber: 9
  }, this);
}
__name(EditClientFormLoader, "EditClientFormLoader");
export {
  EditClientFormLoader as default
};
//# sourceMappingURL=editClientForm.dev.js.map
