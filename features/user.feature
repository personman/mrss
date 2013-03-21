Feature: User account
In order to authenticate users
As any type of user
I need to be able to register and sign in

  Scenario: Show user login form
    When I go to "/user"
    Then I should see "Sign In"
    And I should see "Password"

  Scenario: Show user registration form
    When I go to "/user/register"
    Then I should see "Register"
    And I should see "Password Verify"
    And I should see "Display Name"
