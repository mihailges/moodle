@mod @mod_assign
Feature: In an assignment, teachers can download submissions through the actions dropdown
  In order to download
  As a teacher
  I need to click the actions dropdown and select 'download all submissions'

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
    And the following "users" exist:
      | username  | firstname  | lastname  | email                 |
      | teacher1  | Teacher    | 1         | teacher1@example.com  |
      | student1  | Student    | 1         | student1@example.com  |
    And the following "course enrolments" exist:
      | user      | course  | role            |
      | teacher1  | C1      | editingteacher  |
      | student1  | C1      | student         |

  Scenario Outline: Submissions actions dropdown download button visibility
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
    And the following "mod_assign > submissions" exist:
      | assign                | user      | onlinetext                       |
      | Test assignment name  | student1  | <submission_text>                |
    And I am on the "Test assignment name" Activity page logged in as teacher1
    And I change window size to "large"
    When I navigate to "Submissions" in current page administration
    And I click on "Actions" "link"
    Then I should <see_or_not_see> "Download all submissions"
    And I should see "View gradebook"

    Examples:
      | onlinetext_enabled | file_enabled | submission_text                  | see_or_not_see |
      | 1                  | 0            | I'm the student first submission | see            |
      | 0                  | 0            |                                  | not see        |

  Scenario: Submissions actions dropdown download button should not be visible when no submission is made
    Given the following "activity" exists:
      | activity                            | assign                  |
      | course                              | C1                      |
      | name                                | Test assignment name    |
      | intro                               | Submit your online text |
      | assignsubmission_onlinetext_enabled | 1                       |
      | assignsubmission_file_enabled       | 0                       |
      | maxattempts                         | -1                      |
      | attemptreopenmethod                 | manual                  |
      | hidegrader                          | 1                       |
      | submissiondrafts                    | 0                       |

    And I am on the "Test assignment name" Activity page logged in as teacher1
    And I change window size to "large"
    When I navigate to "Submissions" in current page administration
    And I click on "Actions" "link"
    Then I should not see "Download all submissions"
    And I should see "View gradebook"
