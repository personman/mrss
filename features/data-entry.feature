Feature: Data entry
  In order to submit data to the study
  As a user
  I need to use the data entry form

  Scenario: View data entry overview
    Given I am logged in
    When I go to "/data-entry"
    Then I should see "Data entry"
    And I should see "Form"

#  Scenario: Import page
#    Given I am logged inAnd I am on "/data-entry"
#    When I follow "Excel Spreadsheet"
#    Then I should be on "/data-entry/import"
#    And I should see "Download Excel File"
#    And I should see "Upload Excel File"
#    And I should see "Complete the Excel File"
#    And I should see "using the form below"

#  Scenario: Download export
#    Given I am logged in
#    And I am on "/data-entry/import"
#    When I follow "Download"
#    Then I should be on "/data-entry/export"
#
#  Scenario: Enter data
#    Given I am logged in
#    And I am on "/data-entry"
#    When I follow "Form 1: Instructional Costs"
#    Then I should see "Faculty Categories"
#    And I should see "Executive Staff"
#    And I should see "Clerical"
#    When I fill in "inst_full_expend" with "5"
#    And I press "Save"
#    Then I should see "Data saved"
#    And I should be on "/data-entry"
