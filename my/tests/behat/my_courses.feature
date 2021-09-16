@core @core_my
Feature: Run tests over my courses.

  Scenario: Admin can add new courses or manage them from my courses
    Given I log in as "admin"
    And I am on my courses homepage
    And I click on "Course management options" "button"
    And I click on "New course" "link"
    And I wait to be redirected
    Then I should see "Add a new course"
    And I am on my courses homepage
    And I click on "Course management options" "button"
    And I click on "Manage courses" "link"
    And I should see "Course and category management"
