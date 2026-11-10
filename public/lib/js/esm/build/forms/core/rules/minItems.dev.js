var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { isEmpty } from "./helpers";
import { markMandatory } from "../useMoodleFormField";
const minItems = /* @__PURE__ */ __name((min, message) => markMandatory(
  (v) => !Array.isArray(v) || v.filter((item) => !isEmpty(item)).length >= min || message,
  min >= 1
), "minItems");
export {
  minItems
};
//# sourceMappingURL=minItems.dev.js.map
