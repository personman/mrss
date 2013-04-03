Feature: Homepage

  Scenario: Load the homepage
    Given I am on "/"
    Then I should see "Maximizing Resources for Student Success"
    And the response status code should be 200