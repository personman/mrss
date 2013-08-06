Feature: User account
In order to authenticate users
As any type of user
I need to be able to register and sign in

  Scenario: Show user login form
    When I go to "/user"
    Then I should see "Sign In"
    And I should see "Password"

  Scenario: Registration is disabled
    When I go to "/user/register"
    Then I should see "Register"
    And I should see "Registration is disabled"

  Scenario: User login
    Given I am logged out
    When I go to "/user/login"
    And I fill in "identity" with "dfergu15@jccc.edu"
    And I fill in "credential" with "111111"
    And I press "Sign In"
    Then show the page
    Then I should be on "/data-entry"

  Scenario: Login link
    Given I am logged out
    When I go to "/"
    Then I should see "Log In"

