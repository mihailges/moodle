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

/**
 * The requests of a form on a collection route like `/admin/oauth2/server/clients`: POST creates on it, and GET and
 * PATCH use the record's route, `/admin/oauth2/server/clients/{id}`.
 */
export const formApi = (endpoint: string) => {
    // The route already starts with its component (e.g. /admin/...), so there is no separate component.
    const component = '';
    const collection = endpoint.replace(/\/+$/, '');
    const record = (id: number | string) => `${collection}/${id}`;
    return {
        get: (id: number | string) => send(Fetch.performGet(component, record(id), {cachekey: null})),
        create: (payload: unknown) =>
            send(Fetch.performPost(component, collection, {body: payload as any, rejectWithResponse: true})),
        update: (id: number | string, payload: unknown) =>
            send(Fetch.performPatch(component, record(id), {body: payload as any, rejectWithResponse: true})),
    };
};
