import {useEffect, useMemo, useState, type ReactNode} from 'react';
import {useForm, FormProvider, type DefaultValues, type FieldValues, type Path} from 'react-hook-form';
import {Button} from '@moodlehq/design-system';
import {FormStateContext} from './context';
import {isFormValidationError} from './FormValidationError';

type DefinedFormProps<V extends FieldValues, P, I extends number | string> = {
    defaultValues: DefaultValues<V>;
    // Present when editing an existing record.
    id?: I;
    load?: (id: I) => Promise<{values: P; readonly?: string[]}>;
    create: (payload: P) => Promise<P>;
    // Receives only the fields that changed.
    update?: (id: I, changes: Partial<P>) => Promise<P>;
    // Only needed when the form values differ in shape from the payload.
    toForm?: (payload: P) => V;
    toPayload?: (values: V) => P;
    // Translate a server error path into a form field path.
    mapErrorPath?: (path: string) => string;
    onSaved?: (saved: P) => void;
    children: ReactNode;
};

/**
 * Renders a form from its fields (children) and owns the shared plumbing:
 * loading, create/update, dirty-only updates and server error mapping.
 */
export function DefinedForm<V extends FieldValues, P = V, I extends number | string = number>({
    defaultValues, id, load, create, update, toForm, toPayload, mapErrorPath = (p) => p, onSaved, children,
}: DefinedFormProps<V, P, I>) {
    const form = useForm<V>({defaultValues, shouldUnregister: true});   // unmounted (hidden) fields are not submitted
    const {reset, setError, handleSubmit, formState: {dirtyFields, errors, isSubmitting}} = form;

    const [status, setStatus] = useState<'loading' | 'ready' | 'error'>(id !== undefined && load ? 'loading' : 'ready');
    const [serverDisabled, setServerDisabled] = useState<string[]>([]);

    const asForm = (payload: P) => (toForm ? toForm(payload) : payload as unknown as V);
    const asPayload = (values: V) => (toPayload ? toPayload(values) : values as unknown as P);

    useEffect(() => {
        if (id === undefined || !load) {
            return;
        }
        let cancelled = false;
        setStatus('loading');
        load(id)
            .then((record) => {
                if (cancelled) {
                    return;
                }
                reset(asForm(record.values) as DefaultValues<V>);   // dirty tracking starts from the loaded values
                setServerDisabled(record.readonly ?? []);
                setStatus('ready');
            })
            .catch(() => !cancelled && setStatus('error'));
        return () => {
            cancelled = true;
        };
    }, [id]);

    const state = useMemo(() => ({serverDisabled}), [serverDisabled]);

    const onSubmit = async (values: V) => {
        try {
            const payload = asPayload(values);
            let saved: P;
            if (id !== undefined && update) {
                const changes = Object.fromEntries(
                    Object.keys(dirtyFields).map((key) => [key, payload[key as keyof P]]),
                ) as Partial<P>;
                if (Object.keys(changes).length === 0) {
                    return;
                }
                saved = await update(id, changes);
                reset(asForm(saved) as DefaultValues<V>);   // saved state becomes the clean baseline
            } else {
                saved = await create(payload);
            }
            onSaved?.(saved);
        } catch (e) {
            if (!isFormValidationError(e)) {
                setError('root.server', {message: 'Something went wrong. Try again.'});
                return;
            }
            Object.entries(e.fieldErrors).forEach(([path, messages], i) => {
                setError(mapErrorPath(path) as Path<V>, {type: 'server', message: messages[0]}, {shouldFocus: i === 0});
            });
            e.formErrors.forEach((message) => setError('root.server', {message}));
        }
    };

    if (status === 'loading') {
        return <p>Loading...</p>;
    }
    if (status === 'error') {
        return <div role="alert" className="alert alert-danger">This record could not be loaded.</div>;
    }

    return (
        <FormProvider {...form}>
            <FormStateContext.Provider value={state}>
                <form onSubmit={handleSubmit(onSubmit)} noValidate>
                    {children}
                    {errors.root?.server && (
                        <div role="alert" className="alert alert-danger">{errors.root.server.message}</div>
                    )}
                    <Button type="submit" label={isSubmitting ? 'Saving…' : 'Save'} size="lg" disabled={isSubmitting} />
                </form>
            </FormStateContext.Provider>
        </FormProvider>
    );
}
