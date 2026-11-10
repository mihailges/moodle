var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { isEmpty } from "./helpers";
import { markMandatory } from "../useMoodleFormField";
const requiredIf = /* @__PURE__ */ __name((condition, message) => markMandatory((v, values) => !condition(values) || !isEmpty(v) || message, condition), "requiredIf");
export {
  requiredIf
};
//# sourceMappingURL=requiredIf.dev.js.map
