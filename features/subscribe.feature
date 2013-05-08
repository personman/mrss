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
    When I fill in "Name of Institution" with "Johnson County Community College"
    And I fill in "IPEDS Unit ID" with "10101010"
    And I fill in "Address" with "12345 College Blvd"
    And I fill in "Address 2" with "OCB 204B"
    And I fill in "City" with "Overland Park"
    And I fill in "State" with "KS"
    And I fill in "Zip Code" with "12345"


  Scenario: Submit empty form
    Given I am on "/subscribe"
    When I press "Continue"
    Then I should see "Please correct the problems below."
    And I should see "Value is required and can't be empty"

  Scenario: Submit form with invalid data
    Given I am on "/subscribe"
    When I fill in "IPEDS Unit ID" with "I do not know"
    And I fill in "Zip Code" with "someplace"
    And I fill in "E-Mail Address" with "not-an-email"
    And I press "Continue"
    Then I should see "Please correct the problems below"
    And I should see "Use the format \"123456\""
    And I should see "The input is not a valid email address"

  Scenario: Submit form with non-matching e-mail addresses
    Given I am on "/subscribe"
    When I fill in "E-Mail Address" with "dfergu15@jccc.edu"
    And I fill in "Confirm E-Mail Address" with "nobody@jccc.edu"
    And I press "Continue"
    Then I should see "E-Mail addresses do not match"
