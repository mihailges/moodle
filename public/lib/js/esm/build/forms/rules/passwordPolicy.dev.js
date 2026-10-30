var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
const fetchPasswordPolicy = /* @__PURE__ */ __name(async () => ({
  minLength: 8,
  minDigits: 1,
  minLower: 1,
  minUpper: 1,
  minNonAlphanumeric: 1
}), "fetchPasswordPolicy");
const defaultMessages = /* @__PURE__ */ __name((p) => ({
  minLength: `Password must be at least ${p.minLength} characters long`,
  minDigits: `Password must have at least ${p.minDigits} digit(s)`,
  minLower: `Password must have at least ${p.minLower} lower case letter(s)`,
  minUpper: `Password must have at least ${p.minUpper} upper case letter(s)`,
  minNonAlphanumeric: `Password must have at least ${p.minNonAlphanumeric} special character(s)`
}), "defaultMessages");
const passwordPolicy = /* @__PURE__ */ __name(({ fetchPolicy = fetchPasswordPolicy, messages } = {}) => {
  let policyPromise;
  const getPolicy = /* @__PURE__ */ __name(() => {
    policyPromise ??= fetchPolicy().catch(() => null);
    return policyPromise;
  }, "getPolicy");
  return async (v) => {
    if (typeof v !== "string") {
      return true;
    }
    const policy = await getPolicy();
    if (!policy) {
      return true;
    }
    const text = messages ?? defaultMessages(policy);
    const count = /* @__PURE__ */ __name((re) => (v.match(re) ?? []).length, "count");
    if (v.length < policy.minLength) {
      return text.minLength;
    }
    if (count(/\d/g) < policy.minDigits) {
      return text.minDigits;
    }
    if (count(/[a-z]/g) < policy.minLower) {
      return text.minLower;
    }
    if (count(/[A-Z]/g) < policy.minUpper) {
      return text.minUpper;
    }
    if (count(/[^a-zA-Z0-9]/g) < policy.minNonAlphanumeric) {
      return text.minNonAlphanumeric;
    }
    return true;
  };
}, "passwordPolicy");
export {
  fetchPasswordPolicy,
  passwordPolicy
};
//# sourceMappingURL=passwordPolicy.dev.js.map
