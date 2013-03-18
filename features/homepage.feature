Feature: Homepage

  Scenario: Load the homepage
    Given I am on "/"
    Then I should see "Zend Framework 2"
    Then I should see "Zend Framework 2"
    And the response status code should be 200