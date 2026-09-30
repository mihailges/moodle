@core @core_user
Feature: Manage personal access tokens
  In order to let an external application act on my behalf
  As a user
  I need to create, review and delete my own personal access tokens

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | User      | One      | user1@example.com |
      | user2    | User      | Two      | user2@example.com |

  Scenario: A user without the capability is not offered the page
    Given the following "role capabilities" exist:
      | role | moodle/api:createtoken |
      | user | prevent                |
    And I log in as "user1"
    When I follow "Preferences" in the user menu
    Then I should not see "Personal access tokens"

  Scenario: Every authenticated user is offered the page by default
    Given I log in as "user1"
    When I follow "Preferences" in the user menu
    Then I should see "Personal access tokens"

  Scenario: A user with no tokens is told so
    Given I log in as "user1"
    When I am on the "user > Personal access tokens" page
    Then I should see "You have no personal access tokens."
    And I should see "Create token"

  Scenario: Creating a token reveals its value and lists it
    Given I am on the "user > Personal access tokens" page logged in as "user1"
    And I click on "Create token" "link"
    And I set the following fields to these values:
      | Name                         | Attendance export                                    |
      | Description                  | Used by the faculty office weekly attendance report. |
      | scope_core_grades_grade_read | 1                                                    |
    When I press "Create token"
    Then I should see "This secret is shown only once"
    And I should see "Your new token: Attendance export"
    # The reveal sits between the description and the list, so the new token is visible in the
    # table at the same time as its value.
    And I should see "Attendance export" in the "reportbuilder-table" "table"
    And I should see "View gradebook" in the "reportbuilder-table" "table"
    And I should see "Active" in the "reportbuilder-table" "table"

  Scenario: A revealed token value is never shown again
    Given I am on the "user > Personal access tokens" page logged in as "user1"
    And I click on "Create token" "link"
    And I set the following fields to these values:
      | Name                         | Attendance export |
      | scope_core_grades_grade_read | 1                 |
    And I press "Create token"
    # The value is held for one render only, so a return visit must not show it again.
    When I am on the "user > Personal access tokens" page
    Then I should see "Attendance export" in the "reportbuilder-table" "table"
    And I should not see "This secret is shown only once"
    And I should not see "Your new token"

  Scenario: A token cannot be created without a scope
    Given I am on the "user > Personal access tokens" page logged in as "user1"
    And I click on "Create token" "link"
    And I set the field "Name" to "No scopes"
    When I press "Create token"
    Then I should see "Select at least one scope for the token."

  @javascript
  Scenario: The scopes error is identified to assistive technology and takes focus
    Given I am on the "user > Personal access tokens" page logged in as "user1"
    And I click on "Create token" "link"
    And I set the field "Name" to "No scopes"
    When I press "Create token"
    Then I should see "Select at least one scope for the token."
    # The error belongs to the set of scopes rather than to any one checkbox, so it stays
    # attached to the scopes label at the top of the list, and focus moves to the group
    # wrapping that label, the error and every checkbox, announced as a named, invalid
    # group; from there the first Tab reaches the first checkbox like any other. The error
    # is part of the group's accessible name rather than its description: JAWS does not
    # announce aria-describedby on non-widget roles like group, but announces the name on
    # focus whatever the role. The description instead points at the info notification,
    # because without an explicit description Chromium fabricates one from the group's
    # leftover text and NVDA reads the notification and the error a second time. Everything
    # the ARIA references point at stays visible, as Firefox/NVDA resolve a reference to a
    # hidden source to nothing.
    And the "aria-invalid" attribute of "#id_scopesgroup" "css_element" should contain "true"
    And the "aria-labelledby" attribute of "#id_scopesgroup" "css_element" should contain "id_scopesgroup_label"
    And the "aria-labelledby" attribute of "#id_scopesgroup" "css_element" should contain "id_error_scopeslabel"
    And the "aria-describedby" attribute of "#id_scopesgroup" "css_element" should contain "id_scopesgroup_note"
    And the focused element is "#id_scopesgroup" "css_element"

  Scenario: A user only ever sees their own tokens
    Given I am on the "user > Personal access tokens" page logged in as "user1"
    And I click on "Create token" "link"
    And I set the following fields to these values:
      | Name                         | User one token |
      | scope_core_grades_grade_read | 1              |
    And I press "Create token"
    When I am on the "user > Personal access tokens" page logged in as "user2"
    Then I should see "You have no personal access tokens."
    And I should not see "User one token"

  @javascript
  Scenario: Revoking an active token warns that access is cut off immediately
    Given I am on the "user > Personal access tokens" page logged in as "user1"
    And I click on "Create token" "link"
    And I set the following fields to these values:
      | Name                         | Gradebook sync |
      | scope_core_grades_grade_read | 1              |
    And I press "Create token"
    # An active token is revoked rather than deleted: the wording follows what it means to the
    # owner. The control sits in the row itself, so it is clicked without opening a menu first.
    When I click on "Revoke" "button" in the "Gradebook sync" "table_row"
    Then I should see "Deleting it revokes access immediately" in the "Revoke \"Gradebook sync\"?" "dialogue"

  @javascript
  Scenario: Confirming the warning revokes the token
    Given I am on the "user > Personal access tokens" page logged in as "user1"
    And I click on "Create token" "link"
    And I set the following fields to these values:
      | Name                         | Gradebook sync |
      | scope_core_grades_grade_read | 1              |
    And I press "Create token"
    When I click on "Revoke" "button" in the "Gradebook sync" "table_row"
    And I click on "Revoke" "button" in the "Revoke \"Gradebook sync\"?" "dialogue"
    Then I should see "Token revoked"
    And I should see "You have no personal access tokens."

  @javascript
  Scenario: Tokens can be filtered by name
    Given I am on the "user > Personal access tokens" page logged in as "user1"
    And I click on "Create token" "link"
    And I set the following fields to these values:
      | Name                         | Gradebook sync |
      | scope_core_grades_grade_read | 1              |
    And I press "Create token"
    And I click on "Create token" "link"
    And I set the following fields to these values:
      | Name                         | Mobile app testing |
      | scope_core_grades_grade_read | 1                  |
    And I press "Create token"
    When I click on "Filters" "button"
    And I set the following fields in the "Name" "core_reportbuilder > Filter" to these values:
      | Name operator | Contains       |
      | Name value    | Gradebook      |
    And I click on "Apply" "button" in the "[data-region='report-filters']" "css_element"
    Then I should see "Gradebook sync" in the "reportbuilder-table" "table"
    And I should not see "Mobile app testing" in the "reportbuilder-table" "table"

  Scenario: Each scope offered is labelled by its identifier as well as its summary
    Given I am on the "user > Personal access tokens" page logged in as "user1"
    When I click on "Create token" "link"
    Then I should see "View gradebook"
    And I should see "core_grades:grade:read"

  Scenario: A listed scope carries the identifier an error names it by
    Given I am on the "user > Personal access tokens" page logged in as "user1"
    And I click on "Create token" "link"
    And I set the following fields to these values:
      | Name                         | Gradebook sync |
      | scope_core_grades_grade_read | 1              |
    When I press "Create token"
    # The column has room for the name only, so the identifier rides on the badge itself.
    Then I should see "View gradebook" in the "reportbuilder-table" "table"
    And the "title" attribute of ".reportbuilder-table [data-bs-toggle='tooltip']" "css_element" should contain "core_grades:grade:read"

  Scenario: The expiry field offers fixed periods only
    Given I am on the "user > Personal access tokens" page logged in as "user1"
    When I click on "Create token" "link"
    Then the "Expiry date" select box should contain "7 days"
    And the "Expiry date" select box should contain "1 month"
    And the "Expiry date" select box should contain "2 months"
    And the "Expiry date" select box should contain "3 months"
    And the "Expiry date" select box should contain "1 year"
    And the "Expiry date" select box should not contain "Custom"
    And I should not see "Custom expiry date"
    # A period, not a date, so no calendar is offered.
    And I should not see "Must be within one year of today"

  Scenario: Without JavaScript the control posts and revokes at once
    Given I am on the "user > Personal access tokens" page logged in as "user1"
    And I click on "Create token" "link"
    And I set the following fields to these values:
      | Name                         | Gradebook sync |
      | scope_core_grades_grade_read | 1              |
    And I press "Create token"
    And I am on the "user > Personal access tokens" page
    # The control is a submit button in a posting form. With JavaScript the modal intercepts it;
    # without, the form posts on the first click and there is no confirmation step.
    When I click on "Revoke" "button" in the "Gradebook sync" "table_row"
    Then I should see "Token revoked"
    And I should see "You have no personal access tokens."
