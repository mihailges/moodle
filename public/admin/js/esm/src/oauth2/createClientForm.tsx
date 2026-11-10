import {Suspense, use} from 'react';
import {Alert} from '@moodlehq/design-system';
import {getString} from '@moodle/lms/core/stringUtils';
import {MoodleForm} from '@moodle/lms/core/forms';
import {
    GroupField,
    RadioField,
    CheckboxField,
    RepeaterField,
    StaticField,
    TextField,
    TextareaField,
} from '@moodle/lms/core/forms/fields';
import {required, maxLength, url, minItems} from '@moodle/lms/core/forms/rules';

type Props = {
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

type CreatedClient = {id: number; isconfidential: boolean; redirecturl: string};

const isPublic = (v: ClientPayload) => v.clienttype === 'public';

function CreateClientForm({scopes, listUrl}: Props) {
    // Every getString starts before any `use`, so the strings load in one batch.
    const flowRequired = use(getString('oauth2server_clientmustselectprimaryflow', 'admin'));
    const scopesRequired = use(getString('oauth2serverclientnoscopes', 'error'));
    const redirectUris = use(getString('oauth2server_clientcallbackuris', 'admin'));
    const redirectUriRequired = use(getString('oauth2server_clientcallbackurirequired', 'admin'));
    const redirectUriInvalid = use(getString('oauth2server_clientcallbackurisinvalid', 'admin'));

    return (
        <MoodleForm<ClientPayload, ClientPayload, CreatedClient>
            defaultValues={{
                name: '',
                description: '',
                clienttype: 'confidential',
                flows: ['auth_code'],
                redirecturis: [''],
                ispkcerequired: true,
                scopes: [],
            }}
            endpoint="/admin/oauth2/server/clients"
            submit={{
                label: use(getString('oauth2server_clientcreate', 'admin')),
                onSuccess: (saved) => {
                    window.location.href = saved.redirecturl;
                },
            }}
            cancel={{
                action: () => {
                    window.location.href = listUrl;
                },
            }}
        >
            <TextField
                name="name"
                label={use(getString('name'))}
                rules={[
                    required(use(getString('required'))),
                    maxLength(255, use(getString('maximumchars', 'core', 255)))
                ]}
            />
            <TextareaField name="description" label={use(getString('description'))} />
            <GroupField name="clienttype" label={use(getString('oauth2server_clienttype', 'admin'))} layout="column">
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
            <GroupField
                name="flows"
                label={use(getString('oauth2server_clientprimaryflows', 'admin'))}
                layout="column"
                disableIf={isPublic}
                rules={[minItems(1, flowRequired)]}
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
            <StaticField hideIf={(v) => isPublic(v) || !v.flows.includes('client_credentials')}>
                <Alert type="warning" message={use(getString('oauth2server_clientgranttypeclientcredswarning', 'admin'))} />
            </StaticField>
            <RepeaterField
                name="redirecturis"
                rules={[minItems(1, redirectUriRequired)]}
                label={redirectUris}
                addLabel={use(getString('oauth2server_clientaddcallbackuri', 'admin'))}
                removeLabel={use(getString('remove'))}
                hideIf={(v) => !isPublic(v) && !v.flows.includes('auth_code')}
            >
                <TextField label={redirectUris} hideLabel rules={[url(redirectUriInvalid)]} />
            </RepeaterField>
            <CheckboxField
                name="ispkcerequired"
                label={use(getString('oauth2server_clientpkcerequired', 'admin'))}
                supportingText={use(getString('oauth2server_clientpkcehelp', 'admin'))}
                hideIf={(v) => isPublic(v) || !v.flows.includes('auth_code')}
            />
            <GroupField
                name="scopes"
                label={use(getString('oauth2server_scopes', 'admin'))}
                layout="column"
                rules={[minItems(1, scopesRequired)]}
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
            <StaticField>
                <Alert type="warning" message={use(getString('oauth2server_clientcreationwarning', 'admin'))} />
            </StaticField>
        </MoodleForm>
    );
}

// The strings load asynchronously, so the form renders under a Suspense boundary.
export default function CreateClientFormLoader(props: Props) {
    return (
        <Suspense fallback={null}>
            <CreateClientForm {...props} />
        </Suspense>
    );
}
