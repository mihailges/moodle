var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { isEmpty } from "./helpers";
import { markMandatory } from "../formField";
const minItems = /* @__PURE__ */ __name((min, message) => markMandatory(
  (v) => v !== void 0 && v !== null && !Array.isArray(v) || (Array.isArray(v) ? v.filter((item) => !isEmpty(item)).length : 0) >= min || message,
  min >= 1
), "minItems");
export {
  minItems
};
//# sourceMappingURL=minItems.dev.js.map
