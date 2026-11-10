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
const formApi = /* @__PURE__ */ __name((endpoint, method = "POST") => {
  const component = "";
  return {
    get: /* @__PURE__ */ __name(() => send(Fetch.performGet(component, endpoint, { cachekey: null })), "get"),
    save: /* @__PURE__ */ __name((payload) => {
      const options = { body: payload, rejectWithResponse: true };
      const request = method === "POST" ? Fetch.performPost : method === "PUT" ? Fetch.performPut : Fetch.performPatch;
      return send(request(component, endpoint, options));
    }, "save")
  };
}, "formApi");
export {
  formApi
};
//# sourceMappingURL=restApi.dev.js.map
