var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
const minItems = /* @__PURE__ */ __name((min, message) => (v) => !Array.isArray(v) || v.length >= min || message, "minItems");
export {
  minItems
};
//# sourceMappingURL=minItems.dev.js.map
