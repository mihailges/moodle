import {useCallback, useEffect, useMemo, useRef, useState, Children, type ReactNode} from 'react';
import {useForm, FormProvider, type DefaultValues, type FieldValues, type Path} from 'react-hook-form';
import {getString} from '@moodle/lms/core/stringUtils';
import {
    FormStateContext, FieldRegistry, FieldRegistryContext, FormActionContext, AdvancedContext, type SubmitOverride,
} from './formContext';
import {ActionButton} from './fields/ActionButton';
import {isFormValidationError} from './FormValidationError';
import {sendFormEndpointRequest, type SaveMethod} from './formEndpointRequest';

type MoodleFormProps<V extends FieldValues, P, S> = {
    // Not needed when the values come from the server (`method` other than POST, or `load`).
    defaultValues?: DefaultValues<V>;
    // The full URL of the resource, e.g. '/admin/oauth2/server/clients/5' or '/admin/settings/foo'; it is used as given.
    // The form saves to it with `method`, and unless the method is POST it first GETs the values from it.
    endpoint?: string;
    // POST creates (the default); PATCH and PUT update, so the existing values are loaded and a save resets the form.
    method?: SaveMethod;
    // Only for forms that don't use `endpoint`, or whose values are not a plain GET of it.
    load?: () => Promise<{values: P; readonly?: string[]}>;
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
export function MoodleForm<V extends FieldValues, P = V, S = P>({
    defaultValues, endpoint, method = 'POST', load, submit, cancel, toForm, toPayload, mapErrorPath = (p) => p, children,
}: MoodleFormProps<V, P, S>) {
    const rest = useMemo(() => (endpoint ? sendFormEndpointRequest(endpoint, method) : null), [endpoint, method]);
    const form = useForm<V>({defaultValues: defaultValues ?? ({} as DefaultValues<V>), shouldFocusError: false});
    const {reset, setError, handleSubmit, formState: {errors, isSubmitting}} = form;

    // An update form starts from the saved values; a create form starts from its defaults.
    const loader: (() => Promise<{values: P; readonly?: string[]}>) | null = load ?? (rest && method !== 'POST'
        ? async () => ({values: await rest.get() as P})
        : null);
    const [status, setStatus] = useState<'loading' | 'ready' | 'error'>(loader ? 'loading' : 'ready');
    const registry = useRef(new FieldRegistry()).current;
    const [serverDisabled, setServerDisabled] = useState<string[]>([]);

    const [showAdvanced, setShowAdvanced] = useState(false);
    const [advancedCount, setAdvancedCount] = useState(0);
    const registerAdvanced = useCallback(() => {
        setAdvancedCount((count) => count + 1);
        return () => setAdvancedCount((count) => count - 1);
    }, []);
    const advanced = useMemo(() => ({show: showAdvanced, register: registerAdvanced}), [showAdvanced, registerAdvanced]);

    const [strings, setStrings] = useState({
        save: 'Save', cancel: 'Cancel', error: 'Something went wrong. Try again.', showMore: 'Show more', showLess: 'Show less',
    });
    useEffect(() => {
        Promise.all([
            getString('save'), getString('cancel'), getString('unknownerror'), getString('showmore'), getString('showless'),
        ])
            .then(([save, cancelLabel, error, showMore, showLess]) => {
                setStrings({save, cancel: cancelLabel, error, showMore, showLess});
            })
            .catch(() => undefined);
    }, []);

    const asForm = (payload: P) => (toForm ? toForm(payload) : payload as unknown as V);
    const asPayload = (values: V) => (toPayload ? toPayload(values) : values as unknown as P);

    useEffect(() => {
        if (!loader) {
            return;
        }
        let cancelled = false;
        setStatus('loading');
        loader()
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
    }, [endpoint]);

    const state = useMemo(() => ({serverDisabled}), [serverDisabled]);

    // After a failed save the first invalid field gets the focus, even if it was collapsed and has just opened. The
    // errors render over a few updates, so this keeps looking after each render until it finds one.
    const formElement = useRef<HTMLFormElement>(null);
    const focusPending = useRef(false);
    const focusFirstInvalid = () => {
        focusPending.current = true;
        setTimeout(() => {
            focusPending.current = false;
        }, 500);
    };
    useEffect(() => {
        if (!focusPending.current) {
            return;
        }
        const invalid = formElement.current?.querySelector<HTMLElement>('[aria-invalid="true"], [data-invalid]');
        if (invalid) {
            focusPending.current = false;
            invalid.focus();
        }
    });

    const onSubmit = async (values: V, override?: SubmitOverride) => {
        const action = override?.action ?? submit?.action
            ?? (rest
                ? rest.save
                : undefined);
        if (!action) {
            return;
        }
        try {
            const payload = asPayload(registry.unwrap(values));
            const saved = await action(payload);
            if (loader) {
                reset(registry.wrap(asForm(saved as unknown as P)) as DefaultValues<V>);   // saved state becomes the clean baseline
            }
            (override?.onSuccess ?? submit?.onSuccess)?.(saved);
        } catch (e) {
            if (!isFormValidationError(e)) {
                setError('root.server', {message: strings.error});
                return;
            }
            Object.entries(e.fieldErrors).forEach(([path, messages]) => {
                const field = registry.resolve(mapErrorPath(path));
                if (field) {
                    setError(field as Path<V>, {type: 'server', message: messages[0]});
                } else {
                    setError('root.server', {message: messages[0]});
                }
            });
            e.formErrors.forEach((message) => setError('root.server', {message}));
            focusFirstInvalid();
        }
    };

    const actionApi = {
        submit: (override?: SubmitOverride) => handleSubmit((values) => onSubmit(values, override), focusFirstInvalid)(),
        submitting: isSubmitting,
        getValues: () => registry.unwrap(form.getValues()),
        setValue: (name: string, value: unknown) => form.setValue(name as Path<V>, value as any, {shouldDirty: true}),
    };

    if (status === 'loading') {
        return <p role="status">Loading...</p>;
    }
    if (status === 'error') {
        return <div role="alert" className="alert alert-danger">This record could not be loaded.</div>;
    }

    return (
        <FormProvider {...form}>
            <FieldRegistryContext.Provider value={registry}>
            <FormStateContext.Provider value={state}>
                <FormActionContext.Provider value={actionApi}>
                <AdvancedContext.Provider value={advanced}>
                <form ref={formElement} onSubmit={handleSubmit((values) => onSubmit(values), focusFirstInvalid)} noValidate>
                    {Children.map(children, (child) => (
                        <div className="mt-3">
                            {child}
                        </div>
                    ))}
                    {advancedCount > 0 && (
                        <button type="button" className="btn btn-link px-0 mt-3" aria-expanded={showAdvanced}
                            onClick={() => setShowAdvanced(!showAdvanced)}>
                            {showAdvanced ? strings.showLess : strings.showMore}
                        </button>
                    )}
                    {errors.root?.server && (
                        <div role="alert" className="alert alert-danger">{errors.root.server.message}</div>
                    )}
                    {(submit || rest || cancel) && (
                        <div className="d-flex gap-2 mt-3">
                            {(submit || rest) && (
                                <ActionButton
                                    label={submit?.label ?? strings.save}
                                    variant="primary"
                                    inProgressLabel={submit?.savingLabel ?? 'Saving…'}
                                    submit
                                />
                            )}
                            {cancel && (
                                <ActionButton label={cancel.label ?? strings.cancel} onClick={cancel.action} />
                            )}
                        </div>
                    )}
                </form>
                </AdvancedContext.Provider>
                </FormActionContext.Provider>
            </FormStateContext.Provider>
            </FieldRegistryContext.Provider>
        </FormProvider>
    );
}
