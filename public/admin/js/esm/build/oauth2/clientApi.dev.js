var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { FormValidationError } from "@moodle/lms/core/forms";
let store = {
  id: 7,
  name: "Mobile app",
  description: "iOS client",
  ispkcerequired: true,
  scopes: ["user:read"],
  redirecturis: ["com.example.app:/callback"]
};
async function getClient(id) {
  await new Promise((r) => setTimeout(r, 300));
  return { values: { ...store }, readonly: ["ispkcerequired"] };
}
__name(getClient, "getClient");
async function patchClient(id, changes) {
  await new Promise((r) => setTimeout(r, 300));
  if (changes.name?.toLowerCase() === "duplicate") {
    throw new FormValidationError({ name: ["A client with this name already exists"] });
  }
  store = { ...store, ...changes };
  return { ...store };
}
__name(patchClient, "patchClient");
async function getScopes() {
  await new Promise((r) => setTimeout(r, 300));
  return [
    { value: "user:read", label: "Read user data" },
    { value: "user:write", label: "Modify user data" },
    { value: "course:read", label: "Read course data" }
  ];
}
__name(getScopes, "getScopes");
async function createClient(payload) {
  await new Promise((r) => setTimeout(r, 300));
  const errors = {};
  if (payload.name.toLowerCase() === "duplicate") {
    errors.name = ["A client with this name already exists"];
  }
  payload.redirecturis.forEach((uri, i) => {
    if (uri.includes("#")) {
      errors[`redirecturis.${i}`] = ["Redirect URIs must not contain a fragment"];
    }
  });
  if (Object.keys(errors).length) {
    throw new FormValidationError(errors);
  }
  return { id: 42, ...payload };
}
__name(createClient, "createClient");
export {
  createClient,
  getClient,
  getScopes,
  patchClient
};
//# sourceMappingURL=clientApi.dev.js.map
