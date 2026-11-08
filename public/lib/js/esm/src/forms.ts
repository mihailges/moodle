// Core of the forms system. Fields, rules and conditions are separate imports:
//   '@moodle/lms/core/forms/fields', '@moodle/lms/core/forms/rules', '@moodle/lms/core/forms/conditions'
export {MoodleForm} from './forms/MoodleForm';
export {useMoodleFormField, type FieldBehaviour} from './forms/useMoodleFormField';
export {FormValidationError} from './forms/FormValidationError';
export {FormSlot} from './forms/slots/FormSlot';
export {registerFormExtension, getFormExtensions, type FormExtension} from './forms/registry';
export {ExtensionModules, loadFormExtensionModules} from './forms/slots/ExtensionModules';
