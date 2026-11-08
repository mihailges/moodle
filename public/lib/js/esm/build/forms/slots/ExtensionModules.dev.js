var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
import { useEffect, useState } from "react";
const SPECIFIER = /^@moodle\/lms\/[a-z][a-z0-9_]*(\/[A-Za-z0-9_.-]+)+$/;
async function loadFormExtensionModules(modules) {
  await Promise.all(modules.map(async (specifier) => {
    if (!SPECIFIER.test(specifier) || specifier.includes("..")) {
      window.console.error(`[forms] Ignoring invalid form extension module: ${specifier}`);
      return;
    }
    try {
      await import(specifier);
    } catch (e) {
      window.console.error(`[forms] Failed to load form extension module: ${specifier}`, e);
    }
  }));
}
__name(loadFormExtensionModules, "loadFormExtensionModules");
function ExtensionModules({ modules = [], children }) {
  const [ready, setReady] = useState(modules.length === 0);
  useEffect(() => {
    let cancelled = false;
    loadFormExtensionModules(modules).then(() => !cancelled && setReady(true));
    return () => {
      cancelled = true;
    };
  }, [modules.join("|")]);
  return ready ? /* @__PURE__ */ jsxDEV(Fragment, { children }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/slots/ExtensionModules.tsx",
    lineNumber: 35,
    columnNumber: 20
  }, this) : null;
}
__name(ExtensionModules, "ExtensionModules");
export {
  ExtensionModules,
  loadFormExtensionModules
};
//# sourceMappingURL=ExtensionModules.dev.js.map
