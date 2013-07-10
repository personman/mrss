Feature: CMS pages
In order to put content on the website
As an admin
I need to be able to add and edit CMS pages

  Scenario: View pages admin index
    Given I am logged in
    When I go to "/pages"
    Then I should see "Add a page"
    And I should see "Pages"
    And I should see "Title"
    And I should see "Route"
    And I should see "Status"

  Scenario: Navigate to add page form
    Given I am logged in
    And I am on "/pages"
    When I follow "Add a page"
    Then I should be on "/pages/edit"
    And I should see "Edit Page"
    And I should see "Title"

  Scenario: Create a new page
    Given I am logged in
    And I am on "/pages/edit"
    When I fill in "title" with "About Us"
    And I fill in "route" with "about-us-test"
    And I fill in "content" with "Lorem"
    And I press "Save"
    Then I should be on "/pages"
    And I should see "Page saved"
    And I should see "About Us"
    And I should see "about-us-test"
