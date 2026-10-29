import {useEffect, useState} from 'react';
import {useForm, useFieldArray} from 'react-hook-form';
import {Input, Textarea, Switch, Checkbox, Button} from '@moodlehq/design-system';
import {getScopes, createClient, FormValidationError, getClient, patchClient} from '@moodle/lms/core_admin/oauth2/clientApi';

type ClientValues = {
    name: string;
    description: string;
    ispkcerequired: boolean;
    scopes: string[];
    redirecturis: {uri: string}[];
};

export default function ClientForm({clientId}: {clientId?: number}) {
    const {register, control, handleSubmit, reset, setError, formState: {dirtyFields, errors, isDirty, isSubmitting}} = useForm<ClientValues>({
        defaultValues: {name: 'Test client', description: '', ispkcerequired: false, scopes: [], redirecturis: [{uri: ''}]},
    });

    const {fields, append, remove} = useFieldArray({control, name: 'redirecturis'});

    const toFormValues = (c: ClientPayload): ClientValues => ({...c, redirecturis: c.redirecturis.map((uri) => ({uri}))});

    const [scopeOptions, setScopeOptions] = useState<Option[] | null>(null);
    const [loaded, setLoaded] = useState(false);
    const [readonly, setReadonly] = useState<string[]>([]);
    const [loadError, setLoadError] = useState<string | null>(null);

    useEffect(() => {
        Promise.all([getScopes(), clientId ? getClient(clientId) : Promise.resolve(null)])
            .then(([options, client]) => {
                setScopeOptions(options);
                if (client) {
                    reset(toFormValues(client.values));   // dirty tracking starts from the loaded values
                    setReadonly(client.readonly);
                }
                setLoaded(true);
            })
            .catch(() => {
                setLoadError('this client could not be loaded');
            })
            .finally(() => setLoaded(true));
    }, [clientId]);

    if (!loaded) {
        return <p>Loading...</p>;
    }
    if (loadError) {
        return <div role="alert" className="alert alert-danger">{loadError}</div>;
    }

    const toPayload = (values: ClientValues): ClientPayload => ({
        ...values,
        redirecturis: values.redirecturis.map((r) => r.uri),
    });

    const changedValues = (values: ClientValues): Partial<ClientPayload> => {
        const payload = toPayload(values);
        return Object.fromEntries(
            Object.keys(dirtyFields).map((key) => [key, payload[key as keyof ClientPayload]]),
        );
    };

    const onSubmit = async (values: ClientValues) => {
        try {
            if (clientId) {
                // Edit: PATCH only the changed fields.
                const changes = changedValues(values);
                if (Object.keys(changes).length === 0) {
                    return;
                }
                const saved = await patchClient(clientId, changes);
                reset(toFormValues(saved));          // saved state becomes the clean baseline
                console.log('updated', saved);
            } else {
                const client = await createClient(toPayload(values));
                console.log('created', client);
            }
        } catch (e) {
            if (!(e instanceof FormValidationError)) {
                setError('root.server', {message: 'Something went wrong. Try again.'});
                return;
            }
            Object.entries(e.fieldErrors).forEach(([path, messages], i) => {
                // The server says redirecturis.1, but the form holds redirecturis.1.uri.
                const formPath = path.startsWith('redirecturis.') ? `${path}.uri` : path;
                setError(formPath as any, {type: 'server', message: messages[0]}, {shouldFocus: i === 0});
            });
            e.formErrors.forEach((message) => setError('root.server', {message}));
        }
    };

    return (
        <form onSubmit={handleSubmit(onSubmit)} noValidate>
            <Input
                label="Name"
                {...register('name', {
                    required: 'Enter a client name',
                    maxLength: {value: 15, message: 'Name must be 15 characters or fewer'},
                })}
                invalid={Boolean(errors.name)}
                invalidFeedback={errors.name?.message}
                readOnly={readonly.includes('name')}
            />
            <Textarea
                label="Description"
                {...register('description')}
            />
            <Switch
                label="Is PKCE required"
                {...register('ispkcerequired')}
                disabled={readonly.includes('ispkcerequired')}
            />

            <fieldset>
                <legend className="fs-6 mb-2">Redirect URIs</legend>
                <div className="d-flex flex-column gap-2">
                    {fields.map((field, index) => (
                        <div key={field.id} className="d-flex gap-2 align-items-start">
                            <Input
                                id={`oauth2client_redirecturis_${index}`}
                                label={`Redirect URI ${index + 1}`}
                                hideLabel
                                {...register(`redirecturis.${index}.uri`, {
                                    required: 'Enter a redirect URI',
                                    validate: (v) => URL.canParse(v) || 'Enter a valid URL',
                                })}
                                invalid={Boolean(errors.redirecturis?.[index]?.uri)}
                                invalidFeedback={errors.redirecturis?.[index]?.uri?.message}
                            />
                            <Button
                                type="button"
                                onClick={() => remove(index)}
                                disabled={fields.length === 1}
                                aria-label={`Remove redirect URI ${index + 1}`}
                                label="Remove"
                                variant="secondary"
                            />
                        </div>
                    ))}
                </div>
                <Button type="button" className="mt-2" onClick={() => append({uri: ''})} label="Add redirect URI" />
            </fieldset>

            <fieldset aria-describedby={errors.scopes ? 'oauth2client_scopes_error' : undefined}>
                <legend className="fs-6 mb-2">Scopes</legend>

                {scopeOptions === null ? (
                    <p className="text-muted">Loading scopes…</p>
                ) : scopeOptions.map((scope) => (
                    <Checkbox
                        key={scope.value}
                        id={`oauth2client_scopes_${scope.value.replace(/\W+/g, '_')}`}
                        label={scope.label}
                        value={scope.value}
                        {...register('scopes', {
                            validate: (v) => (Array.isArray(v) && v.length > 0) || 'Select at least one scope',
                        })}
                    />
                ))}

                {errors.scopes && (
                    <div id="oauth2client_scopes_error" className="text-danger small mt-1">
                        {errors.scopes.message}
                    </div>
                )}
            </fieldset>
            {errors.root?.server && (
                <div role="alert" className="alert alert-danger">{errors.root.server.message}</div>
            )}

            <p>Dirty: {String(isDirty)}</p>
            <Button type="submit" label={isSubmitting ? 'Saving…' : 'Save'} size="lg" disabled={isSubmitting} />
        </form>
    );
}
