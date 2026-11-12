import {useState} from 'react';
import {Alert} from '@moodlehq/design-system';
import {MoodleForm, FormSlot, ExtensionModules, sendFormEndpointRequest} from '@moodle/lms/core/forms';
import {
    TextField,
    SwitchField,
    RadioField,
    CheckboxField,
    GroupField,
    RepeaterField,
    SectionField,
} from '@moodle/lms/core/forms/fields';
import {required, requiredIf, maxLength, minItems, pattern, url, validIf} from '@moodle/lms/core/forms/rules';
import {RatingField} from './fields/RatingField';
import {ColorField} from './fields/ColorField';
import {CountedTextareaField} from './fields/CountedTextareaField';

const TITLE_MAX_LENGTH = 50;
const DESCRIPTION_MAX_LENGTH = 200;
const SLUG = /^[a-z0-9-]+$/;
const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

type Props = {
    // Plugins can extend the form: their modules are loaded with it and their initial values go in `extensions`.
    extensionModules?: string[];
    extensionValues?: Record<string, Record<string, unknown>>;
};

type TestValues = {
    title: string;
    description: string;
    priority: string;
    notify: boolean;
    notifyEmail: string;
    tags: string[];
    emails: string[];
    rating: string;
    color: string;
    internalNote: string;
    externalId: string;
    slug: string;
    website: string;
    // Values of plugin form extensions, by component.
    extensions: Record<string, Record<string, unknown>>;
};

const TAGS = ['alpha', 'beta', 'gamma'];

/**
 * A dummy form for trying out the forms system: core fields, custom fields (./fields) and plugin extensions.
 * It is shown by /test.php and saved with the dummy endpoint /core/test/form.
 */
function TestForm({extensionModules, extensionValues}: Props) {
    const [saved, setSaved] = useState<unknown>(null);

    return (
        <ExtensionModules modules={extensionModules}>
            <MoodleForm<TestValues>
                defaultValues={{
                    title: '',
                    description: '',
                    priority: 'normal',
                    notify: true,
                    notifyEmail: '',
                    tags: [],
                    emails: [''],
                    rating: '3',
                    color: '#3366ff',
                    internalNote: '',
                    externalId: '',
                    slug: '',
                    website: '',
                    extensions: extensionValues ?? {},
                }}
                submit={{
                    action: (payload) => sendFormEndpointRequest('/core/test/form', 'POST').save(payload),
                    label: 'Save test form',
                    onSuccess: setSaved,
                }}
            >
                {saved !== null && (
                    <>
                        <Alert type="success" message="Saved" />
                        <pre>{JSON.stringify(saved, null, 2)}</pre>
                    </>
                )}
                <TextField
                    name="title"
                    label="Title"
                    rules={[required('The title is required'), maxLength(TITLE_MAX_LENGTH, 'The title is too long')]}
                />
                <CountedTextareaField
                    name="description"
                    label="Description"
                    maxLength={DESCRIPTION_MAX_LENGTH}
                    rules={[maxLength(DESCRIPTION_MAX_LENGTH, 'The description is too long')]}
                />
                <FormSlot name="core_test_form.general" />
                <GroupField name="priority" label="Priority" layout="column">
                    <RadioField name="priority" value="low" label="Low" />
                    <RadioField name="priority" value="normal" label="Normal" />
                    <RadioField name="priority" value="high" label="High" />
                </GroupField>
                <SwitchField name="notify" label="Notify me when it is done" />
                <TextField
                    name="notifyEmail"
                    label="Notification email"
                    hideIf={(v: TestValues) => !v.notify}
                    rules={[
                        requiredIf((v: TestValues) => v.notify, 'Enter an email to notify'),
                        pattern(EMAIL, 'This is not a valid email address'),
                    ]}
                />
                <GroupField
                    name="tags"
                    label="Tags"
                    layout="column"
                    rules={[minItems(1, 'Select at least one tag')]}
                >
                    {TAGS.map((tag) => <CheckboxField key={tag} name="tags" value={tag} label={tag} />)}
                </GroupField>
                <RepeaterField
                    name="emails"
                    label="Emails"
                    addLabel="Add an email"
                    removeLabel="Remove"
                    emptyItem=""
                >
                    <TextField label="Email" hideLabel rules={[pattern(EMAIL, 'This is not a valid email address')]} />
                </RepeaterField>
                <TextField
                    name="externalId"
                    label="External id (advanced)"
                    advanced
                    rules={[required('The external id is required'), pattern(SLUG, 'Use lowercase letters, numbers and dashes')]}
                />
                <SectionField title="Appearance" defaultOpen={false}>
                    <TextField
                        name="slug"
                        label="Slug"
                        rules={[required('The slug is required'), pattern(SLUG, 'Use lowercase letters, numbers and dashes')]}
                    />
                    <TextField name="website" label="Website" rules={[url('Enter a valid URL')]} />
                    <RatingField
                        name="rating"
                        label="Rating"
                        rules={[validIf((rating) => Number(rating) >= 1, 'Choose a rating')]}
                    />
                    <ColorField name="color" label="Colour" />
                    <TextField
                        name="internalNote"
                        label="Internal note (advanced)"
                        advanced
                        rules={[maxLength(20, 'The note is too long')]}
                    />
                </SectionField>
                <FormSlot name="core_test_form.footer" />
            </MoodleForm>
        </ExtensionModules>
    );
}

export default TestForm;
