Feature: Subscribe
  In order to share and access data
  As a new user
  I need to subscribe to a study

  Scenario: Subscribe link on homepage
    Given I am on "/"
    When I follow "Subscribe"
    Then I should be on "/subscribe"

  Scenario: Subscribe page form
    When I am on "/subscribe"
    Then I should see "Institution"
    And I should see "Administrative Contact"
    And I should see "Data Contact"

  Scenario: Fill in subscription form
    Given I am on "/subscribe"
    When I fill in "Name of Institution" with "Johnson County Community College"
    And I fill in "IPEDS Unit ID" with "155210"
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
    And I should be on "/subscribe"

  Scenario: Submit form with non-matching e-mail addresses
    Given I am on "/subscribe"
    When I fill in "E-Mail Address" with "dfergu15@jccc.edu"
    And I fill in "Confirm E-Mail Address" with "nobody@jccc.edu"
    And I press "Continue"
    Then I should see "E-Mail addresses do not match"
    And I should be on "/subscribe"

  Scenario: Submit valid form
    Given I am on "/subscribe"
    When I fill in "Name of Institution" with "Johnson County Community College"
    And I fill in "IPEDS Unit ID" with "155210"
    And I fill in "Address" with "12345 College Blvd"
    And I fill in "Address 2" with "OCB 204B"
    And I fill in "City" with "Overland Park"
    And I fill in "State" with "KS"
    And I fill in "Zip Code" with "12345"
    And I fill in "adminContact[prefix]" with "Mr."
    And I fill in "adminContact[firstName]" with "Test"
    And I fill in "adminContact[lastName]" with "Testerson"
    And I fill in "adminContact[title]" with "Chief Tester"
    And I fill in "adminContact[phone]" with "111-111-1111"
    And I fill in "adminContact[email]" with "dfergu15@jccc.edu"
    And I fill in "adminContact[emailConfirm]" with "dfergu15@jccc.edu"
    And I fill in "dataContact[prefix]" with "Mr."
    And I fill in "dataContact[firstName]" with "Test"
    And I fill in "dataContact[lastName]" with "Testerson"
    And I fill in "dataContact[title]" with "Chief Tester"
    And I fill in "dataContact[phone]" with "111-111-1111"
    And I fill in "dataContact[email]" with "dfergu15@jccc.edu"
    And I fill in "dataContact[emailConfirm]" with "dfergu15@jccc.edu"
    And I press "Continue"
    Then I should be on "/subscribe/user-agreement"
    And I should see "Data Confidentiality and Use Agreement"
    And I should see "I agree to the terms above"
    And I should see "I hereby authorize my institution"
    When I check "agree"
    And I check "authorization"
    And I fill in "Electronic Signature" with "John Doe"
    And I fill in "Title" with "Chief Tester"
    And I press "Continue"
    Then show the page
    Then I should see "Select Payment Type"
    And I should see "Pay by Credit Card"
    And I should see "Request an Invoice"
    And I should see "Paid by System"
    When I press "Request an Invoice"
    Then I should be on "/subscribe/complete"
    And I should see "Thank you for subscribing"

