import {Suspense, use} from 'react';
import {getString} from '@moodle/lms/core/stringUtils';
import {MoodleForm, FormSlot, ExtensionModules} from '@moodle/lms/core/forms';
import {
    GroupField,
    CheckboxField,
    RepeaterField,
    TextField,
    TextareaField,
} from '@moodle/lms/core/forms/fields';
import {required, maxLength, url, minItems} from '@moodle/lms/core/forms/rules';

const NAME_MAX_LENGTH = 255;

type EditValues = {
    name: string;
    description: string;
    ispkcerequired: boolean;
    scopes: string[];
    redirecturis: string[];
    // Values of plugin form extensions, by component.
    extensions?: Record<string, Record<string, unknown>>;
};

type Props = {
    clientId: number;
    // Which optional fields apply to this client.
    hasAuthCode: boolean;
    isConfidential: boolean;
    // Scope names and descriptions come from the scope classes, so they arrive as plain text.
    scopes: {value: string; label: string; description: string}[];
    listUrl: string;
    // Plugins can extend the form: their modules are loaded with it and their values come with the client's `extensions`.
    extensionModules?: string[];
};

function EditClientForm({
    clientId, hasAuthCode, isConfidential, scopes, listUrl, extensionModules,
}: Props) {
    // Every getString starts before any `use`, so the strings load in one batch.
    const s = {
        name: getString('name'),
        nameRequired: getString('required'),
        nameTooLong: getString('maximumchars', 'core', NAME_MAX_LENGTH),
        description: getString('description'),
        redirectUris: getString('oauth2server_clientcallbackuris', 'admin'),
        redirectUriRequired: getString('oauth2server_clientcallbackurirequired', 'admin'),
        redirectUriInvalid: getString('oauth2server_clientcallbackurisinvalid', 'admin'),
        addRedirectUri: getString('oauth2server_clientaddcallbackuri', 'admin'),
        remove: getString('remove'),
        pkce: getString('oauth2server_clientpkcerequired', 'admin'),
        pkceHelp: getString('oauth2server_clientpkcehelpconfidential', 'admin'),
        scopes: getString('oauth2server_scopes', 'admin'),
        scopesRequired: getString('oauth2serverclientnoscopes', 'error'),
    };
    const redirectUris = use(s.redirectUris);
    const redirectUriRequired = use(s.redirectUriRequired);
    const redirectUriInvalid = use(s.redirectUriInvalid);
    const scopesRequired = use(s.scopesRequired);

    return (
        <ExtensionModules modules={extensionModules}>
        <MoodleForm<EditValues>
            endpoint="/admin/oauth2/server/clients"
            resourceId={clientId}
            submit={{
                onSuccess: () => {
                    window.location.href = listUrl;
                },
            }}
            cancel={{action: () => {
                window.location.href = listUrl;
            }}}
        >
            <TextField
                name="name"
                label={use(s.name)}
                rules={[required(use(s.nameRequired)), maxLength(NAME_MAX_LENGTH, use(s.nameTooLong))]}
            />
            <TextareaField name="description" label={use(s.description)} />
            <FormSlot name="core_admin_oauth2_client.general" />
            {hasAuthCode && (
                <RepeaterField
                    name="redirecturis"
                    rules={[minItems(1, redirectUriRequired)]}
                    label={redirectUris}
                    addLabel={use(s.addRedirectUri)}
                    removeLabel={use(s.remove)}
                >
                    <TextField label={redirectUris} hideLabel rules={[url(redirectUriInvalid)]} />
                </RepeaterField>
            )}
            {hasAuthCode && isConfidential && (
                <CheckboxField name="ispkcerequired" label={use(s.pkce)} supportingText={use(s.pkceHelp)} />
            )}
            <GroupField
                name="scopes"
                label={use(s.scopes)}
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
            <FormSlot name="core_admin_oauth2_client.footer" />
        </MoodleForm>
        </ExtensionModules>
    );
}

// The strings load asynchronously, so the form renders under a Suspense boundary.
export default function EditClientFormLoader(props: Props) {
    return (
        <Suspense fallback={null}>
            <EditClientForm {...props} />
        </Suspense>
    );
}
