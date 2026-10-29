import {useEffect, useState} from 'react';
import {useForm, useFieldArray} from 'react-hook-form';
import {Input, Textarea, Switch, Checkbox ,Button} from '@moodlehq/design-system';
import {getScopes, createClient, FormValidationError} from '@moodle/lms/core_admin/oauth2/clientApi';

type ClientValues = {
    name: string;
    description: string;
    ispkcerequired: boolean;
    scopes: string[];
    redirecturis: {uri: string}[];
};

export default function RhfSmokeTest() {
    const {register, control, handleSubmit, setError, formState: {errors, isDirty, isSubmitting}} = useForm<ClientValues>({
        defaultValues: {name: 'Test client', description: '', ispkcerequired: false, scopes: [], redirecturis: [{uri: ''}]},
    });

    const {fields, append, remove} = useFieldArray({control, name: 'redirecturis'});

    const [scopeOptions, setScopeOptions] = useState(null);

    useEffect(() => {
        getScopes().then(setScopeOptions);
    }, []);

    const onSubmit = async (values: ClientValues) => {
        try {
            const client = await createClient({...values, redirecturis: values.redirecturis.map((r) => r.uri)});
            console.log('created', client);
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
            />
            <Textarea
                label="Description"
                {...register('description')}
            />
            <Switch
                label="Is PKCE required"
                {...register('ispkcerequired')}
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
                {errors.root?.server && (
                    <div role="alert" className="alert alert-danger">{errors.root.server.message}</div>
                )}
            </fieldset>

            <p>Dirty: {String(isDirty)}</p>
            <Button type="submit" label={isSubmitting ? 'Saving…' : 'Save'} size="lg" disabled={isSubmitting} />
        </form>
    );
}
