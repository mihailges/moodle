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
 * Tests for the form rules and conditions.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {
    required, requiredIf, maxLength, minLength, pattern, url, minItems, validIf, passwordPolicy,
} from '../src/forms/rules';

describe('rules', () => {
    it('requiredIf() only requires a value when the condition is met', () => {
        const rule = requiredIf((v: any) => Boolean(v.on), 'req');
        expect(rule('', {on: false})).toBe(true);
        expect(rule('', {on: true})).toBe('req');
    });

    it('passwordPolicy() fetches the policy once and enforces it', async() => {
        const fetchPolicy = jest.fn().mockResolvedValue(
            {minLength: 8, minDigits: 1, minLower: 1, minUpper: 1, minNonAlphanumeric: 1},
        );
        const rule = passwordPolicy({
            fetchPolicy,
            messages: {minLength: 'len', minDigits: 'digits', minLower: 'lower', minUpper: 'upper', minNonAlphanumeric: 'sym'},
        });
        expect(await rule('Ab1!', {})).toBe('len');
        expect(await rule('Abcdefgh!', {})).toBe('digits');
        expect(await rule('ABCDEFG1!', {})).toBe('lower');
        expect(await rule('Abcdefg1', {})).toBe('sym');
        expect(await rule('Abcdefg1!', {})).toBe(true);
        expect(fetchPolicy).toHaveBeenCalledTimes(1);
    });

    it('passwordPolicy() passes when the policy cannot be fetched', async() => {
        const rule = passwordPolicy({fetchPolicy: () => Promise.reject(new Error('offline'))});
        expect(await rule('a', {})).toBe(true);
    });
});

describe('rules paired with incompatible values', () => {
    it.each([true, false, undefined, null, 5, ['a'], {a: 1}])('do not throw or fail for %p', async(value) => {
        const rules = [
            maxLength(1, 'm'), minLength(3, 'm'), pattern(/^x$/, 'm'), url('m'),
            passwordPolicy(),
        ];
        for (const rule of rules) {
            expect(await rule(value, {})).toBe(true);
        }
    });

    it('validIf turns a condition into a rule with the message as the error', () => {
        const rule = validIf((v: number) => v > 1, 'Too small');
        expect(rule(2, {})).toBe(true);
        expect(rule(1, {})).toBe('Too small');
    });

    it('minItems only counts filled items', () => {
        const rule = minItems(1, 'Need one');
        expect(rule(['', 'a'], {})).toBe(true);
        expect(rule(['', ' '], {})).toBe('Need one');
        expect(rule([], {})).toBe('Need one');
    });

    it('minItems ignores non-array values', () => {
        expect(minItems(1, 'm')(true, {})).toBe(true);
        expect(minItems(1, 'm')([], {})).toBe('m');
    });
});
