import type {ReactNode} from 'react';
import {useFieldConditions, type FieldBehaviour} from '../formField';

type StaticFieldProps = Pick<FieldBehaviour, 'hideIf'> & {
    // Only used to look up server-disabled state; a static field holds no value.
    name?: string;
    children: ReactNode;
};

/**
 * Renders any content inside the form without a value, like the mform 'static' element.
 * Pass anything as children (an Alert, text, a link...); a condition can still show or hide it.
 */
export function StaticField({name = 'static', hideIf, children}: StaticFieldProps) {
    const {hidden} = useFieldConditions(name, {hideIf});
    return hidden ? null : <>{children}</>;
}
