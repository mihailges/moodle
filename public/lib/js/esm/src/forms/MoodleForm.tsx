import {useEffect, useMemo, useRef, useState, Children, type ReactNode} from 'react';
import {useForm, FormProvider, type DefaultValues, type FieldValues, type Path} from 'react-hook-form';
import {getString} from '@moodle/lms/core/stringUtils';
import {FormStateContext, FieldRegistry, FieldRegistryContext, FormActionContext, type SubmitOverride} from './context';
import {ActionButtons, SubmitButton, CancelButton} from './fields/ActionButtons';
import {isFormValidationError} from './FormValidationError';
import {formApi} from './restApi';

type MoodleFormProps<V extends FieldValues, P, I extends number | string, S> = {
    // Not needed when editing with `endpoint` and `resourceId`: the values come from the server.
    defaultValues?: DefaultValues<V>;
    // The REST collection route, e.g. '/admin/oauth2/server/clients'. Without `resourceId` the form POSTs to it;
    // with it the form GETs the values from `{endpoint}/{resourceId}` and PATCHes them there.
    endpoint?: string;
    // Present when editing an existing record: it is loaded, and a save resets the form's changes.
    resourceId?: I;
    // Only for forms that don't use `endpoint`.
    load?: (id: I) => Promise<{values: P; readonly?: string[]}>;
    // The standard submit button; shown with `endpoint` or `submit`. Without either, place your own
    // <ActionButton submit={{action}}>.
    submit?: {
        // Saves the payload instead of `endpoint`.
        action?: (payload: P) => Promise<S>;
        label?: string;
        savingLabel?: string;
        onSuccess?: (saved: S) => void;
    };
    // The standard cancel button.
    cancel?: {action: () => void; label?: string};
    // Only needed when the form values differ in shape from the payload.
    toForm?: (payload: P) => V;
    toPayload?: (values: V) => P;
    // Optional: rename a server error path. By default it is matched to the field with that name, or the closest one.
    mapErrorPath?: (path: string) => string;
    children: ReactNode;
};

/**
 * Renders a form from its fields (children) and owns the shared plumbing:
 * loading, saving and server error mapping.
 */
export function MoodleForm<V extends FieldValues, P = V, I extends number | string = number, S = P>({
    defaultValues, endpoint, resourceId, load, submit, cancel, toForm, toPayload, mapErrorPath = (p) => p, children,
}: MoodleFormProps<V, P, I, S>) {
    const rest = useMemo(() => (endpoint ? formApi(endpoint) : null), [endpoint]);
    const form = useForm<V>({defaultValues: defaultValues ?? ({} as DefaultValues<V>)});
    const {reset, setError, handleSubmit, formState: {errors, isSubmitting}} = form;

    const [status, setStatus] = useState<'loading' | 'ready' | 'error'>(
        resourceId !== undefined && (load || rest) ? 'loading' : 'ready',
    );
    const registry = useRef(new FieldRegistry()).current;
    const [serverDisabled, setServerDisabled] = useState<string[]>([]);

    const [strings, setStrings] = useState({save: 'Save', cancel: 'Cancel', error: 'Something went wrong. Try again.'});
    useEffect(() => {
        Promise.all([getString('save'), getString('cancel'), getString('unknownerror')])
            .then(([save, cancelLabel, error]) => setStrings({save, cancel: cancelLabel, error}))
            .catch(() => undefined);
    }, []);

    const asForm = (payload: P) => (toForm ? toForm(payload) : payload as unknown as V);
    const asPayload = (values: V) => (toPayload ? toPayload(values) : values as unknown as P);

    useEffect(() => {
        const fromApi = async (key: I): Promise<{values: P; readonly?: string[]}> => ({values: await rest!.get(key) as P});
        const loader = load ?? (rest ? fromApi : null);
        if (resourceId === undefined || !loader) {
            return;
        }
        let cancelled = false;
        setStatus('loading');
        loader(resourceId)
            .then((record) => {
                if (cancelled) {
                    return;
                }
                // Dirty tracking starts from the loaded values; the defaults fill in what the record lacks.
                reset({...defaultValues, ...asForm(record.values)} as DefaultValues<V>);
                setServerDisabled(record.readonly ?? []);
                setStatus('ready');
            })
            .catch(() => !cancelled && setStatus('error'));
        return () => {
            cancelled = true;
        };
    }, [resourceId]);

    const state = useMemo(() => ({serverDisabled}), [serverDisabled]);

    const onSubmit = async (values: V, override?: SubmitOverride) => {
        const action = override?.action ?? submit?.action
            ?? (rest
                ? (payload: P) => (resourceId !== undefined ? rest.update(resourceId, payload) : rest.create(payload))
                : undefined);
        if (!action) {
            return;
        }
        try {
            const payload = asPayload(registry.unwrap(values));
            const saved = await action(payload);
            if (resourceId !== undefined) {
                reset(registry.wrap(asForm(saved as unknown as P)) as DefaultValues<V>);   // saved state becomes the clean baseline
            }
            (override?.onSuccess ?? submit?.onSuccess)?.(saved);
        } catch (e) {
            if (!isFormValidationError(e)) {
                setError('root.server', {message: strings.error});
                return;
            }
            Object.entries(e.fieldErrors).forEach(([path, messages], i) => {
                const field = registry.resolve(mapErrorPath(path));
                if (field) {
                    setError(field as Path<V>, {type: 'server', message: messages[0]}, {shouldFocus: i === 0});
                } else {
                    setError('root.server', {message: messages[0]});
                }
            });
            e.formErrors.forEach((message) => setError('root.server', {message}));
        }
    };

    const actionApi = {
        submit: (override?: SubmitOverride) => handleSubmit((values) => onSubmit(values, override))(),
        submitting: isSubmitting,
        getValues: () => registry.unwrap(form.getValues()),
        setValue: (name: string, value: unknown) => form.setValue(name as Path<V>, value as any, {shouldDirty: true}),
    };

    if (status === 'loading') {
        return <p>Loading...</p>;
    }
    if (status === 'error') {
        return <div role="alert" className="alert alert-danger">This record could not be loaded.</div>;
    }

    return (
        <FormProvider {...form}>
            <FieldRegistryContext.Provider value={registry}>
            <FormStateContext.Provider value={state}>
                <FormActionContext.Provider value={actionApi}>
                <form onSubmit={handleSubmit((values) => onSubmit(values))} noValidate>
                    {Children.map(children, (child) => (
                        <div className="mt-3">
                            {child}
                        </div>
                    ))}
                    {errors.root?.server && (
                        <div role="alert" className="alert alert-danger">{errors.root.server.message}</div>
                    )}
                    {(submit || rest || cancel) && (
                        <ActionButtons>
                            {(submit || rest) && (
                                <SubmitButton label={submit?.label ?? strings.save} savingLabel={submit?.savingLabel} />
                            )}
                            {cancel && <CancelButton label={cancel.label ?? strings.cancel} onClick={cancel.action} />}
                        </ActionButtons>
                    )}
                </form>
                </FormActionContext.Provider>
            </FormStateContext.Provider>
            </FieldRegistryContext.Provider>
        </FormProvider>
    );
}
