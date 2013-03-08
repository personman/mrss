Feature: Error page

  Scenario: Page not found
    Given I am on "/fake"
    Then I should see "Page not found"
    And the response status code should be 404