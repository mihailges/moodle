var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { createContext } from "react";
const FormStateContext = createContext({ serverDisabled: [] });
const GroupContext = createContext({ disabled: false });
const clone = /* @__PURE__ */ __name((value) => {
  if (Array.isArray(value)) {
    return value.map(clone);
  }
  if (value && typeof value === "object" && Object.getPrototypeOf(value) === Object.prototype) {
    return Object.fromEntries(Object.entries(value).map(([k, v]) => [k, clone(v)]));
  }
  return value;
}, "clone");
class FieldRegistry {
  static {
    __name(this, "FieldRegistry");
  }
  names = /* @__PURE__ */ new Map();
  repeaters = /* @__PURE__ */ new Set();
  add(name) {
    this.names.set(name, (this.names.get(name) ?? 0) + 1);
  }
  remove(name) {
    const count = (this.names.get(name) ?? 0) - 1;
    if (count > 0) {
      this.names.set(name, count);
    } else {
      this.names.delete(name);
    }
  }
  // Repeaters stay known once mounted, so their rows are still converted while the repeater is hidden.
  addRepeater(name) {
    this.repeaters.add(name);
  }
  /**
   * The field a server error path belongs to: the field itself, a repeater row's value,
   * or the nearest enclosing field (e.g. `scopes.read` shows on the `scopes` group). Null if none.
   */
  resolve(path) {
    const parts = path.split(".");
    for (let end = parts.length; end > 0; end--) {
      const candidate = parts.slice(0, end).join(".");
      for (const name of [candidate, `${candidate}.value`]) {
        if (this.names.has(name)) {
          return name;
        }
      }
    }
    return null;
  }
  // Repeaters hold their rows as {value}; the form's values and payload use plain arrays.
  wrap(values) {
    return this.convert(values, (rows) => rows.map((value) => ({ value })));
  }
  unwrap(values) {
    const plain = /* @__PURE__ */ __name((row) => row && typeof row === "object" && "value" in row ? row.value : row, "plain");
    return this.convert(values, (rows) => rows.map(plain));
  }
  convert(values, change) {
    const result = clone(values);
    this.repeaters.forEach((name) => {
      const parts = name.split(".");
      const last = parts.pop();
      const parent = parts.reduce((o, key) => o?.[key], result);
      if (parent && Array.isArray(parent[last])) {
        parent[last] = change(parent[last]);
      }
    });
    return result;
  }
}
const FieldRegistryContext = createContext(new FieldRegistry());
const FormActionContext = createContext(null);
const RowContext = createContext(null);
const AdvancedContext = createContext({ show: true, register: /* @__PURE__ */ __name(() => () => void 0, "register") });
const ContainerContext = createContext(null);
export {
  AdvancedContext,
  ContainerContext,
  FieldRegistry,
  FieldRegistryContext,
  FormActionContext,
  FormStateContext,
  GroupContext,
  RowContext
};
//# sourceMappingURL=context.dev.js.map
