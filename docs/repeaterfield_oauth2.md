# Implementing RepeaterField in Moodle OAuth2 Client Forms

This document provides a guide for implementing and using the `RepeaterField` component in Moodle's OAuth2 client forms.

## Overview

The `RepeaterField` is a form field component that allows users to add multiple instances of a form field, typically used for collecting lists of values such as redirect URIs for OAuth2 clients.

## Key Files

### Component Implementation
- **Source**: `/public/lib/js/esm/src/forms/fields/RepeaterField.tsx`
- **Build Output**: `/public/lib/js/esm/build/forms/fields/RepeaterField.js`

### Usage in OAuth2 Forms
- **Create Client Form**: `/public/admin/js/esm/src/oauth2/createClientForm.tsx`
- **Edit Client Form**: `/public/admin/js/esm/src/oauth2/editClientForm.tsx`

## Component Properties

```typescript
type RepeaterFieldProps = FieldBehaviour & {
    name: string;
    label: string;
    addLabel: string;
    emptyItem?: unknown;
    minRows?: number;
    removeLabel?: string;
    children: (index: number) => ReactNode;
};
```

## Usage Examples

### In Create Client Form
```tsx
<RepeaterField 
    name="redirecturis" 
    label="Redirect URIs" 
    addLabel="Add URI"
    emptyItem=""
>
    {(index) => (
        <TextField 
            name={`redirecturis.${index}.value`} 
            label="URI" 
            rules={{ required: true, validate: url() }}
        />
    )}
</RepeaterField>
```

### In Edit Client Form
```tsx
<RepeaterField 
    name="redirecturis" 
    label="Redirect URIs" 
    addLabel="Add URI"
    emptyItem=""
    minRows={0}
>
    {(index) => (
        <TextField 
            name={`redirecturis.${index}.value`} 
            label="URI" 
            rules={{ required: true, validate: url() }}
        />
    )}
</RepeaterField>
```

## Key Features

1. **Dynamic Row Addition**: Users can add multiple rows of the same field type
2. **Validation Support**: Can be integrated with validation rules
3. **Conditional Logic**: Supports hideIf and disableIf conditions
4. **Flexible Child Rendering**: Accepts a render function for custom field types
5. **Min Rows Configuration**: Can enforce minimum number of rows

## Integration Notes

The `RepeaterField` is imported and used in:
- OAuth2 client forms (both create and edit)
- Core form components
- Test files to verify functionality

## Build Process

The component follows the standard Moodle build process:
1. Source code in TypeScript (`*.tsx`)
2. Transpiled to JavaScript for browser use
3. Included in the main forms export: `/public/lib/js/esm/src/forms/fields.ts`

## Testing

Tests are located in:
- `/public/lib/js/esm/tests/MoodleForm.test.tsx`

The tests verify:
- Basic functionality of RepeaterField
- Integration with other form components
- Validation behavior
- Conditional rendering