// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Tests for core/forms MoodleForm: rule and condition enforcement, dirty-only updates and server errors.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {render, screen, waitFor, fireEvent} from '@testing-library/react';
import {MoodleForm, FormValidationError} from '@moodle/lms/core/forms';
import {
    TextField, SwitchField, RepeaterField, GroupField, CheckboxField, RadioField, StaticField,
    ActionButton, SectionField,
} from '@moodle/lms/core/forms/fields';
import {required, requiredIf, maxLength, minItems} from '@moodle/lms/core/forms/rules';
import Fetch from '@moodle/lms/core/fetch';

jest.mock('@moodle/lms/core/fetch', () => ({
    __esModule: true,
    default: {performGet: jest.fn(), performPost: jest.fn(), performPatch: jest.fn()},
}));

// @moodlehq/design-system is ESM only; stand in minimal markup carrying the props the fields pass.
jest.mock('@moodlehq/design-system', () => ({
    Input: ({label, invalid, invalidFeedback, hideLabel, ...rest}: any) => (
        <div>
            <label htmlFor={rest.name}>{label}</label>
            <input id={rest.name} aria-invalid={invalid || undefined} {...rest} />
            {invalid && <span role="alert">{invalidFeedback}</span>}
        </div>
    ),
    Switch: ({label, ...rest}: any) => (
        <div>
            <label htmlFor={rest.name}>{label}</label>
            <input id={rest.name} type="checkbox" role="switch" {...rest} />
        </div>
    ),
    Checkbox: ({label, supportingText, invalid, invalidFeedback, ...rest}: any) => (
        <div>
            <label>{label}<input type="checkbox" {...rest} /></label>
            {invalid && <span role="alert">{invalidFeedback}</span>}
        </div>
    ),
    Radio: ({label, invalid, invalidFeedback, ...rest}: any) => (
        <label>{label}<input type="radio" {...rest} /></label>
    ),
    Button: ({label, ...rest}: any) => <button {...rest}>{label}</button>,
}), {virtual: true});

type Values = {name: string; pkce: boolean; method: string};
const defaults: Values = {name: '', pkce: false, method: ''};

const click = (el: HTMLElement) => fireEvent.click(el);
const type = (el: HTMLElement, value: string) => fireEvent.change(el, {target: {value}});

const renderForm = (props: Partial<React.ComponentProps<typeof MoodleForm<Values>>> = {}) => {
    const create = jest.fn().mockResolvedValue(defaults);
    render(
        <MoodleForm<Values> defaultValues={defaults} submit={{action: create}} {...props}>
            <TextField name="name" label="Name" rules={[required('Enter a name'), maxLength(5, 'Too long')]} />
            <SwitchField name="pkce" label="PKCE" />
            <TextField name="method" label="Method" hideIf={(v: any) => !v.pkce} rules={[required('Choose a method')]} />
        </MoodleForm>,
    );
    return create;
};

describe('core/forms MoodleForm', () => {
    it('shows rule errors and does not submit invalid data', async() => {
        const create = renderForm();
        click(await screen.findByText('[save, core]'));
        expect(await screen.findByText('Enter a name')).toBeTruthy();
        expect(create).not.toHaveBeenCalled();
    });

    it('does not validate a hidden field, and does once it is shown', async() => {
        const create = renderForm();
        type(screen.getByLabelText('Name'), 'abc');
        click(await screen.findByText('[save, core]'));
        await waitFor(() => expect(create).toHaveBeenCalledTimes(1));

        click(screen.getByLabelText('PKCE'));
        click(await screen.findByText('[save, core]'));
        expect(await screen.findByText('Choose a method')).toBeTruthy();
        expect(create).toHaveBeenCalledTimes(1);
    });

    it('hides a repeater with hideIf, skipping its rows when validating', async() => {
        const create = jest.fn().mockResolvedValue(defaults);
        render(
            <MoodleForm<any> defaultValues={{name: 'ok', pkce: false, uris: ['']}} submit={{action: create}}>
                <SwitchField name="pkce" label="PKCE" />
                <RepeaterField name="uris" label="URIs" addLabel="Add" emptyItem="" hideIf={(v: any) => Boolean(v.pkce)}>
                    <TextField label="URI" rules={[required('Enter a URI')]} />
                </RepeaterField>
            </MoodleForm>,
        );
        expect(await (await screen.findAllByLabelText('URI'))[0]).toBeTruthy();
        click(screen.getByLabelText('PKCE'));
        expect(screen.queryAllByLabelText('URI')).toHaveLength(0);
        click(await screen.findByText('[save, core]'));
        await waitFor(() => expect(create).toHaveBeenCalledTimes(1));
    });

    it('validates a repeater as a whole with its own rules', async() => {
        const create = jest.fn().mockResolvedValue(defaults);
        render(
            <MoodleForm<any> defaultValues={{uris: []}} submit={{action: create}}>
                <RepeaterField name="uris" label="URIs" addLabel="Add" emptyItem="" minRows={0}
                    rules={[minItems(1, 'Add at least one URI')]}>
                    <TextField label="URI" />
                </RepeaterField>
            </MoodleForm>,
        );
        click(await screen.findByText('[save, core]'));
        expect(await screen.findByText('Add at least one URI')).toBeTruthy();
        expect(create).not.toHaveBeenCalled();
    });

    it('clears the repeater\'s own error once a row is filled in', async() => {
        const create = jest.fn().mockResolvedValue(defaults);
        render(
            <MoodleForm<any> defaultValues={{uris: ['']}} submit={{action: create}}>
                <RepeaterField name="uris" label="URIs" addLabel="Add" emptyItem=""
                    rules={[minItems(1, 'Add at least one URI')]}>
                    <TextField label="URI" />
                </RepeaterField>
            </MoodleForm>,
        );
        click(await screen.findByText('[save, core]'));
        expect(await screen.findByText('Add at least one URI')).toBeTruthy();
        type(screen.getByLabelText('URI'), 'https://a.test');
        await waitFor(() => expect(screen.queryByText('Add at least one URI')).toBeNull());
    });

    it('focuses the first invalid field, opening a collapsed advanced one', async() => {
        render(
            <MoodleForm<any> defaultValues={{name: 'ok', note: ''}} submit={{action: jest.fn()}}>
                <TextField name="name" label="Name" rules={[required('Name needed')]} />
                <TextField name="note" label="Note" advanced rules={[required('Note needed')]} />
            </MoodleForm>,
        );
        click(await screen.findByText('[save, core]'));
        const note = await screen.findByLabelText('Note');
        await waitFor(() => expect(document.activeElement).toBe(note));
        expect(note.getAttribute('aria-invalid')).toBe('true');
    });

    it('focuses the repeater when its own rule fails', async() => {
        render(
            <MoodleForm<any> defaultValues={{uris: ['']}} submit={{action: jest.fn()}}>
                <RepeaterField name="uris" label="URIs" addLabel="Add" rules={[minItems(1, 'Add at least one URI')]}>
                    <TextField label="URI" />
                </RepeaterField>
            </MoodleForm>,
        );
        click(await screen.findByText('[save, core]'));
        const message = await screen.findByText('Add at least one URI');
        const fieldset = message.closest('fieldset')!;
        await waitFor(() => expect(document.activeElement).toBe(fieldset));
        expect(fieldset.getAttribute('aria-describedby')).toBe(message.id);
    });

    it('adds and removes plain-value rows, keeping values aligned', async() => {
        const create = jest.fn().mockResolvedValue(defaults);
        render(
            <MoodleForm<any> defaultValues={{uris: ['a', 'b', 'c']}} submit={{action: create}}>
                <RepeaterField name="uris" label="URIs" addLabel="Add">
                    <TextField label="URI" rules={[required('Enter a URI')]} />
                </RepeaterField>
            </MoodleForm>,
        );
        click(screen.getByLabelText('Remove URIs 1'));
        expect((screen.getAllByLabelText("URI")[0] as HTMLInputElement).value).toBe('b');
        click(screen.getByText('Add'));
        type(screen.getAllByLabelText('URI')[2], 'd');
        click(await screen.findByText('[save, core]'));
        await waitFor(() => expect(create).toHaveBeenCalledWith({uris: ['b', 'c', 'd']}));
    });

    it('validates each plain-value row and maps server errors by index', async() => {
        const create = jest.fn().mockRejectedValue(new FormValidationError({'uris.1': ['Bad URI']}));
        render(
            <MoodleForm<any> defaultValues={{uris: ['a', '']}} submit={{action: create}}>
                <RepeaterField name="uris" label="URIs" addLabel="Add">
                    <TextField label="URI" rules={[required('Enter a URI')]} />
                </RepeaterField>
            </MoodleForm>,
        );
        click(await screen.findByText('[save, core]'));
        expect(await screen.findByText('Enter a URI')).toBeTruthy();
        type(screen.getAllByLabelText('URI')[1], 'x');
        click(await screen.findByText('[save, core]'));
        expect(await screen.findByText('Bad URI')).toBeTruthy();
    });

    it('groups fields under one object, with child and group-level rules', async() => {
        const create = jest.fn().mockResolvedValue(defaults);
        const sameUnit = (v: any) => (v.value && !v.unit ? 'Enter a unit' : true);
        render(
            <MoodleForm<any> defaultValues={{duration: {value: '', unit: ''}}} submit={{action: create}}>
                <GroupField name="duration" label="Duration" rules={[sameUnit]}>
                    <TextField name="duration.value" label="Amount" rules={[maxLength(3, 'Too long')]} />
                    <TextField name="duration.unit" label="Unit" />
                </GroupField>
            </MoodleForm>,
        );
        type(screen.getByLabelText('Amount'), '12345');
        click(await screen.findByText('[save, core]'));
        expect(await screen.findByText('Too long')).toBeTruthy();
        type(screen.getByLabelText('Amount'), '12');
        click(await screen.findByText('[save, core]'));
        expect(await screen.findByText('Enter a unit')).toBeTruthy();
        type(screen.getByLabelText('Unit'), 'min');
        click(await screen.findByText('[save, core]'));
        await waitFor(() => expect(create).toHaveBeenCalledWith({duration: {value: '12', unit: 'min'}}));
    });

    it('renders arbitrary content in a static field, shown or hidden by a condition', async() => {
        render(
            <MoodleForm<any> defaultValues={{off: false}} submit={{action: jest.fn()}}>
                <SwitchField name="off" label="Off" />
                <StaticField hideIf={(v: any) => v.off}><p>Static content</p></StaticField>
            </MoodleForm>,
        );
        expect(await screen.findByText('Static content')).toBeTruthy();
        click(screen.getByLabelText('Off'));
        expect(screen.queryByText('Static content')).toBeNull();
    });

    it('hides or disables every field in a group with the group conditions', async() => {
        const create = jest.fn().mockResolvedValue(defaults);
        render(
            <MoodleForm<any> defaultValues={{off: false, lock: false, g: {a: ''}, h: {b: ''}}} submit={{action: create}}>
                <SwitchField name="off" label="Off" />
                <SwitchField name="lock" label="Lock" />
                <GroupField name="g" label="G" hideIf={(v: any) => Boolean(v.off)} disableIf={(v: any) => Boolean(v.lock)}>
                    <TextField name="g.a" label="A" rules={[required('A needed')]} />
                </GroupField>
            </MoodleForm>,
        );
        click(screen.getByLabelText('Lock'));
        expect((screen.getByLabelText('A') as HTMLInputElement).disabled).toBe(true);
        click(screen.getByLabelText('Off'));
        expect(screen.queryByLabelText('A')).toBeNull();
        click(await screen.findByText('[save, core]'));
        await waitFor(() => expect(create).toHaveBeenCalled());
        expect(screen.queryByText('A needed')).toBeNull();
    });

    it('sends the full values when editing', async() => {
        const load = jest.fn().mockResolvedValue({values: {name: 'old', pkce: false, method: ''}});
        const update = jest.fn().mockResolvedValue({name: 'new', pkce: false, method: ''});
        renderForm({load, submit: {action: (payload) => update(1, payload)}});
        const input = await screen.findByLabelText('Name');
        type(input, 'new');
        click(await screen.findByText('[save, core]'));
        await waitFor(() => expect(update).toHaveBeenCalledWith(1, {name: 'new', pkce: false, method: ''}));
    });

    it('shows server validation errors thrown by create, mapped onto fields and the form', async() => {
        const create = jest.fn().mockRejectedValue(new FormValidationError({name: ['Already exists']}, ['Form level problem']));
        renderForm({submit: {action: create}});
        type(screen.getByLabelText('Name'), 'abc');
        click(await screen.findByText('[save, core]'));
        expect(await screen.findByText('Already exists')).toBeTruthy();
        expect(await screen.findByText('Form level problem')).toBeTruthy();
    });

    it('applies server-disabled fields and maps server validation errors', async() => {
        const load = jest.fn().mockResolvedValue({values: {name: 'old', pkce: false, method: ''}, readonly: ['pkce']});
        const update = jest.fn().mockRejectedValue(new FormValidationError({name: ['Already exists']}));
        renderForm({load, submit: {action: (payload) => update(1, payload)}});
        expect(((await screen.findByLabelText('PKCE')) as HTMLInputElement).disabled).toBe(true);
        type(screen.getByLabelText('Name'), 'x');
        click(await screen.findByText('[save, core]'));
        expect(await screen.findByText('Already exists')).toBeTruthy();
    });

    it('builds a choice from radios and a list of options from checkboxes inside groups', async() => {
        const create = jest.fn().mockResolvedValue({});
        const onCancel = jest.fn();
        render(
            <MoodleForm<any>
                defaultValues={{type: 'confidential', scopes: []}}
                submit={{action: create, label: 'Create client'}}
                cancel={{action: onCancel, label: 'Back'}}
            >
                <GroupField name="type" label="Type" layout="column">
                    <RadioField name="type" value="confidential" label="Confidential" />
                    <RadioField name="type" value="public" label="Public" />
                </GroupField>
                <GroupField
                    name="scopes"
                    label="Scopes"
                    layout="column"
                    rules={[(v) => v.length > 0 || 'Select a scope']}
                >
                    <CheckboxField name="scopes" value="read" label="Read" />
                    <CheckboxField name="scopes" value="write" label="Write" />
                </GroupField>
            </MoodleForm>,
        );
        click(await screen.findByText('Create client'));
        expect(await screen.findAllByText('Select a scope')).toHaveLength(1);
        click(screen.getByLabelText('Public'));
        click(screen.getByLabelText('Write'));
        click(screen.getByText('Create client'));
        await waitFor(() => expect(create).toHaveBeenCalledWith({type: 'public', scopes: ['write']}));
        click(screen.getByText('Back'));
        expect(onCancel).toHaveBeenCalled();
    });

    it('places server errors on the closest field, or on the form when none matches', async() => {
        const create = jest.fn().mockRejectedValue(new FormValidationError({
            'scopes.read': ['Not allowed'],
            unknown: ['Unknown problem'],
        }));
        render(
            <MoodleForm<any> defaultValues={{scopes: []}} submit={{action: create}}>
                <GroupField name="scopes" label="Scopes">
                    <CheckboxField name="scopes" value="read" label="Read" />
                </GroupField>
            </MoodleForm>,
        );
        click(await screen.findByText('[save, core]'));
        expect(await screen.findByText('Not allowed')).toBeTruthy();
        expect(await screen.findByText('Unknown problem')).toBeTruthy();
    });

    it('shows no standard buttons without submit, and runs custom action buttons', async() => {
        const create = jest.fn().mockResolvedValue({});
        const onSuccess = jest.fn();
        render(
            <MoodleForm<any> defaultValues={{name: 'x'}}>
                <TextField name="name" label="Name" />
                <div>
                    <ActionButton label="Save and next" submit={{action: create, onSuccess}} />
                    <ActionButton label="Clear" onClick={(form) => form.setValue('name', '')} />
                </div>
            </MoodleForm>,
        );
        expect(screen.queryByText('[save, core]')).toBeNull();
        click(screen.getByText('Clear'));
        await waitFor(() => expect((screen.getByLabelText('Name') as HTMLInputElement).value).toBe(''));
        type(screen.getByLabelText('Name'), 'y');
        click(screen.getByText('Save and next'));
        await waitFor(() => expect(onSuccess).toHaveBeenCalled());
        expect(create).toHaveBeenCalledWith({name: 'y'});
    });

    it('shows preset checkbox values as checked and submits the changed list', async() => {
        const create = jest.fn().mockResolvedValue({});
        render(
            <MoodleForm<any> defaultValues={{scopes: ['write']}} submit={{action: create}}>
                <GroupField name="scopes" label="Scopes">
                    <CheckboxField name="scopes" value="read" label="Read" />
                    <CheckboxField name="scopes" value="write" label="Write" />
                </GroupField>
            </MoodleForm>,
        );
        expect((screen.getByLabelText('Write') as HTMLInputElement).checked).toBe(true);
        click(screen.getByLabelText('Read'));
        click(screen.getByLabelText('Write'));
        click(await screen.findByText('[save, core]'));
        await waitFor(() => expect(create).toHaveBeenCalledWith({scopes: ['read']}));
    });

    describe('with an api', () => {
        const json = (body: unknown) => Promise.resolve({json: () => Promise.resolve(body)});
        const route = {endpoint: '/test/things'};

        beforeEach(() => {
            Object.values(Fetch).forEach((fn) => (fn as jest.Mock).mockReset());
        });

        it('creates with a POST to the path', async() => {
            (Fetch.performPost as jest.Mock).mockReturnValue(json({id: 5}));
            const onSuccess = jest.fn();
            render(
                <MoodleForm<any> defaultValues={{name: 'a'}} {...route} submit={{onSuccess}}>
                    <TextField name="name" label="Name" />
                </MoodleForm>,
            );
            click(await screen.findByText('[save, core]'));
            await waitFor(() => expect(onSuccess).toHaveBeenCalledWith({id: 5}));
            expect(Fetch.performPost).toHaveBeenCalledWith(
                '', '/test/things', {body: {name: 'a'}, rejectWithResponse: true},
            );
        });

        it('loads with a GET and saves with a PATCH on the given endpoint, mapping a 422 onto the field', async() => {
            (Fetch.performGet as jest.Mock).mockReturnValue(json({name: 'old'}));
            class FakeResponse {
                status = 422;
                json = () => Promise.resolve({errors: {name: ['Taken']}});
            }
            (global as any).Response = FakeResponse;
            (Fetch.performPatch as jest.Mock).mockRejectedValue(new FakeResponse());
            render(
                <MoodleForm<any> endpoint="/test/things/7" method="PATCH">
                    <TextField name="name" label="Name" />
                </MoodleForm>,
            );
            expect(((await screen.findByLabelText('Name')) as HTMLInputElement).value).toBe('old');
            expect(Fetch.performGet).toHaveBeenCalledWith('', '/test/things/7', {cachekey: null});
            click(await screen.findByText('[save, core]'));
            expect(await screen.findByText('Taken')).toBeTruthy();
            expect(Fetch.performPatch).toHaveBeenCalledWith(
                '', '/test/things/7', {body: {name: 'old'}, rejectWithResponse: true},
            );
        });
    });

    it('runs a field\'s rules in order, awaiting async ones, and shows the first failure', async() => {
        const create = jest.fn().mockResolvedValue({});
        render(
            <MoodleForm<any> defaultValues={{name: ''}} submit={{action: create}}>
                <TextField
                    name="name"
                    label="Name"
                    rules={[required('req'), async(v: string) => v !== 'taken' || 'taken', maxLength(5, 'long')]}
                />
            </MoodleForm>,
        );
        click(await screen.findByText('[save, core]'));
        expect(await screen.findByText('req')).toBeTruthy();

        type(screen.getByLabelText('Name'), 'taken');
        click(screen.getByText('[save, core]'));
        expect(await screen.findByText('taken')).toBeTruthy();
        expect(screen.queryByText('req')).toBeNull();
        expect(create).not.toHaveBeenCalled();
    });

    it('marks fields mandatory from their rules, following requiredIf conditions and group rules', async() => {
        render(
            <MoodleForm<any> defaultValues={{name: '', pkce: false, method: '', scopes: []}} submit={{action: jest.fn()}}>
                <TextField name="name" label="Name" rules={[required('Enter a name')]} />
                <TextField name="nick" label="Nickname" rules={[maxLength(5, 'Too long')]} />
                <SwitchField name="pkce" label="PKCE" />
                <TextField name="method" label="Method" rules={[requiredIf((v: any) => v.pkce, 'Choose a method')]} />
                <GroupField name="scopes" label="Scopes" layout="column" rules={[minItems(1, 'Select a scope')]}>
                    <CheckboxField name="scopes" value="read" label="Read" />
                </GroupField>
            </MoodleForm>,
        );
        const isRequired = (label: string) => (screen.getByLabelText(label) as HTMLInputElement).required;
        expect(isRequired('Name')).toBe(true);
        expect(isRequired('Nickname')).toBe(false);
        expect(isRequired('Method')).toBe(false);
        expect(screen.getByText('Scopes').parentElement!.textContent).toContain('*');

        click(screen.getByLabelText('PKCE'));
        await waitFor(() => expect(isRequired('Method')).toBe(true));
    });

    it('hides advanced fields anywhere in the form behind "Show more", still validating and submitting them', async() => {
        const create = jest.fn().mockResolvedValue({});
        render(
            <MoodleForm<any> defaultValues={{name: 'a', note: 'kept', other: 'ok'}} submit={{action: create}}>
                <TextField name="note" label="Note" advanced rules={[maxLength(2, 'Note too long')]} />
                <SectionField title="Section">
                    <TextField name="name" label="Name" />
                    <TextField name="other" label="Other" advanced />
                </SectionField>
            </MoodleForm>,
        );
        const hidden = (label: string) => screen.queryByLabelText(label) === null;
        expect(hidden('Note')).toBe(true);
        expect(hidden('Other')).toBe(true);
        expect(hidden('Name')).toBe(false);
        click(await screen.findByText('[showmore, core]'));
        expect(hidden('Note')).toBe(false);
        expect(hidden('Other')).toBe(false);
        click(screen.getByText('[showless, core]'));
        expect(hidden('Note')).toBe(true);

        click(screen.getByText('[save, core]'));
        expect(await screen.findByText('Note too long')).toBeTruthy();
        expect(hidden('Note')).toBe(false);
        expect(create).not.toHaveBeenCalled();

        type(screen.getByLabelText('Note'), 'ok');
        click(screen.getByText('[save, core]'));
        await waitFor(() => expect(create).toHaveBeenCalledWith({name: 'a', note: 'ok', other: 'ok'}));
    });

    it('keeps the fields of a collapsed section and opens it when one has an error', async() => {
        const create = jest.fn().mockResolvedValue({});
        render(
            <MoodleForm<any> defaultValues={{note: 'long'}} submit={{action: create}}>
                <SectionField title="More" defaultOpen={false}>
                    <TextField name="note" label="Note" rules={[maxLength(2, 'Note too long')]} />
                </SectionField>
            </MoodleForm>,
        );
        const details = screen.getByText('More').closest('details')!;
        expect(details.open).toBe(false);
        click(await screen.findByText('[save, core]'));
        expect(await screen.findByText('Note too long')).toBeTruthy();
        expect(details.open).toBe(true);
    });
});
