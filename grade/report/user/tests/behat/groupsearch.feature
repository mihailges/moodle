@core @core_grades @gradereport_singleview @javascript
Feature: Group searching functionality within the user report.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "users" exist:
      | username  | firstname | lastname  | email                 | idnumber  |
      | teacher1  | Teacher   | 1         | teacher1@example.com  | t1        |
    And the following "course enrolments" exist:
      | user      | course | role           |
      | teacher1  | C1     | editingteacher |
    And the following "groups" exist:
      | name          | course | idnumber |
      | Default group | C1     | dg       |
      | Tutor group   | C1     | tg       |
      | Marker group  | C1     | mg       |
    And I am on the "C1" "Course" page logged in as "teacher1"
    And I change window size to "large"

  Scenario: A teacher can search for and find a group to find a user in
    When I navigate to "View > User report" in the course gradebook
    And I click on "Click to select group" "button"
    And I wait until "Search groups" "dialogue" exists
    And I should see "Tutor group" in the "Search groups" "dialogue"
    And I should see "Marker group" in the "Search groups" "dialogue"
    And I set the field "searchinput" to "tutor"
    And I wait "1" seconds
    Then I should see "Tutor group" in the "Search groups" "dialogue"
    And I should not see "Marker group" in the "Search groups" "dialogue"
