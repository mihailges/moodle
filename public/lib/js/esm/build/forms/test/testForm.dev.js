var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
import { useState } from "react";
import { Alert } from "@moodlehq/design-system";
import { MoodleForm, FormSlot, ExtensionModules, sendFormEndpointRequest } from "@moodle/lms/core/forms";
import {
  TextField,
  SwitchField,
  RadioField,
  CheckboxField,
  GroupField,
  RepeaterField,
  SectionField
} from "@moodle/lms/core/forms/fields";
import { required, requiredIf, maxLength, minItems, pattern, url, validIf } from "@moodle/lms/core/forms/rules";
import { RatingField } from "./fields/RatingField";
import { ColorField } from "./fields/ColorField";
import { CountedTextareaField } from "./fields/CountedTextareaField";
const TITLE_MAX_LENGTH = 50;
const DESCRIPTION_MAX_LENGTH = 200;
const SLUG = /^[a-z0-9-]+$/;
const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const TAGS = ["alpha", "beta", "gamma"];
function TestForm({ extensionModules, extensionValues }) {
  const [saved, setSaved] = useState(null);
  return /* @__PURE__ */ jsxDEV(ExtensionModules, { modules: extensionModules, children: /* @__PURE__ */ jsxDEV(
    MoodleForm,
    {
      defaultValues: {
        title: "",
        description: "",
        priority: "normal",
        notify: true,
        notifyEmail: "",
        tags: [],
        emails: [""],
        rating: "3",
        color: "#3366ff",
        internalNote: "",
        externalId: "",
        slug: "",
        website: "",
        extensions: extensionValues ?? {}
      },
      submit: {
        action: /* @__PURE__ */ __name((payload) => sendFormEndpointRequest("/core/test/form", "POST").save(payload), "action"),
        label: "Save test form",
        onSuccess: setSaved
      },
      children: [
        saved !== null && /* @__PURE__ */ jsxDEV(Fragment, { children: [
          /* @__PURE__ */ jsxDEV(Alert, { type: "success", message: "Saved" }, void 0, false, {
            fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
            lineNumber: 83,
            columnNumber: 25
          }, this),
          /* @__PURE__ */ jsxDEV("pre", { children: JSON.stringify(saved, null, 2) }, void 0, false, {
            fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
            lineNumber: 84,
            columnNumber: 25
          }, this)
        ] }, void 0, true, {
          fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
          lineNumber: 82,
          columnNumber: 21
        }, this),
        /* @__PURE__ */ jsxDEV(
          TextField,
          {
            name: "title",
            label: "Title",
            rules: [required("The title is required"), maxLength(TITLE_MAX_LENGTH, "The title is too long")]
          },
          void 0,
          false,
          {
            fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
            lineNumber: 87,
            columnNumber: 17
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(
          CountedTextareaField,
          {
            name: "description",
            label: "Description",
            maxLength: DESCRIPTION_MAX_LENGTH,
            rules: [maxLength(DESCRIPTION_MAX_LENGTH, "The description is too long")]
          },
          void 0,
          false,
          {
            fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
            lineNumber: 92,
            columnNumber: 17
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(FormSlot, { name: "core_test_form.general" }, void 0, false, {
          fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
          lineNumber: 98,
          columnNumber: 17
        }, this),
        /* @__PURE__ */ jsxDEV(GroupField, { name: "priority", label: "Priority", layout: "column", children: [
          /* @__PURE__ */ jsxDEV(RadioField, { name: "priority", value: "low", label: "Low" }, void 0, false, {
            fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
            lineNumber: 100,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV(RadioField, { name: "priority", value: "normal", label: "Normal" }, void 0, false, {
            fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
            lineNumber: 101,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV(RadioField, { name: "priority", value: "high", label: "High" }, void 0, false, {
            fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
            lineNumber: 102,
            columnNumber: 21
          }, this)
        ] }, void 0, true, {
          fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
          lineNumber: 99,
          columnNumber: 17
        }, this),
        /* @__PURE__ */ jsxDEV(SwitchField, { name: "notify", label: "Notify me when it is done" }, void 0, false, {
          fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
          lineNumber: 104,
          columnNumber: 17
        }, this),
        /* @__PURE__ */ jsxDEV(
          TextField,
          {
            name: "notifyEmail",
            label: "Notification email",
            hideIf: (v) => !v.notify,
            rules: [
              requiredIf((v) => v.notify, "Enter an email to notify"),
              pattern(EMAIL, "This is not a valid email address")
            ]
          },
          void 0,
          false,
          {
            fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
            lineNumber: 105,
            columnNumber: 17
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(
          GroupField,
          {
            name: "tags",
            label: "Tags",
            layout: "column",
            rules: [minItems(1, "Select at least one tag")],
            children: TAGS.map((tag) => /* @__PURE__ */ jsxDEV(CheckboxField, { name: "tags", value: tag, label: tag }, tag, false, {
              fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
              lineNumber: 120,
              columnNumber: 40
            }, this))
          },
          void 0,
          false,
          {
            fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
            lineNumber: 114,
            columnNumber: 17
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(
          RepeaterField,
          {
            name: "emails",
            label: "Emails",
            addLabel: "Add an email",
            removeLabel: "Remove",
            emptyItem: "",
            children: /* @__PURE__ */ jsxDEV(TextField, { label: "Email", hideLabel: true, rules: [pattern(EMAIL, "This is not a valid email address")] }, void 0, false, {
              fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
              lineNumber: 129,
              columnNumber: 21
            }, this)
          },
          void 0,
          false,
          {
            fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
            lineNumber: 122,
            columnNumber: 17
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(
          TextField,
          {
            name: "externalId",
            label: "External id (advanced)",
            advanced: true,
            rules: [required("The external id is required"), pattern(SLUG, "Use lowercase letters, numbers and dashes")]
          },
          void 0,
          false,
          {
            fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
            lineNumber: 131,
            columnNumber: 17
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(SectionField, { title: "Appearance", defaultOpen: false, children: [
          /* @__PURE__ */ jsxDEV(
            TextField,
            {
              name: "slug",
              label: "Slug",
              rules: [required("The slug is required"), pattern(SLUG, "Use lowercase letters, numbers and dashes")]
            },
            void 0,
            false,
            {
              fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
              lineNumber: 138,
              columnNumber: 21
            },
            this
          ),
          /* @__PURE__ */ jsxDEV(TextField, { name: "website", label: "Website", rules: [url("Enter a valid URL")] }, void 0, false, {
            fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
            lineNumber: 143,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV(
            RatingField,
            {
              name: "rating",
              label: "Rating",
              rules: [validIf((rating) => Number(rating) >= 1, "Choose a rating")]
            },
            void 0,
            false,
            {
              fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
              lineNumber: 144,
              columnNumber: 21
            },
            this
          ),
          /* @__PURE__ */ jsxDEV(ColorField, { name: "color", label: "Colour" }, void 0, false, {
            fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
            lineNumber: 149,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV(
            TextField,
            {
              name: "internalNote",
              label: "Internal note (advanced)",
              advanced: true,
              rules: [maxLength(20, "The note is too long")]
            },
            void 0,
            false,
            {
              fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
              lineNumber: 150,
              columnNumber: 21
            },
            this
          )
        ] }, void 0, true, {
          fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
          lineNumber: 137,
          columnNumber: 17
        }, this),
        /* @__PURE__ */ jsxDEV(FormSlot, { name: "core_test_form.footer" }, void 0, false, {
          fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
          lineNumber: 157,
          columnNumber: 17
        }, this)
      ]
    },
    void 0,
    true,
    {
      fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
      lineNumber: 58,
      columnNumber: 13
    },
    this
  ) }, void 0, false, {
    fileName: "public/lib/js/esm/src/forms/test/testForm.tsx",
    lineNumber: 57,
    columnNumber: 9
  }, this);
}
__name(TestForm, "TestForm");
var testForm_default = TestForm;
export {
  testForm_default as default
};
//# sourceMappingURL=testForm.dev.js.map
