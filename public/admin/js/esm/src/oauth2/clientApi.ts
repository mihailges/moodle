import {FormValidationError} from '@moodle/lms/core/forms';

let store = {id: 7, name: 'Mobile app', description: 'iOS client', ispkcerequired: true,
             scopes: ['user:read'], redirecturis: ['com.example.app:/callback']};

export async function getClient(id: number) {
    await new Promise((r) => setTimeout(r, 300));
    return {values: {...store}, readonly: ['ispkcerequired']};
}

// Mock of PATCH /oauth2/server/clients/{id}
export async function patchClient(id: number, changes: Partial<ClientPayload>) {
    await new Promise((r) => setTimeout(r, 300));
    // console.log('PATCH body', changes);
    if (changes.name?.toLowerCase() === 'duplicate') {
        throw new FormValidationError({name: ['A client with this name already exists']});
    }
    store = {...store, ...changes};
    return {...store};
}

export type ClientPayload = {
    name: string;
    description: string;
    ispkcerequired: boolean;
    scopes: string[];
    redirecturis: string[];
};

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
