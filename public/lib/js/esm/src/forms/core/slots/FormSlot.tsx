import {Component, type ReactNode} from 'react';
import {useFormContext} from 'react-hook-form';
import {getFormExtensions} from '../registry';

const DEFAULT_ORDER = 100;

// Contains a failing plugin extension so it cannot take the whole form down.
class ExtensionBoundary extends Component<{plugin: string; children: ReactNode}, {failed: boolean}> {
    state = {failed: false};

    static getDerivedStateFromError() {
        return {failed: true};
    }

    componentDidCatch(error: unknown) {
        window.console.error(`[forms] extension from ${this.props.plugin} failed to render`, error);
    }

    render() {
        return this.state.failed ? null : this.props.children;
    }
}

// Renders the extensions plugins registered for this slot, lowest order first.
export function FormSlot({name}: {name: string}) {
    const {control} = useFormContext();
    // Array.sort is stable, so equal orders keep their registration order.
    const extensions = [...getFormExtensions(name)].sort((a, b) => (a.order ?? DEFAULT_ORDER) - (b.order ?? DEFAULT_ORDER));
    return (
        <>
            {extensions.map(({component: Extension, plugin, id}) => (
                <ExtensionBoundary key={`${plugin}:${id ?? ''}`} plugin={plugin}>
                    <Extension control={control} />
                </ExtensionBoundary>
            ))}
        </>
    );
}
