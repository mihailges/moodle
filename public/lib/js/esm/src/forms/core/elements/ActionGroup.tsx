import type {ReactNode} from 'react';

type ActionGroupProps = {
    // Names the group for screen readers, e.g. "Form actions".
    label: string;
    children: ReactNode;
};

/**
 * Lays out ActionButtons in a row, like an mform group of action buttons. Use it, instead of `create`, `update` and
 * `cancel`, to give the form its own buttons:
 *   <ActionGroup label="Actions">
 *     <ActionButton label="Save" variant="primary" submit={{action: save, onSuccess: goToList}} />
 *     <ActionButton label="Save and continue" submit={{action: saveAndNext, onSuccess: goToNext}} />
 *     <ActionButton label="Cancel" onClick={goBack} />
 *   </ActionGroup>
 */
export function ActionGroup({label, children}: ActionGroupProps) {
    return <div role="group" aria-label={label} className="d-flex flex-wrap gap-2">{children}</div>;
}
