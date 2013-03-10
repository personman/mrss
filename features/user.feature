Feature: User account
In order to authenticate users
As any type of user
I need to be able to register and sign in

  Scenario: Show user login form
    Given that I am logged out
    When I go to "/user"
    Then I should see "Sign In"
    And I should see "Password"
