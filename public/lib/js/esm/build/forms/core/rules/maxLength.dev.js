var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { stringRule } from "./helpers";
const maxLength = /* @__PURE__ */ __name((max, message) => stringRule((v) => v.length <= max, message), "maxLength");
export {
  maxLength
};
//# sourceMappingURL=maxLength.dev.js.map
