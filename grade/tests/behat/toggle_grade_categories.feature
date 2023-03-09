@core @core_grades @javascript
Feature: Teachers can toggle the visibility of the grade categories in the Gradebook setup page.
  In order to focus only on the information that I am interested in
  As a teacher
  I need to be able to easily toggle the visibility of grade categories in the Gradebook setup page

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course   | C1        | 0        |
    And the following "users" exist:
      | username  | firstname | lastname  | email                 | idnumber  |
      | teacher1  | Teacher 1 | 1         | teacher1@example.com  | t1        |
      | teacher2  | Teacher 2 | 2         | teacher2@example.com  | t2        |
    And the following "course enrolments" exist:
      | user      | course | role           |
      | teacher1  | C1     | editingteacher |
      | teacher2  | C1     | editingteacher |
    And the following "grade categories" exist:
      | fullname   | course |
      | Category 1 | C1     |
    And the following "activities" exist:
      | activity | course | idnumber | name                | intro             |
      | assign   | C1     | a1       | Test assignment one | Submit something! |
    And the following "activities" exist:
      | activity | course | idnumber | name                | intro             | gradecategory |
      | assign   | C1     | a2       | Test assignment two | Submit something! | Category 1    |
    And the following "grade items" exist:
      | itemname     | grademax | course | gradecategory |
      | Manual grade | 40       | C1     | Category 1    |
    And I log in as "teacher1"
    And I am on "Course" course homepage

  Scenario: A teacher can collapse and expand grade categories in the Gradebook setup page
    Given I navigate to "Setup > Gradebook setup" in the course gradebook
    And I wait until the page is ready
    And I should see "Course" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Course" "table_row"
    And I should see "Test assignment one" in the "setup-grades" "table"
    And I should see "Category 1" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Category 1" "table_row"
    And I should see "Test assignment two" in the "setup-grades" "table"
    And I should see "Manual grade" in the "setup-grades" "table"
    And I should see "Category 1 total" in the "setup-grades" "table"
    And I should see "Course total" in the "setup-grades" "table"
    # Collapse the grade category 'Category 1'.
    When I click on "Collapse" "link" in the "Category 1" "table_row"
    Then I should see "Course" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Course" "table_row"
    And I should see "Test assignment one" in the "setup-grades" "table"
    And I should see "Category 1" in the "setup-grades" "table"
    And "Expand" "link" should exist in the "Category 1" "table_row"
    And I should not see "Test assignment two" in the "setup-grades" "table"
    And I should not see "Manual grade" in the "setup-grades" "table"
    And I should not see "Category 1 total" in the "setup-grades" "table"
    And I should see "Course total" in the "setup-grades" "table"
    # Expand the grade category 'Category 1'.
    And I click on "Expand" "link" in the "Category 1" "table_row"
    And I should see "Course" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Course" "table_row"
    And I should see "Test assignment one" in the "setup-grades" "table"
    And I should see "Category 1" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Category 1" "table_row"
    And I should see "Test assignment two" in the "setup-grades" "table"
    And I should see "Manual grade" in the "setup-grades" "table"
    And I should see "Category 1 total" in the "setup-grades" "table"
    And I should see "Course total" in the "setup-grades" "table"
    # Collapse again the grade category 'Category 1'.
    And I click on "Collapse" "link" in the "Category 1" "table_row"
    # Collapse the grade category 'Course'.
    And I click on "Collapse" "link" in the "Course" "table_row"
    And I should see "Course" in the "setup-grades" "table"
    And "Expand" "link" should exist in the "Course" "table_row"
    And I should not see "Test assignment one" in the "setup-grades" "table"
    And I should not see "Category 1" in the "setup-grades" "table"
    And I should not see "Test assignment two" in the "setup-grades" "table"
    And I should not see "Manual grade" in the "setup-grades" "table"
    And I should not see "Category 1 total" in the "setup-grades" "table"
    And I should not see "Course total" in the "setup-grades" "table"
    # Expand the grade category 'Course'. 'Category 1' should be still collapsed.
    And I click on "Expand" "link" in the "Course" "table_row"
    And I should see "Course" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Course" "table_row"
    And I should see "Test assignment one" in the "setup-grades" "table"
    And I should see "Category 1" in the "setup-grades" "table"
    And "Expand" "link" should exist in the "Category 1" "table_row"
    And I should not see "Test assignment two" in the "setup-grades" "table"
    And I should not see "Manual grade" in the "setup-grades" "table"
    And I should not see "Category 1 total" in the "setup-grades" "table"
    And I should see "Course total" in the "setup-grades" "table"

  Scenario: A teacher can see the aggregated max grade for a grade category even when the category is collapsed
    Given I navigate to "Setup > Gradebook setup" in the course gradebook
    And I wait until the page is ready
    And "Course" row "Max grade" column of "setup-grades" table should contain ""
    And "Course total" row "Max grade" column of "setup-grades" table should contain "240.00"
    And "Category 1" row "Max grade" column of "setup-grades" table should contain ""
    And "Category 1 total" row "Max grade" column of "setup-grades" table should contain "140.00"
    # Collapse the grade category 'Category 1'. The aggregated max grade should now be displayed within the 'Category 1' row.
    When I click on "Collapse" "link" in the "Category 1" "table_row"
    Then "Category 1" row "Max grade" column of "setup-grades" table should contain "140.00"
    And I should not see "Category 1 total" in the "setup-grades" "table"
    And "Course" row "Max grade" column of "setup-grades" table should contain ""
    And "Course total" row "Max grade" column of "setup-grades" table should contain "240.00"
    # Collapse the grade category 'Course'. The aggregated max grade should now be displayed within the 'Course' row.
    And I click on "Collapse" "link" in the "Course" "table_row"
    And "Course" row "Max grade" column of "setup-grades" table should contain "240.00"
    And I should not see "Course total" in the "setup-grades" "table"
    # Expand the grade category 'Course'. The aggregated max grade should not be displayed within the 'Course' row anymore.
    And I click on "Expand" "link" in the "Course" "table_row"
    And "Course" row "Max grade" column of "setup-grades" table should contain ""
    And I should see "Course total" in the "setup-grades" "table"

  Scenario: A teacher can collapse and expand grade categories in the Gradebook setup when moving grade items
    Given I navigate to "Setup > Gradebook setup" in the course gradebook
    And I wait until the page is ready
    And I click on "Move" "link" in the "Test assignment one" "table_row"
    And I should see "Course" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Course" "table_row"
    And I should see "Test assignment one" in the "setup-grades" "table"
    And I should see "Category 1" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Category 1" "table_row"
    And I should see "Test assignment two" in the "setup-grades" "table"
    And I should see "Manual grade" in the "setup-grades" "table"
    # Collapse the grade category 'Category 1'.
    When I click on "Collapse" "link" in the "Category 1" "table_row"
    Then I should see "Course" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Course" "table_row"
    And I should see "Test assignment one" in the "setup-grades" "table"
    And I should see "Category 1" in the "setup-grades" "table"
    And "Expand" "link" should exist in the "Category 1" "table_row"
    And I should not see "Test assignment two" in the "setup-grades" "table"
    And I should not see "Manual grade" in the "setup-grades" "table"
    # Expand the grade category 'Category 1'.
    And I click on "Expand" "link" in the "Category 1" "table_row"
    And I should see "Course" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Course" "table_row"
    And I should see "Test assignment one" in the "setup-grades" "table"
    And I should see "Category 1" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Category 1" "table_row"
    And I should see "Test assignment two" in the "setup-grades" "table"
    And I should see "Manual grade" in the "setup-grades" "table"
    # Collapse again the grade category 'Category 1'.
    And I click on "Collapse" "link" in the "Category 1" "table_row"
    # Collapse the grade category 'Course'.
    And I click on "Collapse" "link" in the "Course" "table_row"
    And I should see "Course" in the "setup-grades" "table"
    And "Expand" "link" should exist in the "Course" "table_row"
    And I should not see "Test assignment one" in the "setup-grades" "table"
    And I should not see "Category 1" in the "setup-grades" "table"
    And I should not see "Test assignment two" in the "setup-grades" "table"
    And I should not see "Manual grade" in the "setup-grades" "table"
    # Expand the grade category 'Course'. 'Category 1' should be still collapsed.
    And I click on "Expand" "link" in the "Course" "table_row"
    And I should see "Course" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Course" "table_row"
    And I should see "Test assignment one" in the "setup-grades" "table"
    And I should see "Category 1" in the "setup-grades" "table"
    And "Expand" "link" should exist in the "Category 1" "table_row"
    And I should not see "Test assignment two" in the "setup-grades" "table"
    And I should not see "Manual grade" in the "setup-grades" "table"

  Scenario: Previously collapsed categories are still shown as collapsed when a teacher navigates back to Gradebook setup
    Given I navigate to "Setup > Gradebook setup" in the course gradebook
    And I wait until the page is ready
    # Collapse the grade category 'Category 1' and navigate to the course homepage.
    And I click on "Collapse" "link" in the "Category 1" "table_row"
    And I am on "Course" course homepage
    # Navigate back to Gradebook setup and confirm that the category 'Category 1' is still collapsed.
    When I navigate to "Setup > Gradebook setup" in the course gradebook
    And I wait until the page is ready
    Then I should see "Course" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Course" "table_row"
    And I should see "Test assignment one" in the "setup-grades" "table"
    And I should see "Category 1" in the "setup-grades" "table"
    And "Expand" "link" should exist in the "Category 1" "table_row"
    And I should not see "Test assignment two" in the "setup-grades" "table"
    And I should not see "Manual grade" in the "setup-grades" "table"
    And I should not see "Category 1 total" in the "setup-grades" "table"
    And I should see "Course total" in the "setup-grades" "table"

  Scenario: Previously collapsed categories are still shown as collapsed when a teacher is moving grade items in Gradebook setup
    Given I navigate to "Setup > Gradebook setup" in the course gradebook
    And I wait until the page is ready
    # Collapse the grade category 'Category 1'.
    And I click on "Collapse" "link" in the "Category 1" "table_row"
    # Attempt to move a grade item and confirm that the category 'Category 1' is still collapsed.
    When I click on "Move" "link" in the "Test assignment one" "table_row"
    And I wait until the page is ready
    Then I should see "Course" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Course" "table_row"
    And I should see "Test assignment one" in the "setup-grades" "table"
    And I should see "Category 1" in the "setup-grades" "table"
    And "Expand" "link" should exist in the "Category 1" "table_row"
    And I should not see "Test assignment two" in the "setup-grades" "table"
    And I should not see "Manual grade" in the "setup-grades" "table"

  Scenario: Grade categories are shown as collapsed only to the teacher that collapsed them
    Given I navigate to "Setup > Gradebook setup" in the course gradebook
    And I wait until the page is ready
    # Collapse the grade category 'Category 1'.
    And I click on "Collapse" "link" in the "Category 1" "table_row"
    # Log in as teacher2 and confirm that the category 'Category 1' is not collapsed.
    And I log in as "teacher2"
    And I am on "Course" course homepage
    When I navigate to "Setup > Gradebook setup" in the course gradebook
    And I wait until the page is ready
    Then I should see "Course" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Course" "table_row"
    And I should see "Test assignment one" in the "setup-grades" "table"
    And I should see "Category 1" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Category 1" "table_row"
    And I should see "Test assignment two" in the "setup-grades" "table"
    And I should see "Manual grade" in the "setup-grades" "table"
    And I should see "Category 1 total" in the "setup-grades" "table"
    And I should see "Course total" in the "setup-grades" "table"
    # Log in as teacher1 and confirm that the category 'Category 1' is still collapsed.
    And I log in as "teacher1"
    And I am on "Course" course homepage
    And I navigate to "Setup > Gradebook setup" in the course gradebook
    And I wait until the page is ready
    And I should see "Course" in the "setup-grades" "table"
    And "Collapse" "link" should exist in the "Course" "table_row"
    And I should see "Test assignment one" in the "setup-grades" "table"
    And I should see "Category 1" in the "setup-grades" "table"
    And "Expand" "link" should exist in the "Category 1" "table_row"
    And I should not see "Test assignment two" in the "setup-grades" "table"
    And I should not see "Manual grade" in the "setup-grades" "table"
    And I should not see "Category 1 total" in the "setup-grades" "table"
    And I should see "Course total" in the "setup-grades" "table"
