var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { isEmpty } from "./helpers";
const requiredIf = /* @__PURE__ */ __name((condition, message) => (v, values) => !condition(values) || !isEmpty(v) || message, "requiredIf");
export {
  requiredIf
};
//# sourceMappingURL=requiredIf.dev.js.map
