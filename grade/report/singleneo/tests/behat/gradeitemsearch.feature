@core @core_grades @gradereport_singleneo @javascript
Feature: Matt

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email                | idnumber |
      | teacher1 | Teacher   | 1        | teacher1@example.com | t1       |
      | student1 | Student   | 1        | student1@example.com | s1       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity | course | idnumber | name                |
      | assign   | C1     | a1       | Test assignment one |
      | assign   | C1     | a2       | Test assignment two |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I change window size to "large"

  Scenario: A teacher can search for and find a grade item to view
    When I navigate to "View > Neo Single report" in the course gradebook
    And I click on "Grades" "link" in the ".neosingle" "css_element"
    And I click on "Select grade item" "link"
    And I wait until "Neo Single report" "dialogue" exists
    And I should see "Test assignment one" in the "Neo Single report" "dialogue"
    And I should see "Test assignment two" in the "Neo Single report" "dialogue"
    And I set the field "searchinput" to "two"
    And I wait "1" seconds
    Then I should see "Test assignment two" in the "Neo Single report" "dialogue"
    And I should not see "Test assignment one" in the "Neo Single report" "dialogue"
