var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { stringRule } from "./helpers";
const minLength = /* @__PURE__ */ __name((min, message) => stringRule((v) => v.length >= min, message), "minLength");
export {
  minLength
};
//# sourceMappingURL=minLength.dev.js.map
