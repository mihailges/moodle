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
 * Tests for core/forms DefinedForm: rule and condition enforcement, dirty-only updates and server errors.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {render, screen, waitFor, fireEvent} from '@testing-library/react';
import {DefinedForm, FormValidationError} from '@moodle/lms/core/forms';
import {TextField, SwitchField, RepeaterField} from '@moodle/lms/core/forms/fields';
import {required, maxLength, minItems} from '@moodle/lms/core/forms/rules';
import {checked, not} from '@moodle/lms/core/forms/conditions';

// @moodlehq/design-system is ESM only; stand in minimal markup carrying the props the fields pass.
jest.mock('@moodlehq/design-system', () => ({
    Input: ({label, invalid, invalidFeedback, hideLabel, ...rest}: any) => (
        <div>
            <label htmlFor={rest.name}>{label}</label>
            <input id={rest.name} {...rest} />
            {invalid && <span role="alert">{invalidFeedback}</span>}
        </div>
    ),
    Switch: ({label, ...rest}: any) => (
        <div>
            <label htmlFor={rest.name}>{label}</label>
            <input id={rest.name} type="checkbox" role="switch" {...rest} />
        </div>
    ),
    Button: ({label, ...rest}: any) => <button {...rest}>{label}</button>,
}), {virtual: true});

type Values = {name: string; pkce: boolean; method: string};
const defaults: Values = {name: '', pkce: false, method: ''};

const click = (el: HTMLElement) => fireEvent.click(el);
const type = (el: HTMLElement, value: string) => fireEvent.change(el, {target: {value}});

const renderForm = (props: Partial<React.ComponentProps<typeof DefinedForm<Values>>> = {}) => {
    const create = jest.fn().mockResolvedValue(defaults);
    render(
        <DefinedForm<Values> defaultValues={defaults} create={create} {...props}>
            <TextField name="name" label="Name" rules={[required('Enter a name'), maxLength(5, 'Too long')]} />
            <SwitchField name="pkce" label="PKCE" />
            <TextField name="method" label="Method" hideIf={not(checked('pkce'))} rules={[required('Choose a method')]} />
        </DefinedForm>,
    );
    return create;
};

describe('core/forms DefinedForm', () => {
    it('shows rule errors and does not submit invalid data', async() => {
        const create = renderForm();
        click(screen.getByText('Save'));
        expect(await screen.findByText('Enter a name')).toBeTruthy();
        expect(create).not.toHaveBeenCalled();
    });

    it('does not validate or submit a hidden field, and does once it is shown', async() => {
        const create = renderForm();
        type(screen.getByLabelText('Name'), 'abc');
        click(screen.getByText('Save'));
        await waitFor(() => expect(create).toHaveBeenCalledTimes(1));
        expect(create.mock.calls[0][0]).not.toHaveProperty('method');

        click(screen.getByLabelText('PKCE'));
        click(screen.getByText('Save'));
        expect(await screen.findByText('Choose a method')).toBeTruthy();
        expect(create).toHaveBeenCalledTimes(1);
    });

    it('hides a repeater with hideIf, skipping its rows when validating and submitting', async() => {
        const create = jest.fn().mockResolvedValue(defaults);
        render(
            <DefinedForm<any> defaultValues={{name: 'ok', pkce: false, uris: ['']}} create={create}>
                <SwitchField name="pkce" label="PKCE" />
                <RepeaterField name="uris" label="URIs" addLabel="Add" emptyItem="" hideIf={checked('pkce')}>
                    {(i) => <TextField name={`uris.${i}`} label={`URI ${i + 1}`} rules={[required('Enter a URI')]} />}
                </RepeaterField>
            </DefinedForm>,
        );
        expect(screen.queryByLabelText('URI 1')).toBeTruthy();
        click(screen.getByLabelText('PKCE'));
        expect(screen.queryByLabelText('URI 1')).toBeNull();
        click(screen.getByText('Save'));
        await waitFor(() => expect(create).toHaveBeenCalledTimes(1));
        expect(create.mock.calls[0][0]).not.toHaveProperty('uris');
    });

    it('validates a repeater as a whole with its own rules', async() => {
        const create = jest.fn().mockResolvedValue(defaults);
        render(
            <DefinedForm<any> defaultValues={{uris: []}} create={create}>
                <RepeaterField name="uris" label="URIs" addLabel="Add" emptyItem="" minRows={0}
                    rules={[minItems(1, 'Add at least one URI')]}>
                    {(i) => <TextField name={`uris.${i}`} label={`URI ${i + 1}`} />}
                </RepeaterField>
            </DefinedForm>,
        );
        click(screen.getByText('Save'));
        expect(await screen.findByText('Add at least one URI')).toBeTruthy();
        expect(create).not.toHaveBeenCalled();
    });

    it('adds and removes plain-value rows, keeping values aligned', async() => {
        const create = jest.fn().mockResolvedValue(defaults);
        render(
            <DefinedForm<any> defaultValues={{uris: ['a', 'b', 'c']}} create={create}>
                <RepeaterField name="uris" label="URIs" addLabel="Add">
                    {(i) => <TextField name={`uris.${i}`} label={`URI ${i + 1}`} rules={[required('Enter a URI')]} />}
                </RepeaterField>
            </DefinedForm>,
        );
        click(screen.getByLabelText('Remove URIs 1'));
        expect((screen.getByLabelText('URI 1') as HTMLInputElement).value).toBe('b');
        click(screen.getByText('Add'));
        type(screen.getByLabelText('URI 3'), 'd');
        click(screen.getByText('Save'));
        await waitFor(() => expect(create).toHaveBeenCalledWith({uris: ['b', 'c', 'd']}));
    });

    it('validates each plain-value row and maps server errors by index', async() => {
        const create = jest.fn().mockRejectedValue(new FormValidationError({'uris.1': ['Bad URI']}));
        render(
            <DefinedForm<any> defaultValues={{uris: ['a', '']}} create={create}>
                <RepeaterField name="uris" label="URIs" addLabel="Add">
                    {(i) => <TextField name={`uris.${i}`} label={`URI ${i + 1}`} rules={[required('Enter a URI')]} />}
                </RepeaterField>
            </DefinedForm>,
        );
        click(screen.getByText('Save'));
        expect(await screen.findByText('Enter a URI')).toBeTruthy();
        type(screen.getByLabelText('URI 2'), 'x');
        click(screen.getByText('Save'));
        expect(await screen.findByText('Bad URI')).toBeTruthy();
    });

    it('updates only the changed fields when editing', async() => {
        const load = jest.fn().mockResolvedValue({values: {name: 'old', pkce: false, method: ''}});
        const update = jest.fn().mockResolvedValue({name: 'new', pkce: false, method: ''});
        renderForm({id: 1, load, update});
        const input = await screen.findByLabelText('Name');
        type(input, 'new');
        click(screen.getByText('Save'));
        await waitFor(() => expect(update).toHaveBeenCalledWith(1, {name: 'new'}));
    });

    it('shows server validation errors thrown by create, mapped onto fields and the form', async() => {
        const create = jest.fn().mockRejectedValue(new FormValidationError({name: ['Already exists']}, ['Form level problem']));
        renderForm({create});
        type(screen.getByLabelText('Name'), 'abc');
        click(screen.getByText('Save'));
        expect(await screen.findByText('Already exists')).toBeTruthy();
        expect(await screen.findByText('Form level problem')).toBeTruthy();
    });

    it('applies server-disabled fields and maps server validation errors', async() => {
        const load = jest.fn().mockResolvedValue({values: {name: 'old', pkce: false, method: ''}, readonly: ['pkce']});
        const update = jest.fn().mockRejectedValue(new FormValidationError({name: ['Already exists']}));
        renderForm({id: 1, load, update});
        expect(((await screen.findByLabelText('PKCE')) as HTMLInputElement).disabled).toBe(true);
        type(screen.getByLabelText('Name'), 'x');
        click(screen.getByText('Save'));
        expect(await screen.findByText('Already exists')).toBeTruthy();
    });
});
