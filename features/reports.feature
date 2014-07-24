Feature: Reports
  In order to analyze project data
  As a user
  I need to view reports


  Scenario: Navigate to national report
    Given I am logged in
    Then I should see "National Report"
    When I follow "National Report"
    Then I should be on "/reports/national"
    And I should see "Prepared for Johnson County"
    And I should see "Form 1: Instructional Costs"
    And I should see "Percentiles"
    And I should see "Reported Value"

  Scenario: Navigate to peer comparison
    Given I am logged in
    Then I should see "Peer Comparison"
    When I follow "Peer Comparison"
    Then I should see "Demographic Criteria"
    And I should see "Reporting Period"
    And I should see "Peer Group Name"
