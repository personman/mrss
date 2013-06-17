Feature: Error page
In order to properly handle unexpected cases
As any type of user
I need to see error messages

  Scenario: Page not found
    Given I am on "/fake"
    Then I should see "Page not found"
    And I should see "contact us"
    And the response status code should be 404
