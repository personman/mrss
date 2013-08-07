Feature: Data entry
  In order to submit data to the study
  As a user
  I need to use the data entry form

  Scenario: View data entry overview
    Given I am logged in
    When I go to "/data-entry"
    Then I should see "Your Data Entry Progress"
    And I should see "Import/Export"
    And I should see "Instruction"
    And I should see "Student Services"

  Scenario: Import page
    Given I am logged in
    And I am on "/data-entry"
    When I follow "Import/Export"
    Then I should be on "/data-entry/import"
    And I should see "Export your current data as an Excel file"
    And I should see "Add your new data to the file in the green column."
    And I should see "Upload the updated file using the form below."

  Scenario: Download export
    Given I am logged in
    And I am on "/data-entry/import"
    When I follow "Export your current data as an Excel file"
    Then I should be on "/data-entry/export"

  Scenario: Enter data
    Given I am logged in
    And I am on "/data-entry"
    When I follow "Instruction"
    Then I should see "Program Development"
    And I should see "Teaching"
    And I should see "Tutoring"
    When I fill in "Program Development" with "5"
    And I press "Save"
    Then I should see "Data saved"
    And I should be on "/data-entry"
