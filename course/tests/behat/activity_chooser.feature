@core @core_course @javascript
Feature: Display and choose from the available activities in course
  In order to add activities to a course
  As a teacher
  I should be enabled to choose from a list of available activities and also being able to read their summaries.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher | Teacher | 1 | teacher@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course | C | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher | C | editingteacher |
    And I log in as "teacher"
    And I am on "Course" course homepage with editing mode on
    And I wait until the page is ready

  Scenario: The available activities are displayed to the teacher in the activity chooser
    When I open the activity chooser
    Then I should see "Add an activity or resource" in the ".modal-title" "css_element"
    And I should see "Assignment" in the ".modal-body" "css_element"

  Scenario: The teacher can choose to add an activity from the activity items in the activity chooser
    Given I open the activity chooser
    And I should see "Add an activity or resource" in the ".modal-title" "css_element"
    When I add the "Assignment" module in the activity chooser
    Then I should see "Adding a new Assignment"

  Scenario: The teacher can choose to add an activity from the activity summary in the activity chooser
    Given I open the activity chooser
    And I should see "Add an activity or resource" in the ".modal-title" "css_element"
    And I click on ".optionaction" "css_element" in the "Assignment" module cell in the activity chooser
    And I wait until "Add" "link" exists
    And I click on "Add" "link" in the active carousel panel
    Then I should see "Adding a new Assignment"

  Scenario: Show summary
    Given I open the activity chooser
    And I should see "Add an activity or resource" in the ".modal-title" "css_element"
    When I click on ".optionaction" "css_element" in the "Assignment" module cell in the activity chooser
    Then I should see "Assignment"
    And I should see "The assignment activity module enables a teacher to communicate tasks, collect work and provide grades and feedback."

  Scenario: Hide summary
    Given I open the activity chooser
    And I should see "Add an activity or resource" in the ".modal-title" "css_element"
    And I click on ".optionaction" "css_element" in the "Assignment" module cell in the activity chooser
    And I should see "The assignment activity module enables a teacher to communicate tasks, collect work and provide grades and feedback."
    And I should see "Back"
    When I click on "Back" "button"
    And I should see "Forum" in the "//div[@class='carousel-item active']" "xpath_element"
    And "Back" "button" should not exist in the "//div[@class='carousel-item active']" "xpath_element"
    Then I should not see "The assignment activity module enables a teacher to communicate tasks, collect work and provide grades and feedback."

  Scenario: The teacher can search for an activity by it's name
    Given I open the activity chooser
    When I set the field "search" to "Lesson"
    Then I should see "1 results found"
    And I should see "1" activities in the activity chooser search results area
    And I should see "Lesson" activity in the activity chooser search results area

  Scenario: The teacher can search for an activity by it's description
    Given I open the activity chooser
    When I set the field "search" to "The lesson activity module enables a teacher to deliver content"
    Then I should see "1 results found"
    And I should see "1" activities in the activity chooser search results area
    And I should see "Lesson" activity in the activity chooser search results area

  Scenario: Search results are not returned if the search query does not match any activity name or description
    Given I open the activity chooser
    When I set the field "search" to "Random search query"
    Then I should see "0 results found"
    And I should see "0" activities in the activity chooser search results area

  Scenario: Teacher can return to the default activity chooser state by manually removing the search query
    Given I open the activity chooser
    And I set the field "search" to "Lesson"
    And I should see "1 results found"
    And I should see "1" activities in the activity chooser search results area
    When I set the field "search" to ""
    Then ".searchresultscontainer" "css_element" should not exist
    And ".optionscontainer" "css_element" should exist

  Scenario: Teacher can not see a "clear" button if a search query is not entered in the activity chooser search bar
    When I open the activity chooser
    Then "//button[@data-action='clearsearch']" "xpath_element" should not exist

  Scenario: Teacher can see a "clear" button after entering a search query in the activity chooser search bar
    Given I open the activity chooser
    When I set the field "search" to "Search query"
    Then "//button[@data-action='clearsearch']" "xpath_element" should exist

  Scenario: Teacher can not see a "clear" button if the search query is removed in the activity chooser search bar
    Given I open the activity chooser
    And I set the field "search" to "Search query"
    And "//button[@data-action='clearsearch']" "xpath_element" should exist
    When I set the field "search" to ""
    Then "//button[@data-action='clearsearch']" "xpath_element" should not exist

  Scenario: Teacher can instantly remove the search query from the activity search bar by clicking on the "clear" button
    Given I open the activity chooser
    And I set the field "search" to "Search query"
    And I should see "results found"
    When I click on "//button[@data-action='clearsearch']" "xpath_element"
    Then I should not see "Search query"
    And ".searchresultscontainer" "css_element" should not exist
    And ".optionscontainer" "css_element" should exist
