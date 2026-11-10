var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
import { useCallback, useContext, useEffect, useRef } from "react";
import { useFormContext, get } from "react-hook-form";
import { AdvancedContext, ContainerContext } from "./context";
function useContainer(advanced) {
  const names = useRef(/* @__PURE__ */ new Map());
  const { formState: { errors } } = useFormContext();
  const add = useCallback((name) => {
    names.current.set(name, (names.current.get(name) ?? 0) + 1);
    return () => {
      const count = (names.current.get(name) ?? 1) - 1;
      if (count > 0) {
        names.current.set(name, count);
      } else {
        names.current.delete(name);
      }
    };
  }, []);
  const hasError = [...names.current.keys()].some((name) => Boolean(get(errors, name)));
  const { show, register } = useContext(AdvancedContext);
  useEffect(() => advanced ? register() : void 0, [advanced, register]);
  return {
    add,
    hasError,
    // Advanced content is hidden, not removed: its fields stay registered, so they keep their values and rules.
    hidden: Boolean(advanced) && !show && !hasError
  };
}
__name(useContainer, "useContainer");
function ContainerProvider({ add, children }) {
  return /* @__PURE__ */ jsxDEV(ContainerContext.Provider, { value: { add }, children }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/core/containers.tsx",
    lineNumber: 37,
    columnNumber: 12
  }, this);
}
__name(ContainerProvider, "ContainerProvider");
export {
  ContainerProvider,
  useContainer
};
//# sourceMappingURL=containers.dev.js.map
