@javascript @theme_boost
Feature: Breadcrumbs navigation
  To navigate in boost theme
  As an admin user
  I should see breadcrumbs

  Scenario: Admin user navigates to site administrations plugins assignment settings
    Given I log in as "admin"
    When I navigate to "Plugins > Activity modules > Assignment > Assignment settings" in site administration
    Then I should see "Activity modules" in the ".breadcrumb" "css_element"
    And I should see "Assignment" in the ".breadcrumb" "css_element"
    And I should see "Assignment settings" in the ".breadcrumb" "css_element"

  Scenario: Admin user navigates to site adminsitrations plugins assignment feedback offline grading worksheet
    Given I log in as "admin"
    When I navigate to "Plugins > Activity modules > Assignment > Feedback plugins > Offline grading worksheet" in site administration
    Then I should see "Activity modules" in the ".breadcrumb" "css_element"
    And I should see "Assignment" in the ".breadcrumb" "css_element"
    And I should see "Feedback plugins" in the ".breadcrumb" "css_element"
    And I should see "Offline grading worksheet" in the ".breadcrumb" "css_element"

  Scenario: Admin user navigates to site adminsitrations plugins badges manage backpacks page
    Given I log in as "admin"
    When I navigate to "Badges > Manage backpacks" in site administration
    Then I should see "Badges" in the ".breadcrumb" "css_element"
    And I should see "Manage backpacks" in the ".breadcrumb" "css_element"

  Scenario: Admin user navigates to site adminsitrations plugins caching memcached page
    Given I log in as "admin"
    When I navigate to "Plugins > Caching > Cache stores > Memcached" in site administration
    Then I should see "Caching" in the ".breadcrumb" "css_element"
    Then I should see "Cache stores" in the ".breadcrumb" "css_element"
    And I should see "Memcached" in the ".breadcrumb" "css_element"

  Scenario: Admin user navigates to 'course category management' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    When I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Manage courses and categories" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"
    And I should see "Manage course categories and courses" in the "region-main" "region"

  Scenario: Admin user navigates to category 'view' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    When I navigate to "Category" in current page administration
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"

  Scenario: Admin user navigates to 'add new course' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    When I click on "Create new course" "link"
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Manage courses and categories" in the ".breadcrumb" "css_element"
    And I should see "Add a new course" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"
    And I should see "Add a new course" in the "region-main" "region"

  Scenario: Admin user navigates to 'add subcategory' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    When I click on "Create new category" "link"
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Manage courses and categories" in the ".breadcrumb" "css_element"
    And I should see "Add a subcategory" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"
    And I should see "Add a subcategory" in the "region-main" "region"

  Scenario: Admin user navigates to a subcategory 'management' page
    Given the following "categories" exist:
      | name     | category | idnumber |
      | Cat 1    | 0        | CAT1     |
      | Subcat 1 | CAT1     | SUBCAT1  |
    Given I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    And I follow "Subcat 1"
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Subcat 1" in the ".breadcrumb" "css_element"
    And I should see "Manage courses and categories" in the ".breadcrumb" "css_element"
    And I should see "Subcat 1" in the ".page-context-header" "css_element"
    And I should see "Manage course categories and courses" in the "region-main" "region"

  Scenario: Admin user navigates to category 'settings' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    When I navigate to "Settings" in current page administration
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Settings" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"
    And I should see "Edit category settings" in the "region-main" "region"

  Scenario: Admin user navigates to category 'permissions' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    When I navigate to "Permissions" in current page administration
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Permissions" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"
    And I should see "Permissions in Category: Cat 1" in the "region-main" "region"

  Scenario: Admin user navigates to category 'assign roles' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    And I navigate to "Permissions" in current page administration
    When I select "Assign roles" from the "jump" singleselect
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Assign roles" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"
    And I should see "Assign roles in Category: Cat 1" in the "region-main" "region"

  Scenario: Admin user navigates to category 'check permissions' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    And I navigate to "Permissions" in current page administration
    When I select "Check permissions" from the "jump" singleselect
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Check permissions" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"
    And I should see "Check permissions in Category: Cat 1" in the "region-main" "region"

  Scenario: Admin user navigates to category 'cohorts' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    When I navigate to "Cohorts" in current page administration
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Cohorts" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"
    And I should see "Category: Cat 1: available cohorts" in the "region-main" "region"

  Scenario: Admin user navigates to category 'add new cohort' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    And I navigate to "Cohorts" in current page administration
    When I follow "Add new cohort"
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Cohorts" in the ".breadcrumb" "css_element"
    And I should see "Add new cohort" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"
    And I should see "Add new cohort" in the "region-main" "region"

  Scenario: Admin user navigates to category 'upload cohorts' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    And I navigate to "Cohorts" in current page administration
    When I follow "Upload cohorts"
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Cohorts" in the ".breadcrumb" "css_element"
    And I should see "Upload cohorts" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"
    And I should see "Upload cohorts" in the "region-main" "region"

  Scenario: Admin user navigates to category 'filters' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    When I navigate to "Filters" in current page administration
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Filters" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"
    And I should see "Filter settings in Category: Cat 1" in the "region-main" "region"

  Scenario: Admin user navigates to category 'restore course' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    When I navigate to "Restore course" in current page administration
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Restore course" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"
    And I should see "Import a backup file" in the "region-main" "region"

  Scenario: Admin user navigates to category 'manage backup files' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    And I navigate to "Restore course" in current page administration
    When I press "Manage backup files"
    Then I should see "Courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Restore course" in the ".breadcrumb" "css_element"
    And I should see "Manage backup files" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".page-context-header" "css_element"
    And I should see "Manage backup files" in the "region-main" "region"

  Scenario: Admin user changes the default home page and navigates to 'course category management' page
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And I log in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I follow "Cat 1"
    And I should not see "My courses" in the ".breadcrumb" "css_element"
    And the following config values are set as admin:
      | defaulthomepage | 3 |
    And I navigate to "Courses > Manage courses and categories" in site administration
    When I follow "Cat 1"
    Then I should see "My courses" in the ".breadcrumb" "css_element"
    And I should see "Cat 1" in the ".breadcrumb" "css_element"
    And I should see "Manage courses and categories" in the ".breadcrumb" "css_element"
