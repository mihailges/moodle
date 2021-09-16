@core @core_my
Feature: Run tests over my courses checking editing permissions.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |

  Scenario: Check that learners can not edit the page but admins can
    Given I log in as "student1"
    And I am on my courses homepage
    Then I should not see "Turn editing on"
    And I log out

    And I log in as "admin"
    And I am on my courses homepage
    And I press "Turn editing on"
    And I add the "Latest announcements" block
    Then I should see "Latest announcements" in the "Latest announcements" "block"
    And I should see "Course overview" in the "Course overview" "block"
    And I log out

    And I log in as "student1"
    And I am on my courses homepage
    # Since the learner can not modify their page it'll be the same as system.
    And I should see "Latest announcements"
    And I log out

  Scenario: Admin can add new courses or manage them from my courses
    Given I log in as "admin"
    And I am on my courses homepage
    When I click on "Course management options" "button"
    And I click on "New course" "link"
    And I wait to be redirected
    Then I should see "Add a new course"
    And I am on my courses homepage
    And I click on "Course management options" "button"
    And I click on "Manage courses" "link"
    And I should see "Course and category management"
