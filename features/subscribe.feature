Feature: Subscribe
  In order to share and access data
  As a new user
  I need to subscribe to a study

  Scenario: Subscribe page form
    When I am on "/subscribe"
    Then I should see "Institution"
    And I should see "Administrative Contact"
    And I should see "Data Contact"

  Scenario: Fill in subscription form
    Given I am on "/subscribe"
    When I fill in "institution[name]" with "Johnson County Community College"
    And I fill in "institution[ipeds]" with "10101010"


  Scenario: Submit empty form
    Given I am on "/subscribe"
    When I press "Continue"
    Then I should see "Please correct the problems below."
