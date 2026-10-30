var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
class FormValidationError extends Error {
  constructor(fieldErrors, formErrors = []) {
    super("Validation failed");
    this.fieldErrors = fieldErrors;
    this.formErrors = formErrors;
    this.name = "FormValidationError";
  }
  static {
    __name(this, "FormValidationError");
  }
}
const isFormValidationError = /* @__PURE__ */ __name((e) => e instanceof FormValidationError || e instanceof Error && e.name === "FormValidationError" && "fieldErrors" in e, "isFormValidationError");
export {
  FormValidationError,
  isFormValidationError
};
//# sourceMappingURL=FormValidationError.dev.js.map
