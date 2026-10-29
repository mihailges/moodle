export async function getClient(id: number) {
    await delay(300);
    return {
        values: {name: 'Test client', description: '', ispkcerequired: true,
                 scopes: ['user:read'], redirecturis: ['https://example.com/cb']},
        readonly: ['ispkcerequired'],      // stand-in for capability-driven state
    };
}

export type Option = {
    value: string;
    label: string;
}

// GET /oauth2/server/scopes
export async function getScopes(): Promise<Option[]> {
    await new Promise((r) => setTimeout(r, 300));
    return [
        {value: 'user:read', label: 'Read user data'},
        {value: 'user:write', label: 'Modify user data'},
        {value: 'course:read', label: 'Read course data'},
    ];
}

export class FormValidationError extends Error {
    constructor(
        public fieldErrors: Record<string, string[]>,   // keyed by server path
        public formErrors: string[] = [],
    ) {
        super('Validation failed');
    }
}

// Mock of POST /oauth2/server/clients returning a 422.
export async function createClient(payload: ClientPayload) {
    await new Promise((r) => setTimeout(r, 300));
    const errors: Record<string, string[]> = {};
    if (payload.name.toLowerCase() === 'duplicate') {
        errors.name = ['A client with this name already exists'];
    }
    payload.redirecturis.forEach((uri, i) => {
        if (uri.includes('#')) {
            errors[`redirecturis.${i}`] = ['Redirect URIs must not contain a fragment'];
        }
    });
    if (Object.keys(errors).length) {
        throw new FormValidationError(errors);
    }
    return {id: 42, ...payload};
}
