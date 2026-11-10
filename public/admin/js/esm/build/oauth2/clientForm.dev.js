var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { Suspense, use } from "react";
import { Alert } from "@moodlehq/design-system";
import { getString } from "@moodle/lms/core/stringUtils";
import { MoodleForm, sendFormEndpointRequest } from "@moodle/lms/core/forms";
import {
  GroupField,
  RadioField,
  CheckboxField,
  RepeaterField,
  StaticField,
  TextField,
  TextareaField
} from "@moodle/lms/core/forms/elements";
import { required, maxLength, url, minItems } from "@moodle/lms/core/forms/rules";
const CLIENTS_URL = "/admin/oauth2/server/clients";
const isPublic = /* @__PURE__ */ __name((v) => v.clienttype === "public", "isPublic");
const supportsAuthCode = /* @__PURE__ */ __name((v) => isPublic(v) || Boolean(v.flows?.includes("auth_code")), "supportsAuthCode");
function ClientForm({ mode, clientId, scopes, listUrl }) {
  const isCreate = mode === "create";
  const goToList = /* @__PURE__ */ __name(() => {
    window.location.href = listUrl;
  }, "goToList");
  const create = {
    submit: {
      action: /* @__PURE__ */ __name((payload) => sendFormEndpointRequest("/admin/oauth2/server/clients", "POST").save(payload), "action"),
      label: use(getString("oauth2server_clientcreate", "admin")),
      onSuccess: goToList
    }
  };
  const update = {
    load: /* @__PURE__ */ __name(async () => ({
      values: await sendFormEndpointRequest(`/admin/oauth2/server/clients/${clientId}`).get()
    }), "load"),
    submit: {
      action: /* @__PURE__ */ __name((payload) => sendFormEndpointRequest(`/admin/oauth2/server/clients/${clientId}`, "PATCH").save(payload), "action"),
      onSuccess: goToList
    }
  };
  return /* @__PURE__ */ jsxDEV(
    MoodleForm,
    {
      ...isCreate ? create : update,
      cancel: { action: goToList },
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
            fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
            lineNumber: 70,
            columnNumber: 13
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(TextareaField, { name: "description", label: use(getString("description")) }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
          lineNumber: 78,
          columnNumber: 13
        }, this),
        isCreate && /* @__PURE__ */ jsxDEV(
          GroupField,
          {
            name: "clienttype",
            label: use(getString("oauth2server_clienttype", "admin")),
            layout: "column",
            defaultValue: "confidential",
            children: [
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
                  fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
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
                  fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
                  lineNumber: 92,
                  columnNumber: 17
                },
                this
              )
            ]
          },
          void 0,
          true,
          {
            fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
            lineNumber: 80,
            columnNumber: 13
          },
          this
        ),
        isCreate && /* @__PURE__ */ jsxDEV(
          GroupField,
          {
            name: "flows",
            label: use(getString("oauth2server_clientprimaryflows", "admin")),
            layout: "column",
            defaultValue: ["auth_code"],
            defaultIf: (v) => isPublic(v) ? ["auth_code"] : void 0,
            disableIf: isPublic,
            rules: [minItems(1, use(getString("oauth2server_clientmustselectprimaryflow", "admin")))],
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
                  fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
                  lineNumber: 110,
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
                  fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
                  lineNumber: 116,
                  columnNumber: 17
                },
                this
              )
            ]
          },
          void 0,
          true,
          {
            fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
            lineNumber: 101,
            columnNumber: 13
          },
          this
        ),
        isCreate && /* @__PURE__ */ jsxDEV(StaticField, { hideIf: (v) => isPublic(v) || !v.flows?.includes("client_credentials"), children: /* @__PURE__ */ jsxDEV(Alert, { type: "warning", message: use(getString("oauth2server_clientgranttypeclientcredswarning", "admin")) }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
          lineNumber: 126,
          columnNumber: 17
        }, this) }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
          lineNumber: 125,
          columnNumber: 13
        }, this),
        /* @__PURE__ */ jsxDEV(
          RepeaterField,
          {
            name: "redirecturis",
            rules: [minItems(1, use(getString("oauth2server_clientcallbackurirequired", "admin")))],
            label: use(getString("oauth2server_clientcallbackuris", "admin")),
            addLabel: use(getString("oauth2server_clientaddcallbackuri", "admin")),
            removeLabel: use(getString("remove")),
            defaultValue: [""],
            hideIf: (v) => !supportsAuthCode(v),
            children: /* @__PURE__ */ jsxDEV(
              TextField,
              {
                label: use(getString("oauth2server_clientcallbackuris", "admin")),
                hideLabel: true,
                rules: [url(use(getString("oauth2server_clientcallbackurisinvalid", "admin")))]
              },
              void 0,
              false,
              {
                fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
                lineNumber: 138,
                columnNumber: 17
              },
              this
            )
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
            lineNumber: 129,
            columnNumber: 13
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(
          CheckboxField,
          {
            name: "ispkcerequired",
            label: use(getString("oauth2server_clientpkcerequired", "admin")),
            supportingText: use(getString(
              isCreate ? "oauth2server_clientpkcehelp" : "oauth2server_clientpkcehelpconfidential",
              "admin"
            )),
            hideIf: (v) => !v.flows?.includes("auth_code"),
            defaultValue: true,
            defaultIf: (v) => isPublic(v) ? true : void 0,
            disableIf: (v) => isPublic(v)
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
            lineNumber: 144,
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
            rules: [minItems(1, use(getString("oauth2serverclientnoscopes", "error")))],
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
                fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
                lineNumber: 163,
                columnNumber: 21
              },
              this
            ))
          },
          void 0,
          false,
          {
            fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
            lineNumber: 156,
            columnNumber: 13
          },
          this
        ),
        isCreate && /* @__PURE__ */ jsxDEV(StaticField, { children: /* @__PURE__ */ jsxDEV(Alert, { type: "warning", message: use(getString("oauth2server_clientcreationwarning", "admin")) }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
          lineNumber: 174,
          columnNumber: 21
        }, this) }, void 0, false, {
          fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
          lineNumber: 173,
          columnNumber: 17
        }, this)
      ]
    },
    void 0,
    true,
    {
      fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
      lineNumber: 66,
      columnNumber: 9
    },
    this
  );
}
__name(ClientForm, "ClientForm");
function ClientFormLoader(props) {
  return /* @__PURE__ */ jsxDEV(Suspense, { fallback: null, children: /* @__PURE__ */ jsxDEV(ClientForm, { ...props }, void 0, false, {
    fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
    lineNumber: 185,
    columnNumber: 13
  }, this) }, void 0, false, {
    fileName: "public/admin/js/esm/src/oauth2/clientForm.tsx",
    lineNumber: 184,
    columnNumber: 9
  }, this);
}
__name(ClientFormLoader, "ClientFormLoader");
export {
  ClientFormLoader as default
};
//# sourceMappingURL=clientForm.dev.js.map
