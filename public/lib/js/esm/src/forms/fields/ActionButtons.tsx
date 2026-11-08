import {useContext, type ReactNode} from 'react';
import {Button} from '@moodlehq/design-system';
import {FormActionContext, type FormActionApi, type SubmitOverride} from '../context';

// A row of buttons. Used by the form's standard buttons, and to place your own (see ActionButton).
export function ActionButtons({children}: {children: ReactNode}) {
    return <div className="d-flex gap-2 mt-3">{children}</div>;
}

export function SubmitButton({label, savingLabel = 'Saving…'}: {label: string; savingLabel?: string}) {
    const api = useContext(FormActionContext);
    return <Button type="submit" label={api?.submitting ? savingLabel : label} size="lg" disabled={api?.submitting} />;
}

export function CancelButton({label, onClick}: {label: string; onClick: () => void}) {
    const api = useContext(FormActionContext);
    return <Button type="button" label={label} variant="secondary" size="lg" disabled={api?.submitting} onClick={onClick} />;
}

type ActionButtonProps = {
    label: string;
    variant?: 'primary' | 'secondary' | 'outline-secondary' | 'danger';
    // Save the form like the main button does. Give `action` when the form has no `submit`, and `onSuccess`
    // to run something afterwards (e.g. "Save and continue" going elsewhere).
    submit?: boolean | SubmitOverride;
    // A custom action; receives the form's values and a way to change them or to submit.
    onClick?: (form: FormActionApi) => void | Promise<void>;
};

/**
 * One button of the form, for forms with several actions, like several buttons in an mform group:
 *   <ActionButtons>
 *       <ActionButton label="Save" submit />
 *       <ActionButton label="Reset" onClick={(f) => f.setValue('a', '')} />
 *   </ActionButtons>
 */
export function ActionButton({label, variant = 'secondary', submit, onClick}: ActionButtonProps) {
    const api = useContext(FormActionContext);
    const run = () => {
        if (submit) {
            return api?.submit(typeof submit === 'object' ? submit : undefined);
        }
        return api && onClick?.(api);
    };
    return <Button type="button" label={label} variant={variant} size="lg" disabled={api?.submitting} onClick={run} />;
}
