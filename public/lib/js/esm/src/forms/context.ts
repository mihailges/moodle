import {createContext} from 'react';

export type FormState = {
    // Fields disabled by the server (e.g. capability driven), on top of each field's disableIf.
    serverDisabled: string[];
};

export const FormStateContext = createContext<FormState>({serverDisabled: []});
