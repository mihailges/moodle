@gradereport @gradereport_grader @javascript
Feature: Display feedback on the Grader report
  In order to check the expected results are displayed
  As a teacher
  I need to see the feedback information in a modal

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "activities" exist:
      | activity | course | section | name                   | intro                   | assignfeedback_comments_enabled |
      | assign   | C1     | 1       | Test assignment name 1 | Submit your online text | 1                               |
      | assign   | C1     | 1       | Test assignment name 2 | submit your online text | 1                               |
    And the following "mod_assign > submissions" exist:
      | assign                 | user     | onlinetext                            |
      | Test assignment name 1 | student1 | This is a submission for assignment 1 |
      | Test assignment name 2 | student1 | This is a submission for assignment 2 |
    And the following "grade items" exist:
      | itemname | course | gradetype | itemtype |
      | Grade item 1 | C1 | text | manual |
    And the following "grade grades" exist:
      | gradeitem              | user     | grade | feedback                     |
      | Grade item 1           | student1 |       | Longer feedback text content |
    And I log in as "teacher1"
    And I am on the "Test assignment name 1" "assign activity" page
    And I follow "View all submissions"
    And I click on "Grade" "link" in the "Student 1" "table_row"
    And I set the following fields to these values:
      | Grade out of 100  | 50               |
      | Feedback comments | This is feedback |
    And I press "Save changes"

  Scenario: View the feedback icon on the Grader report
    Given I am on "Course 1" course homepage
    When I navigate to "View > Grader report" in the course gradebook
    Then I should see "Test assignment name 1"
    And I should see "Test assignment name 2"
    And "Feedback provided" "icon" should exist in the "Student 1" "table_row"
    And "Feedback provided" "icon" should not exist in the "Student 2" "table_row"

  Scenario: View the feedback modal from the action menu
    Given I am on "Course 1" course homepage
    And I navigate to "View > Grader report" in the course gradebook
    And I click on ".action-menu.grader" "css_element"
    When I click on ".action-menu a[data-action='feedback']" "css_element"
    Then I should see "This is feedback" in the ".modal .modal-body" "css_element"

  Scenario: View the feedback text for text only grade
    Given I am on "Course 1" course homepage
    When I navigate to "View > Grader report" in the course gradebook
    Then I should see "Grade item 1"
    And "Longer feedback ..." "text" should exist in the "Student 1" "table_row"

  Scenario: View the feedback modal for text only grade
    Given I am on "Course 1" course homepage
    And I navigate to "View > Grader report" in the course gradebook
    When I click on "Longer feedback ..." "text"
    Then I should see "Longer feedback text content" in the ".modal .modal-body" "css_element"
