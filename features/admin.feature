Feature: Administration pages
  In order to monitor and configure studies
  As an administrator
  I need to use the admin pages

  Scenario: Dashboard
    Given I am logged in
    When I go to "/admin"
    Then I should see "Subscriptions"

  Scenario: Admin navigation
    Given I am logged in
    When I go to "/admin"
    Then I should see "Studies"
    And I should see "Imports"
    And I should see "Colleges"

  Scenario: Manage studies
    Given I am logged in
    And I am on "/admin"
    When I follow "Studies"
    Then I should see "MRSS"
    And I should see "Edit"
    And I should see "NCCWTP"
