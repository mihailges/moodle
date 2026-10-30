var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { isEmpty } from "./helpers";
const required = /* @__PURE__ */ __name((message) => (v) => !isEmpty(v) || message, "required");
export {
  required
};
//# sourceMappingURL=required.dev.js.map
