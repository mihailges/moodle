import {useState, type ReactNode} from 'react';
import {useContainer, ContainerProvider} from '../containers';

type SectionFieldProps = {
    title: string;
    // Whether the section starts expanded. Defaults to true.
    defaultOpen?: boolean;
    // Only shown after the form's "Show more" button.
    advanced?: boolean;
    children: ReactNode;
};

/**
 * A collapsible section of fields, like mform's header with collapsed state. Its fields stay on the form
 * while collapsed, so they keep their values and rules, and the section opens if one of them has an error.
 */
export function SectionField({title, defaultOpen = true, advanced, children}: SectionFieldProps) {
    const [open, setOpen] = useState(defaultOpen);
    const {add, hasError, hidden} = useContainer(advanced);
    return (
        <ContainerProvider add={add}>
            <details
                className="border rounded p-3"
                hidden={hidden}
                open={open || hasError}
                onToggle={(e) => setOpen(e.currentTarget.open)}
            >
                <summary className="fs-5 fw-bold">{title}</summary>
                <div className="d-flex flex-column gap-3 mt-3">{children}</div>
            </details>
        </ContainerProvider>
    );
}
