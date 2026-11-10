var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { isEmpty } from "./helpers";
import { markMandatory } from "../useMoodleFormField";
const required = /* @__PURE__ */ __name((message) => markMandatory((v) => !isEmpty(v) || message, true), "required");
export {
  required
};
//# sourceMappingURL=required.dev.js.map
