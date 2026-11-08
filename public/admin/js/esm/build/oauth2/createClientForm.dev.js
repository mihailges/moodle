var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { Suspense, use } from "react";
import { Alert } from "@moodlehq/design-system";
import { getString } from "@moodle/lms/core/stringUtils";
import { MoodleForm, FormSlot, ExtensionModules } from "@moodle/lms/core/forms";
import {
  GroupField,
  RadioField,
  CheckboxField,
  RepeaterField,
  StaticField,
  TextField,
  TextareaField
} from "@moodle/lms/core/forms/fields";
import { required, maxLength, url, minItems } from "@moodle/lms/core/forms/rules";
const isPublic = /* @__PURE__ */ __name((v) => v.clienttype === "public", "isPublic");
function CreateClientForm({ scopes, listUrl, extensionModules, extensionValues }) {
  const flowRequired = use(getString("oauth2server_clientmustselectprimaryflow", "admin"));
  const scopesRequired = use(getString("oauth2serverclientnoscopes", "error"));
  const redirectUris = use(getString("oauth2server_clientcallbackuris", "admin"));
  const redirectUriRequired = use(getString("oauth2server_clientcallbackurirequired", "admin"));
  const redirectUriInvalid = use(getString("oauth2server_clientcallbackurisinvalid", "admin"));
  return /* @__PURE__ */ jsxDEV(ExtensionModules, { modules: extensionModules, children: /* @__PURE__ */ jsxDEV(
    MoodleForm,
    {
      defaultValues: {
        name: "",
        description: "",
        clienttype: "confidential",
        flows: ["auth_code"],
        redirecturis: [""],
        ispkcerequired: true,
        scopes: [],
        extensions: extensionValues ?? {}
      },
      endpoint: "/admin/oauth2/server/clients",
      submit: {
        label: use(getString("oauth2server_clientcreate", "admin")),
        onSuccess: /* @__PURE__ */ __name((saved) => {
          window.location.href = saved.redirecturl;
        }, "onSuccess")
      },
      cancel: {
        action: /* @__PURE__ */ __name(() => {
          window.location.href = listUrl;
        }, "action")
      },
      children: [
        /* @__PURE__ */ jsxDEV(
          TextField,
          {
            name: "name",
            label: use(getString("name")),
            rules: [
              required(use(getString("required"))),
              maxLength(255, use(getString("maximumchars", "core", 255)))
            ]
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
            lineNumber: 75,
            columnNumber: 13
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(TextareaField, { name: "description", label: use(getString("description")) }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
          lineNumber: 83,
          columnNumber: 13
        }, this),
        /* @__PURE__ */ jsxDEV(FormSlot, { name: "core_admin_oauth2_client.general" }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
          lineNumber: 84,
          columnNumber: 13
        }, this),
        /* @__PURE__ */ jsxDEV(GroupField, { name: "clienttype", label: use(getString("oauth2server_clienttype", "admin")), layout: "column", children: [
          /* @__PURE__ */ jsxDEV(
            RadioField,
            {
              name: "clienttype",
              value: "confidential",
              label: use(getString("oauth2server_clienttypeconfidential", "admin")),
              supportingText: use(getString("oauth2server_clienttypeconfidentialdesc", "admin"))
            },
            void 0,
            false,
            {
              fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
              lineNumber: 86,
              columnNumber: 17
            },
            this
          ),
          /* @__PURE__ */ jsxDEV(
            RadioField,
            {
              name: "clienttype",
              value: "public",
              label: use(getString("oauth2server_clienttypepublic", "admin")),
              supportingText: use(getString("oauth2server_clienttypepublicdesc", "admin"))
            },
            void 0,
            false,
            {
              fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
              lineNumber: 92,
              columnNumber: 17
            },
            this
          )
        ] }, void 0, true, {
          fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
          lineNumber: 85,
          columnNumber: 13
        }, this),
        /* @__PURE__ */ jsxDEV(
          GroupField,
          {
            name: "flows",
            label: use(getString("oauth2server_clientprimaryflows", "admin")),
            layout: "column",
            disableIf: isPublic,
            rules: [minItems(1, flowRequired)],
            children: [
              /* @__PURE__ */ jsxDEV(
                CheckboxField,
                {
                  name: "flows",
                  value: "auth_code",
                  label: use(getString("oauth2server_clientgranttypeauthcode", "admin")),
                  supportingText: use(getString("oauth2server_clientgranttypeauthcodedesc", "admin"))
                },
                void 0,
                false,
                {
                  fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
                  lineNumber: 106,
                  columnNumber: 17
                },
                this
              ),
              /* @__PURE__ */ jsxDEV(
                CheckboxField,
                {
                  name: "flows",
                  value: "client_credentials",
                  label: use(getString("oauth2server_clientgranttypeclientcreds", "admin")),
                  supportingText: use(getString("oauth2server_clientgranttypeclientcredsdesc", "admin"))
                },
                void 0,
                false,
                {
                  fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
                  lineNumber: 112,
                  columnNumber: 17
                },
                this
              )
            ]
          },
          void 0,
          true,
          {
            fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
            lineNumber: 99,
            columnNumber: 13
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(StaticField, { hideIf: (v) => isPublic(v) || !v.flows.includes("client_credentials"), children: /* @__PURE__ */ jsxDEV(Alert, { type: "warning", message: use(getString("oauth2server_clientgranttypeclientcredswarning", "admin")) }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
          lineNumber: 120,
          columnNumber: 17
        }, this) }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
          lineNumber: 119,
          columnNumber: 13
        }, this),
        /* @__PURE__ */ jsxDEV(
          RepeaterField,
          {
            name: "redirecturis",
            rules: [minItems(1, redirectUriRequired)],
            label: redirectUris,
            addLabel: use(getString("oauth2server_clientaddcallbackuri", "admin")),
            removeLabel: use(getString("remove")),
            hideIf: (v) => !isPublic(v) && !v.flows.includes("auth_code"),
            children: /* @__PURE__ */ jsxDEV(TextField, { label: redirectUris, hideLabel: true, rules: [url(redirectUriInvalid)] }, void 0, false, {
              fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
              lineNumber: 130,
              columnNumber: 17
            }, this)
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
            lineNumber: 122,
            columnNumber: 13
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(
          CheckboxField,
          {
            name: "ispkcerequired",
            label: use(getString("oauth2server_clientpkcerequired", "admin")),
            supportingText: use(getString("oauth2server_clientpkcehelp", "admin")),
            hideIf: (v) => isPublic(v) || !v.flows.includes("auth_code")
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
            lineNumber: 132,
            columnNumber: 13
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(
          GroupField,
          {
            name: "scopes",
            label: use(getString("oauth2server_scopes", "admin")),
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
                fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
                lineNumber: 145,
                columnNumber: 21
              },
              this
            ))
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
            lineNumber: 138,
            columnNumber: 13
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(StaticField, { children: /* @__PURE__ */ jsxDEV(Alert, { type: "warning", message: use(getString("oauth2server_clientcreationwarning", "admin")) }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
          lineNumber: 155,
          columnNumber: 17
        }, this) }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
          lineNumber: 154,
          columnNumber: 13
        }, this),
        /* @__PURE__ */ jsxDEV(FormSlot, { name: "core_admin_oauth2_client.footer" }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
          lineNumber: 157,
          columnNumber: 13
        }, this)
      ]
    },
    void 0,
    true,
    {
      fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
      lineNumber: 51,
      columnNumber: 9
    },
    this
  ) }, void 0, false, {
    fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
    lineNumber: 50,
    columnNumber: 9
  }, this);
}
__name(CreateClientForm, "CreateClientForm");
function CreateClientFormLoader(props) {
  return /* @__PURE__ */ jsxDEV(Suspense, { fallback: null, children: /* @__PURE__ */ jsxDEV(CreateClientForm, { ...props }, void 0, false, {
    fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
    lineNumber: 167,
    columnNumber: 13
  }, this) }, void 0, false, {
    fileName: "public/admin/js/esm/src/oauth2/createClientForm.tsx",
    lineNumber: 166,
    columnNumber: 9
  }, this);
}
__name(CreateClientFormLoader, "CreateClientFormLoader");
export {
  CreateClientFormLoader as default
};
//# sourceMappingURL=createClientForm.dev.js.map
