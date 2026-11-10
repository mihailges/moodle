import {Suspense, use} from 'react';
import {Alert} from '@moodlehq/design-system';
import {getString} from '@moodle/lms/core/stringUtils';
import {MoodleForm, sendFormEndpointRequest} from '@moodle/lms/core/forms';
import {
    GroupField,
    RadioField,
    CheckboxField,
    RepeaterField,
    StaticField,
    TextField,
    TextareaField,
} from '@moodle/lms/core/forms/elements';
import {required, maxLength, url, minItems} from '@moodle/lms/core/forms/rules';

type Props = {
    // The caller decides: create a client, or update the existing one (then clientId is required).
    mode: 'create' | 'update';
    clientId?: number;
    // Scope names and descriptions come from the scope classes, so they arrive as plain text.
    scopes: {value: string; label: string; description: string}[];
    listUrl: string;
};

type ClientPayload = {
    name: string;
    description: string;
    clienttype: 'confidential' | 'public';
    flows: string[];
    redirecturis: string[];
    ispkcerequired: boolean;
    scopes: string[];
};

const CLIENTS_URL = '/admin/oauth2/server/clients';

// Values can be empty while a record loads, so the conditions don't assume any field is set.
const isPublic = (v: Partial<ClientPayload>) => v.clienttype === 'public';
const supportsAuthCode = (v: Partial<ClientPayload>) => isPublic(v) || Boolean(v.flows?.includes('auth_code'));

function ClientForm({mode, clientId, scopes, listUrl}: Props) {
    const isCreate = mode === 'create';
    const goToList = () => {
        window.location.href = listUrl;
    };
    // Create and update differ only in whether a record is loaded and what saving does.
    const create = {
        submit: {
            action: (payload: ClientPayload) => sendFormEndpointRequest('/admin/oauth2/server/clients', 'POST').save(payload),
            label: use(getString('oauth2server_clientcreate', 'admin')),
            onSuccess: goToList,
        },
    };
    const update = {
        load: async () => ({
            values: await sendFormEndpointRequest(`/admin/oauth2/server/clients/${clientId}`).get() as ClientPayload,
        }),
        submit: {
            action: (payload: ClientPayload) =>
                sendFormEndpointRequest(`/admin/oauth2/server/clients/${clientId}`, 'PATCH').save(payload),
            onSuccess: goToList,
        },
    };

    return (
        <MoodleForm<ClientPayload>
            {...(isCreate ? create : update)}
            cancel={{action: goToList}}
        >
            <TextField
                name="name"
                label={use(getString('name'))}
                rules={[
                    required(use(getString('required'))),
                    maxLength(255, use(getString('maximumchars', 'core', 255))),
                ]}
            />
            <TextareaField name="description" label={use(getString('description'))} />
            {isCreate && (
            <GroupField
                name="clienttype"
                label={use(getString('oauth2server_clienttype', 'admin'))}
                layout="column"
                defaultValue="confidential"
            >
                <RadioField
                    name="clienttype"
                    value="confidential"
                    label={use(getString('oauth2server_clienttypeconfidential', 'admin'))}
                    supportingText={use(getString('oauth2server_clienttypeconfidentialdesc', 'admin'))}
                />
                <RadioField
                    name="clienttype"
                    value="public"
                    label={use(getString('oauth2server_clienttypepublic', 'admin'))}
                    supportingText={use(getString('oauth2server_clienttypepublicdesc', 'admin'))}
                />
            </GroupField>
            )}
            {isCreate && (
            <GroupField
                name="flows"
                label={use(getString('oauth2server_clientprimaryflows', 'admin'))}
                layout="column"
                defaultValue={['auth_code']}
                defaultIf={(v) => (isPublic(v) ? ['auth_code'] : undefined)}
                disableIf={isPublic}
                rules={[minItems(1, use(getString('oauth2server_clientmustselectprimaryflow', 'admin')))]}
            >
                <CheckboxField
                    name="flows"
                    value="auth_code"
                    label={use(getString('oauth2server_clientgranttypeauthcode', 'admin'))}
                    supportingText={use(getString('oauth2server_clientgranttypeauthcodedesc', 'admin'))}
                />
                <CheckboxField
                    name="flows"
                    value="client_credentials"
                    label={use(getString('oauth2server_clientgranttypeclientcreds', 'admin'))}
                    supportingText={use(getString('oauth2server_clientgranttypeclientcredsdesc', 'admin'))}
                />
            </GroupField>
            )}
            {isCreate && (
            <StaticField hideIf={(v) => isPublic(v) || !v.flows?.includes('client_credentials')}>
                <Alert type="warning" message={use(getString('oauth2server_clientgranttypeclientcredswarning', 'admin'))} />
            </StaticField>
            )}
            <RepeaterField
                name="redirecturis"
                rules={[minItems(1, use(getString('oauth2server_clientcallbackurirequired', 'admin')))]}
                label={use(getString('oauth2server_clientcallbackuris', 'admin'))}
                addLabel={use(getString('oauth2server_clientaddcallbackuri', 'admin'))}
                removeLabel={use(getString('remove'))}
                defaultValue={['']}
                hideIf={(v) => !supportsAuthCode(v)}
            >
                <TextField
                    label={use(getString('oauth2server_clientcallbackuris', 'admin'))}
                    hideLabel
                    rules={[url(use(getString('oauth2server_clientcallbackurisinvalid', 'admin')))]}
                />
            </RepeaterField>
            <CheckboxField
                name="ispkcerequired"
                label={use(getString('oauth2server_clientpkcerequired', 'admin'))}
                supportingText={use(getString(
                    isCreate ? 'oauth2server_clientpkcehelp' : 'oauth2server_clientpkcehelpconfidential',
                    'admin',
                ))}
                hideIf={(v) => !v.flows?.includes('auth_code')}
                defaultValue={true}
                defaultIf={(v) => isPublic(v) ? true : undefined}
                disableIf={(v) => isPublic(v)}
            />
            <GroupField
                name="scopes"
                label={use(getString('oauth2server_scopes', 'admin'))}
                layout="column"
                rules={[minItems(1, use(getString('oauth2serverclientnoscopes', 'error')))]}
            >
                {scopes.map((scope) => (
                    <CheckboxField
                        key={scope.value}
                        name="scopes"
                        value={scope.value}
                        label={scope.label}
                        supportingText={scope.description}
                    />
                ))}
            </GroupField>
            {isCreate && (
                <StaticField>
                    <Alert type="warning" message={use(getString('oauth2server_clientcreationwarning', 'admin'))} />
                </StaticField>
            )}
        </MoodleForm>
    );
}

// The strings load asynchronously, so the form renders under a Suspense boundary.
export default function ClientFormLoader(props: Props) {
    return (
        <Suspense fallback={null}>
            <ClientForm {...props} />
        </Suspense>
    );
}
