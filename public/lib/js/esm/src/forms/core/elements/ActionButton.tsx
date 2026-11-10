import {useContext, type MouseEvent} from 'react';
import {Button} from '@moodlehq/design-system';
import {FormActionContext, type FormActionApi, type SubmitOverride} from '../formContext';

type ActionButtonProps = {
    label: string;
    variant?: 'primary' | 'secondary' | 'outline-secondary' | 'danger';
    // Shown instead of `label` while a submit is in progress.
    inProgressLabel?: string;
    // Validates the form, then saves the payload with `action`; `onSuccess` runs afterwards, e.g. to redirect or open
    // a modal. Nothing is inherited from the form's `create` or `update`, so each button says what it does.
    submit?: SubmitOverride;
    // Only for the form's own save button: it submits with the active mode's `create` or `update` (and on Enter).
    formSubmit?: boolean;
    // A custom action; receives the form's values and a way to change them or to submit.
    onClick?: (form: FormActionApi) => void | Promise<void>;
};

/**
 * A button of the form: its standard save and cancel buttons and any extra ones, like several buttons in an mform group.
 *   <ActionButton label="Save" variant="primary" inProgressLabel="Saving…" submit={{action: save, onSuccess: goBack}} />
 *   <ActionButton label="Reset" onClick={(f) => f.setValue('a', '')} />
 */
export function ActionButton({label, variant = 'secondary', inProgressLabel, submit, formSubmit, onClick}: ActionButtonProps) {
    const api = useContext(FormActionContext);
    const run = () => {
        if (submit) {
            return api?.submit(submit);
        }
        return api && onClick?.(api);
    };
    const submitting = !!api?.submitting;
    return (
        <Button
            type={formSubmit ? 'submit' : 'button'}
            label={submitting && (submit || formSubmit) && inProgressLabel ? inProgressLabel : label}
            variant={variant}
            size="lg"
            // Not `disabled`, so a keyboard user's focus stays on the button while the form saves.
            aria-disabled={submitting || undefined}
            className={submitting ? 'disabled' : undefined}
            onClick={(e: MouseEvent) => {
                if (submitting) {
                    e.preventDefault();
                } else if (!formSubmit) {
                    run();
                }
            }}
        />
    );
}
