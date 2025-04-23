@mod @mod_assign
Feature: In an assignment, teachers can perform bulk actions on submissions
  In order to manage submissions
  As a teacher
  I need to select the assignments I want to manage and click the appropriate button

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
    And the following "users" exist:
      | username  | firstname  | lastname  | email                 |
      | teacher1  | Teacher    | 1         | teacher1@example.com  |
      | student1  | Student    | 1         | student1@example.com  |
      | manager1  | Manager    | 1         | manager1@example.com  |
    And the following "role assigns" exist:
      | user      | role          | contextlevel | reference |
      | manager1  | coursecreator | system       |           |
    And the following "role capability" exists:
      | role                           | manager        |
      | mod/assign:editothersubmission | allow          |
    And the following "course enrolments" exist:
      | user      | course  | role            |
      | teacher1  | C1      | editingteacher  |
      | student1  | C1      | student         |
      | manager1  | C1      | manager         |

  @javascript
  Scenario Outline: As a teacher, if a student has made submissions, certain bulk action buttons should be visible
    Given the following "activity" exists:
      | activity                            | assign                  |
      | course                              | C1                      |
      | name                                | Test assignment name    |
      | intro                               | Submit your online text |
      | assignsubmission_onlinetext_enabled | <onlinetext_enabled>    |
      | assignsubmission_file_enabled       | <file_enabled>          |
      | assignsubmission_file_maxfiles      | -1                      |
      | assignsubmission_file_maxsizebytes  | 1000                    |
      | attemptreopenmethod                 | manual                  |
      | hidegrader                          | 1                       |
      | submissiondrafts                    | 0                       |
    And the following "mod_assign > submissions" exist:
      | assign                | user      | onlinetext                       |
      | Test assignment name  | student1  | <submission_text>                |
    And I am on the "Test assignment name" Activity page logged in as <teacher>
    And I change window size to "large"
    When I navigate to "Submissions" in current page administration
    And I click on "Select all" "checkbox"
    Then I should <download_visibility> "Download" in the ".btn-toolbar" "css_element"
    And I should <lock_visibility> "Lock" in the ".btn-toolbar" "css_element"
    And I should <unlock_visibility> "Unlock" in the ".btn-toolbar" "css_element"
    And I should <delete_visibility> "Delete" in the ".btn-toolbar" "css_element"

    Examples:
      | onlinetext_enabled | file_enabled | submission_text                  | download_visibility  | lock_visibility  | unlock_visibility  | delete_visibility  | teacher   |
      | 1                  | 1            | I'm the student first submission | see                  | see              | see                | see                | manager1  |
      | 1                  | 0            | I'm the student first submission | see                  | see              | see                | not see            | teacher1  |
      | 0                  | 0            |                                  | not see              | not see          | not see            | not see            | teacher1  |

  @javascript
  Scenario Outline: As a teacher, if there are no submissions, certain bulk action buttons should be visible
    Given the following "activity" exists:
      | activity                            | assign                  |
      | course                              | C1                      |
      | name                                | Test assignment name    |
      | intro                               | Submit your online text |
      | assignsubmission_onlinetext_enabled | <onlinetext_enabled>    |
      | assignsubmission_file_enabled       | <file_enabled>          |
      | maxattempts                         | -1                      |
      | attemptreopenmethod                 | manual                  |
      | hidegrader                          | 1                       |
      | submissiondrafts                    | 0                       |

    And I am on the "Test assignment name" Activity page logged in as teacher1
    And I change window size to "large"
    When I navigate to "Submissions" in current page administration
    And I click on "Select all" "checkbox"
    Then I should <download_visibility> "Download" in the ".btn-toolbar" "css_element"
    And I should <lock_visibility> "Lock" in the ".btn-toolbar" "css_element"
    And I should <unlock_visibility> "Unlock" in the ".btn-toolbar" "css_element"
    And I should <delete_visibility> "Delete" in the ".btn-toolbar" "css_element"

    Examples:
      | onlinetext_enabled | file_enabled | download_visibility  | lock_visibility  | unlock_visibility  | delete_visibility  |
      | 1                  | 0            | not see              | see              | see                | not see            |
      | 0                  | 0            | not see              | not see          | not see            | not see            |
