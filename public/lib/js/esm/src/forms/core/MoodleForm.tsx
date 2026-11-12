import {useCallback, useEffect, useMemo, useRef, useState, Children, type ReactNode} from 'react';
import {useForm, FormProvider, type DefaultValues, type FieldValues, type Path} from 'react-hook-form';
import {getString} from '@moodle/lms/core/stringUtils';
import {
    FormStateContext, FieldRegistry, FieldRegistryContext, FormActionContext, AdvancedContext, type SubmitOverride,
} from './formContext';
import {ActionButton} from './elements/ActionButton';
import {isFormValidationError} from './FormValidationError';

// What happens when the form is submitted.
export type FormSubmit<P, S> = {
    action: (payload: P) => Promise<S>;
    onSuccess?: (saved: S) => void;
    label?: string;
    inProgressLabel?: string;
};

// Loads the existing record of a form that edits one; it may use any URL, independent of the submit request.
export type RecordLoader<P> = () => Promise<{values: P; readonly?: string[]}>;

type MoodleFormProps<V extends FieldValues, P, S> = {
    // Static values, or a loader of the record being edited: the form shows loading, then starts from the record,
    // and after a save starts from the saved values.
    defaultValues?: DefaultValues<V> | RecordLoader<P>;
    // The standard submit button. Without it the form has none: place your own <ActionButton>s.
    submit?: FormSubmit<P, S>;
    // The standard cancel button.
    cancel?: {action: () => void; label?: string};
    // Only needed when the form values differ in shape from the payload.
    toForm?: (payload: P) => V;
    toPayload?: (values: V) => P;
    // Optional: rename a server error path. By default, it is matched to the field with that name, or the closest one.
    mapErrorPath?: (path: string) => string;
    children: ReactNode;
};

/**
 * Renders a form from its fields (children) and owns the shared plumbing:
 * loading, saving and server error mapping. It does not know whether the form creates or edits: `defaultValues`
 * and `submit` say what the form starts from and what saving does.
 */
export function MoodleForm<V extends FieldValues, P = V, S = P>(props: MoodleFormProps<V, P, S>) {
    const {submit, cancel, toForm, toPayload, mapErrorPath = (p) => p, children} = props;
    const loader = typeof props.defaultValues === 'function' ? props.defaultValues : null;
    const defaultValues = (loader ? undefined : props.defaultValues) as DefaultValues<V> | undefined;
    const form = useForm<V>({defaultValues: defaultValues ?? ({} as DefaultValues<V>), mode: 'onTouched', shouldFocusError: false});
    const {reset, setError, handleSubmit, formState: {errors, isSubmitting}} = form;

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
    }, []);

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
        const action = override?.action ?? submit?.action;
        if (!action) {
            return;
        }
        try {
            const payload = asPayload(registry.unwrap(values));
            const saved = await action(payload);
            if (loader) {
                reset(registry.wrap(asForm(saved as unknown as P)) as DefaultValues<V>);   // saved state becomes the clean baseline
            }
            (override ? override.onSuccess : submit?.onSuccess)?.(saved);
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
                        <div className="mt-4">
                            {child}
                        </div>
                    ))}
                    {advancedCount > 0 && (
                        <button type="button" className="btn btn-link px-0 mt-4" aria-expanded={showAdvanced}
                            onClick={() => setShowAdvanced(!showAdvanced)}>
                            {showAdvanced ? strings.showLess : strings.showMore}
                        </button>
                    )}
                    {errors.root?.server && (
                        <div role="alert" className="alert alert-danger">{errors.root.server.message}</div>
                    )}
                    {(submit || cancel) && (
                        <div className="d-flex gap-2 mt-4">
                            {submit && (
                                <ActionButton
                                    label={submit.label ?? strings.save}
                                    variant="primary"
                                    inProgressLabel={submit.inProgressLabel ?? 'Saving…'}
                                    formSubmit
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
