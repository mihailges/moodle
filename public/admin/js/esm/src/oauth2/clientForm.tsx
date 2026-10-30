import {useEffect, useState} from 'react';
import {DefinedForm} from '@moodle/lms/core/forms';
import {TextField, TextareaField, SwitchField, CheckboxGroupField, RepeaterField} from '@moodle/lms/core/forms/fields';
import {required, maxLength, url, minItems} from '@moodle/lms/core/forms/rules';
import {checked} from '@moodle/lms/core/forms/conditions';
import {
    getScopes, createClient, getClient, patchClient, type ClientPayload, type Option,
} from '@moodle/lms/core_admin/oauth2/clientApi';

export default function ClientForm({clientId}: {clientId?: number}) {
    const [scopeOptions, setScopeOptions] = useState<Option[] | null>(null);

    useEffect(() => {
        getScopes().then(setScopeOptions);
    }, []);

    return (
        <DefinedForm<ClientPayload>
            defaultValues={{name: 'Test client', description: '', ispkcerequired: false, scopes: [], redirecturis: ['']}}
            id={clientId}
            load={getClient}
            create={createClient}
            update={patchClient}
        >
            <TextField
                name="name"
                label="Name"
                rules={[required('Enter a client name'), maxLength(15, 'Name must be 15 characters or fewer')]}
            />
            <TextareaField name="description" label="Description" />
            <SwitchField name="ispkcerequired" label="Is PKCE required" />

            <RepeaterField
                name="redirecturis"
                label="Redirect URIs"
                addLabel="Add redirect URI"
                hideIf={checked('ispkcerequired')}
                rules={[minItems(1, 'Enter at least one redirect URI')]}
            >
                {(index) => (
                    <TextField
                        id={`oauth2client_redirecturis_${index}`}
                        name={`redirecturis.${index}`}
                        label={`Redirect URI ${index + 1}`}
                        hideLabel
                        rules={[required('Enter a redirect URI'), url('Enter a valid URL')]}
                    />
                )}
            </RepeaterField>

            <CheckboxGroupField
                name="scopes"
                label="Scopes"
                options={scopeOptions}
                rules={[minItems(1, 'Select at least one scope')]}
            />
        </DefinedForm>
    );
}
