// Core of the forms system. Fields, rules and conditions are separate imports:
//   '@moodle/lms/core/forms/fields', '@moodle/lms/core/forms/rules', '@moodle/lms/core/forms/conditions'
export {MoodleForm} from './forms/core/MoodleForm';
export {useMoodleFormField, requiredIndicator, type FieldBehaviour} from './forms/core/useMoodleFormField';
export {FormValidationError} from './forms/core/FormValidationError';
export {FormSlot} from './forms/core/slots/FormSlot';
export {registerFormExtension, getFormExtensions, type FormExtension} from './forms/core/registry';
export {ExtensionModules, loadFormExtensionModules} from './forms/core/slots/ExtensionModules';
