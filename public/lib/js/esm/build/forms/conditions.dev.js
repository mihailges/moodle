var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { get } from "react-hook-form";
const make = /* @__PURE__ */ __name((deps, fn) => Object.assign(fn, { deps }), "make");
const isEmpty = /* @__PURE__ */ __name((v) => v === void 0 || v === null || v === "" || Array.isArray(v) && v.length === 0, "isEmpty");
const eq = /* @__PURE__ */ __name((field, expected) => make([field], (values) => get(values, field) === expected), "eq");
const neq = /* @__PURE__ */ __name((field, expected) => make([field], (values) => get(values, field) !== expected), "neq");
const checked = /* @__PURE__ */ __name((field) => make([field], (values) => Boolean(get(values, field))), "checked");
const notEmpty = /* @__PURE__ */ __name((field) => make([field], (values) => !isEmpty(get(values, field))), "notEmpty");
const not = /* @__PURE__ */ __name((condition) => make(condition.deps, (values) => !condition(values)), "not");
const anyOf = /* @__PURE__ */ __name((...conditions) => make(conditions.flatMap((c) => c.deps), (values) => conditions.some((c) => c(values))), "anyOf");
const allOf = /* @__PURE__ */ __name((...conditions) => make(conditions.flatMap((c) => c.deps), (values) => conditions.every((c) => c(values))), "allOf");
export {
  allOf,
  anyOf,
  checked,
  eq,
  neq,
  not,
  notEmpty
};
//# sourceMappingURL=conditions.dev.js.map
