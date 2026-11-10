var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
const isEmpty = /* @__PURE__ */ __name((v) => v === void 0 || v === null || typeof v === "string" && v.trim() === "" || Array.isArray(v) && v.length === 0, "isEmpty");
const stringRule = /* @__PURE__ */ __name((check, message) => (v) => typeof v !== "string" || v === "" || check(v) || message, "stringRule");
export {
  isEmpty,
  stringRule
};
//# sourceMappingURL=helpers.dev.js.map
