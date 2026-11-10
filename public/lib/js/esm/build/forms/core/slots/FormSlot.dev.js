var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
import { Component } from "react";
import { useFormContext } from "react-hook-form";
import { getFormExtensions } from "./formExtension";
const DEFAULT_ORDER = 100;
class ExtensionBoundary extends Component {
  static {
    __name(this, "ExtensionBoundary");
  }
  state = { failed: false };
  static getDerivedStateFromError() {
    return { failed: true };
  }
  componentDidCatch(error) {
    window.console.error(`[forms] extension from ${this.props.plugin} failed to render`, error);
  }
  render() {
    return this.state.failed ? null : this.props.children;
  }
}
function FormSlot({ name }) {
  const { control } = useFormContext();
  const extensions = [...getFormExtensions(name)].sort((a, b) => (a.order ?? DEFAULT_ORDER) - (b.order ?? DEFAULT_ORDER));
  return /* @__PURE__ */ jsxDEV(Fragment, { children: extensions.map(({ component: Extension, plugin, id }) => /* @__PURE__ */ jsxDEV(ExtensionBoundary, { plugin, children: /* @__PURE__ */ jsxDEV(Extension, { control }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/slots/FormSlot.tsx",
    lineNumber: 33,
    columnNumber: 21
  }, this) }, `${plugin}:${id ?? ""}`, false, {
    fileName: "public/lib/js/esm/src/forms/core/slots/FormSlot.tsx",
    lineNumber: 32,
    columnNumber: 17
  }, this)) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/slots/FormSlot.tsx",
    lineNumber: 30,
    columnNumber: 9
  }, this);
}
__name(FormSlot, "FormSlot");
export {
  FormSlot
};
//# sourceMappingURL=FormSlot.dev.js.map
