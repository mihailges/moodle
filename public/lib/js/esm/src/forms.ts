// Core of the forms system. Fields and rules are separate imports:
//   '@moodle/lms/core/forms/fields', '@moodle/lms/core/forms/rules'
export {MoodleForm} from './forms/core/MoodleForm';
export {useFormField, requiredIndicator, type FieldBehaviour} from './forms/core/formField';
export {FormValidationError} from './forms/core/FormValidationError';
export {FormSlot} from './forms/core/slots/FormSlot';
export {registerFormExtension, getFormExtensions, type FormExtension} from './forms/core/slots/formExtension';
export {ExtensionModules, loadFormExtensionModules} from './forms/core/slots/ExtensionModules';
