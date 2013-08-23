Feature: College systems
  In order to manage college system
  As an admin
  I need to be able to create and edit systems

  Scenario: Add a system
    Given I am logged in
    And I am on "/admin"
    When I follow "Systems"
    Then I should see "Add a system"
    When I follow "Add a system"
    Then I should see "Name of System"
    And I should see "IPEDS Unit ID"
    And I should see "Address"
    And I should see "Zip Code"
