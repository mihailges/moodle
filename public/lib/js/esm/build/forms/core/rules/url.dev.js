var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { stringRule } from "./helpers";
const url = /* @__PURE__ */ __name((message) => stringRule((v) => URL.canParse(v), message), "url");
export {
  url
};
//# sourceMappingURL=url.dev.js.map
