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
 * Tests for the core/forms extension registry and FormSlot.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {render, screen} from '@testing-library/react';
import {useForm, FormProvider} from 'react-hook-form';
import {FormSlot, ExtensionModules, registerFormExtension, getFormExtensions} from '@moodle/lms/core/forms';

// @moodlehq/design-system is ESM only; MoodleForm only needs a Button from it here.
jest.mock('@moodlehq/design-system', () => ({Button: () => null}), {virtual: true});

// The registry is module state, so each test uses its own slot names.
const Ext = (text: string) => () => <p>{text}</p>;

const renderSlot = (name: string) => {
    const Host = () => {
        const form = useForm();
        return <FormProvider {...form}><FormSlot name={name} /></FormProvider>;
    };
    return render(<Host />);
};

const texts = (container: HTMLElement) => Array.from(container.querySelectorAll('p')).map((p) => p.textContent);

describe('core/forms registry and FormSlot', () => {
    it('renders only the extensions of the slot, sorted by order with 100 as the default', () => {
        registerFormExtension({slot: 'test.a', plugin: 'p1', component: Ext('default')});
        registerFormExtension({slot: 'test.a', plugin: 'p2', order: 10, component: Ext('first')});
        registerFormExtension({slot: 'test.a', plugin: 'p3', order: 200, component: Ext('last')});
        registerFormExtension({slot: 'test.b', plugin: 'p4', component: Ext('other slot')});

        expect(texts(renderSlot('test.a').container)).toEqual(['first', 'default', 'last']);
    });

    it('keeps registration order for equal orders and renders nothing for an empty slot', () => {
        registerFormExtension({slot: 'test.c', plugin: 'p1', component: Ext('one')});
        registerFormExtension({slot: 'test.c', plugin: 'p2', component: Ext('two')});
        expect(texts(renderSlot('test.c').container)).toEqual(['one', 'two']);
        expect(renderSlot('test.none').container.textContent).toBe('');
    });

    it('replaces a re-registered extension and passes the form control', () => {
        const Spy = jest.fn(() => null);
        registerFormExtension({slot: 'test.d', plugin: 'p1', component: Ext('old')});
        registerFormExtension({slot: 'test.d', plugin: 'p1', component: Spy});
        expect(getFormExtensions('test.d')).toHaveLength(1);
        renderSlot('test.d');
        expect((Spy.mock.calls[0] as unknown[])[0]).toHaveProperty('control');
    });

    it('contains an extension that throws', () => {
        const Broken = () => {
            throw new Error('boom');
        };
        const consoleError = jest.spyOn(window.console, 'error').mockImplementation(() => undefined);
        registerFormExtension({slot: 'test.e', plugin: 'bad', component: Broken});
        registerFormExtension({slot: 'test.e', plugin: 'good', component: Ext('still here')});
        expect(texts(renderSlot('test.e').container)).toEqual(['still here']);
        consoleError.mockRestore();
    });

    it('ExtensionModules skips invalid specifiers and shows its children once loading finished', async() => {
        const consoleError = jest.spyOn(window.console, 'error').mockImplementation(() => undefined);
        render(<ExtensionModules modules={['../../evil', 'https://example.com/x.js']}><p>form</p></ExtensionModules>);
        expect(await screen.findByText('form')).toBeTruthy();
        expect(consoleError).toHaveBeenCalledTimes(2);
        consoleError.mockRestore();
    });
});
