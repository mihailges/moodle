import {registerFormExtension} from '@moodle/lms/core/forms';
import {TextField, SwitchField} from '@moodle/lms/core/forms/elements';
import {required, pattern, validIf} from '@moodle/lms/core/forms/rules';

const MAX_LIMIT = 10;

function GeneralExtension() {
    return (
        <>
            <TextField
                name="extensions.local_formtest.reference"
                label="Reference (plugin)"
                rules={[required('The reference is required'), pattern(/^[A-Z]{3}-\d{3}$/, 'Use a format like ABC-123')]}
            />
            <TextField
                name="extensions.local_formtest.limit"
                label="Limit (plugin)"
                rules={[validIf((limit) => Number(limit) <= MAX_LIMIT, `The limit cannot be more than ${MAX_LIMIT}`)]}
            />
        </>
    );
}

function FooterExtension() {
    return <SwitchField name="extensions.local_formtest.sendcopy" label="Send me a copy (plugin)" />;
}

registerFormExtension({slot: 'core_test_form.general', plugin: 'local_formtest', component: GeneralExtension});
registerFormExtension({slot: 'core_test_form.footer', plugin: 'local_formtest', component: FooterExtension});
