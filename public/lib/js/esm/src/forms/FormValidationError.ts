/**
 * Thrown by a form's create/update call when the server rejects the data.
 * DefinedForm maps it onto the fields.
 */
export class FormValidationError extends Error {
    constructor(
        public fieldErrors: Record<string, string[]>,   // keyed by server path
        public formErrors: string[] = [],
    ) {
        super('Validation failed');
        this.name = 'FormValidationError';
    }
}

// instanceof is unreliable if the module is loaded under two URLs (e.g. with and without a theme prefix).
export const isFormValidationError = (e: unknown): e is FormValidationError =>
    e instanceof FormValidationError || (e instanceof Error && e.name === 'FormValidationError' && 'fieldErrors' in e);
