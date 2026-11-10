import {useContext, type MouseEvent} from 'react';
import {Button} from '@moodlehq/design-system';
import {FormActionContext, type FormActionApi, type SubmitOverride} from '../formContext';

type ActionButtonProps = {
    label: string;
    variant?: 'primary' | 'secondary' | 'outline-secondary' | 'danger';
    // Shown instead of `label` while a submit is in progress.
    inProgressLabel?: string;
    // Saves the form. `true` is the form's own submit (also triggered by Enter); an object saves with its own `action`
    // (needed when the form has no `submit`) and `onSuccess` runs afterwards (e.g. "Save and continue" going elsewhere).
    submit?: boolean | SubmitOverride;
    // A custom action; receives the form's values and a way to change them or to submit.
    onClick?: (form: FormActionApi) => void | Promise<void>;
};

/**
 * A button of the form: its standard save and cancel buttons and any extra ones, like several buttons in an mform group.
 *   <ActionButton label="Save" variant="primary" inProgressLabel="Saving…" submit />
 *   <ActionButton label="Reset" onClick={(f) => f.setValue('a', '')} />
 */
export function ActionButton({label, variant = 'secondary', inProgressLabel, submit, onClick}: ActionButtonProps) {
    const api = useContext(FormActionContext);
    const run = () => {
        if (submit) {
            return api?.submit(typeof submit === 'object' ? submit : undefined);
        }
        return api && onClick?.(api);
    };
    const submitting = !!api?.submitting;
    return (
        <Button
            type={submit === true ? 'submit' : 'button'}
            label={submitting && submit && inProgressLabel ? inProgressLabel : label}
            variant={variant}
            size="lg"
            // Not `disabled`, so a keyboard user's focus stays on the button while the form saves.
            aria-disabled={submitting || undefined}
            className={submitting ? 'disabled' : undefined}
            onClick={(e: MouseEvent) => {
                if (submitting) {
                    e.preventDefault();
                } else if (submit !== true) {
                    run();
                }
            }}
        />
    );
}
