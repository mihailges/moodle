import { MoodleForm } from "./forms/core/MoodleForm";
import { sendFormEndpointRequest } from "./forms/core/formEndpointRequest";
import { useFormField, useDefaultIf, requiredIndicator } from "./forms/core/formField";
import { FormValidationError } from "./forms/core/FormValidationError";
import { FormSlot } from "./forms/core/slots/FormSlot";
import { registerFormExtension, getFormExtensions } from "./forms/core/slots/formExtension";
import { ExtensionModules, loadFormExtensionModules } from "./forms/core/slots/ExtensionModules";
export {
  ExtensionModules,
  FormSlot,
  FormValidationError,
  MoodleForm,
  getFormExtensions,
  loadFormExtensionModules,
  registerFormExtension,
  requiredIndicator,
  sendFormEndpointRequest,
  useDefaultIf,
  useFormField
};
//# sourceMappingURL=forms.dev.js.map
