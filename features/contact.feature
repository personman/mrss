Feature: Contact form
  In order to communicate with NHEBI staff
  As a user
  I need to use the contact form


  Scenario: Navigate to contact form
    Given I am on "/"
    Then I should see "Contact Us"
    When I follow "Contact Us"
    Then I should be on "/contact"

  Scenario: Use contact form
    Given I am on "/contact"
    Then I should see "You can also reach us by phone"
    When I fill in "from" with "dfergu15@jccc.edu"
    And I fill in "subject" with "A question (via Behat)"
    And I fill in "body" with "Lorem"
