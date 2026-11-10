var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
const extensions = [];
const sameExtension = /* @__PURE__ */ __name((a, b) => a.slot === b.slot && a.plugin === b.plugin && (a.id ?? "") === (b.id ?? ""), "sameExtension");
function registerFormExtension(extension) {
  const existing = extensions.findIndex((e) => sameExtension(e, extension));
  if (existing >= 0) {
    extensions.splice(existing, 1);
  }
  extensions.push(extension);
}
__name(registerFormExtension, "registerFormExtension");
function getFormExtensions(slot) {
  return extensions.filter((e) => e.slot === slot);
}
__name(getFormExtensions, "getFormExtensions");
export {
  getFormExtensions,
  registerFormExtension
};
//# sourceMappingURL=formExtension.dev.js.map
