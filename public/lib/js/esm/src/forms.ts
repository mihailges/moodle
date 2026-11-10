// Core of the forms system. Fields and rules are separate imports:
//   '@moodle/lms/core/forms/elements', '@moodle/lms/core/forms/rules'
export {MoodleForm, type FormSubmit, type RecordLoader} from './forms/core/MoodleForm';
export {sendFormEndpointRequest} from './forms/core/formEndpointRequest';
export {useFormField, useDefaultIf, requiredIndicator, type FieldBehaviour, type DefaultRule} from './forms/core/formField';
export {FormValidationError} from './forms/core/FormValidationError';
export {FormSlot} from './forms/core/slots/FormSlot';
export {registerFormExtension, getFormExtensions, type FormExtension} from './forms/core/slots/formExtension';
export {ExtensionModules, loadFormExtensionModules} from './forms/core/slots/ExtensionModules';
