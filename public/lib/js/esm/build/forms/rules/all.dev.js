var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
const all = /* @__PURE__ */ __name((...rules) => async (value, values) => {
  for (const rule of rules) {
    const result = await rule(value, values);
    if (result !== true) {
      return result;
    }
  }
  return true;
}, "all");
export {
  all
};
//# sourceMappingURL=all.dev.js.map
