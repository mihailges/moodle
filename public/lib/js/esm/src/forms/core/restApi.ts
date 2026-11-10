import Fetch from '@moodle/lms/core/fetch';
import {FormValidationError} from './FormValidationError';

// Turns a 422 response ({message, errors: {path: [messages]}}) into a FormValidationError.
async function toError(e: unknown): Promise<unknown> {
    if (e instanceof Response && e.status === 422) {
        const body = await e.json();
        const hasFieldErrors = body.errors && Object.keys(body.errors).length > 0;
        return new FormValidationError(body.errors ?? {}, hasFieldErrors ? [] : [body.message]);
    }
    return e instanceof Response ? new Error(e.statusText) : e;
}

async function send(request: Promise<Response>): Promise<any> {
    try {
        return await (await request).json();
    } catch (e) {
        throw await toError(e);
    }
}

export type SaveMethod = 'POST' | 'PUT' | 'PATCH';

/**
 * The requests of a form on one resource URL, e.g. `/admin/oauth2/server/clients/5` or `/admin/settings/foo`. The
 * URL is used as given, so it is the same for a record and a singleton: GET loads the values and `method` saves them.
 */
export const formApi = (endpoint: string, method: SaveMethod = 'POST') => {
    // The route already starts with its component (e.g. /admin/...), so there is no separate component.
    const component = '';
    return {
        get: () => send(Fetch.performGet(component, endpoint, {cachekey: null})),
        save: (payload: unknown) => {
            const options = {body: payload as any, rejectWithResponse: true};
            const request = method === 'POST' ? Fetch.performPost : method === 'PUT' ? Fetch.performPut : Fetch.performPatch;
            return send(request(component, endpoint, options));
        },
    };
};
