import type {Rule} from './types';

export type PasswordPolicy = {
    minLength: number;
    minDigits: number;
    minLower: number;
    minUpper: number;
    minNonAlphanumeric: number;
};

export type PasswordPolicyMessages = Record<keyof PasswordPolicy, string>;

// Mock of the site password policy config. Replace with the real web service call.
export const fetchPasswordPolicy = async(): Promise<PasswordPolicy> => ({
    minLength: 8, minDigits: 1, minLower: 1, minUpper: 1, minNonAlphanumeric: 1,
});

// TODO: replace with lang strings.
const defaultMessages = (p: PasswordPolicy): PasswordPolicyMessages => ({
    minLength: `Password must be at least ${p.minLength} characters long`,
    minDigits: `Password must have at least ${p.minDigits} digit(s)`,
    minLower: `Password must have at least ${p.minLower} lower case letter(s)`,
    minUpper: `Password must have at least ${p.minUpper} upper case letter(s)`,
    minNonAlphanumeric: `Password must have at least ${p.minNonAlphanumeric} special character(s)`,
});

type Options = {
    fetchPolicy?: () => Promise<PasswordPolicy>;
    messages?: PasswordPolicyMessages;
};

/**
 * Enforces the site password policy, fetched once on first use and cached.
 * If the policy cannot be fetched the rule passes, and the server stays the final authority.
 */
export const passwordPolicy = ({fetchPolicy = fetchPasswordPolicy, messages}: Options = {}): Rule => {
    let policyPromise: Promise<PasswordPolicy | null> | undefined;
    const getPolicy = () => {
        policyPromise ??= fetchPolicy().catch(() => null);
        return policyPromise;
    };

    return async(v) => {
        if (typeof v !== 'string') {
            return true;
        }
        const policy = await getPolicy();
        if (!policy) {
            return true;
        }
        const text = messages ?? defaultMessages(policy);
        const count = (re: RegExp) => (v.match(re) ?? []).length;
        if (v.length < policy.minLength) {
            return text.minLength;
        }
        if (count(/\d/g) < policy.minDigits) {
            return text.minDigits;
        }
        if (count(/[a-z]/g) < policy.minLower) {
            return text.minLower;
        }
        if (count(/[A-Z]/g) < policy.minUpper) {
            return text.minUpper;
        }
        if (count(/[^a-zA-Z0-9]/g) < policy.minNonAlphanumeric) {
            return text.minNonAlphanumeric;
        }
        return true;
    };
};
