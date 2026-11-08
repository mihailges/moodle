var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import Fetch from "@moodle/lms/core/fetch";
import { FormValidationError } from "./FormValidationError";
async function toError(e) {
  if (e instanceof Response && e.status === 422) {
    const body = await e.json();
    const hasFieldErrors = body.errors && Object.keys(body.errors).length > 0;
    return new FormValidationError(body.errors ?? {}, hasFieldErrors ? [] : [body.message]);
  }
  return e instanceof Response ? new Error(e.statusText) : e;
}
__name(toError, "toError");
async function send(request) {
  try {
    return await (await request).json();
  } catch (e) {
    throw await toError(e);
  }
}
__name(send, "send");
const formApi = /* @__PURE__ */ __name((endpoint) => {
  const component = "";
  const collection = endpoint.replace(/\/+$/, "");
  const record = /* @__PURE__ */ __name((id) => `${collection}/${id}`, "record");
  return {
    get: /* @__PURE__ */ __name((id) => send(Fetch.performGet(component, record(id), { cachekey: null })), "get"),
    create: /* @__PURE__ */ __name((payload) => send(Fetch.performPost(component, collection, { body: payload, rejectWithResponse: true })), "create"),
    update: /* @__PURE__ */ __name((id, payload) => send(Fetch.performPatch(component, record(id), { body: payload, rejectWithResponse: true })), "update")
  };
}, "formApi");
export {
  formApi
};
//# sourceMappingURL=restApi.dev.js.map
