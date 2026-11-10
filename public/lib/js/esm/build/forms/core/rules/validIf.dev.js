var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
const validIf = /* @__PURE__ */ __name((isValid, message) => (value, values) => Boolean(isValid(value, values)) || message, "validIf");
export {
  validIf
};
//# sourceMappingURL=validIf.dev.js.map
