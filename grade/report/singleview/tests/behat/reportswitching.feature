@core @core_grades @gradereport_singleview @javascript
Feature: Given we land on the index page, select what type of report we wish to view.
  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "users" exist:
      | username  | firstname | lastname  | email                 | idnumber  |
      | teacher1  | Teacher   | 1         | teacher1@example.com  | t1        |
      | student1  | Student   | 1         | student1@example.com  | s1        |
    And the following "course enrolments" exist:
      | user      | course | role           |
      | teacher1  | C1     | editingteacher |
      | student1  | C1     | student        |
    And the following "activities" exist:
      | activity | course | idnumber | name                |
      | assign   | C1     | a1       | Test assignment one |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I change window size to "large"

  Scenario: I switch between the two report types within singleview
    When I navigate to "View > Single view" in the course gradebook
    And I click on "Grades" "link" in the ".singleindex" "css_element"
    And I click on "Select grade item" "link"
    And I wait until "Select a grade item" "dialogue" exists
    And I click on "Close" "button" in the "Select a grade item" "dialogue"
    Then I click on "Users" "link" in the ".page-toggler" "css_element"
    And I click on "Click to select user" "link"
    And I wait until "Select a user" "dialogue" exists
