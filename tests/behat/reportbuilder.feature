@report @report_payments @javascript
Feature: Payment reportbuilder feature

  Background:
    Given the following "users" exist:
      | username |
      | student1 |
      | teacher1 |
      | manager1 |
    And the following "core_payment > payment accounts" exist:
      | name           | gateways |
      | Dollar account | paypal   |
      | Euro account   | paypal   |
    And the following "core_payment > payments" exist:
      | account        | amount | user     |
      | Dollar account | 10     | student1 |

  Scenario: Admins can generate a reportbuilder payments customreport
    When I log in as "admin"
    And I change window size to "large"
    When I navigate to "Reports > Report builder > Custom reports" in site administration
    And I click on "New report" "button"
    And I set the following fields in the "New report" "dialogue" to these values:
      | Name                  | My report |
      | Report source         | Payments  |
      | Include default setup | 1         |
    And I click on "Save" "button" in the "New report" "dialogue"
    Then I should see "My report"
    # Confirm we see the default columns in the report.
    And I should see "Full name" in the "reportbuilder-table" "table"
    And I should see "Cost" in the "reportbuilder-table" "table"
    And I should not see "Course" in the "reportbuilder-table" "table"
    And I click on "Close 'My report' editor" "button"
    And the following should exist in the "reportbuilder-table" table:
      | Name      | Report source | Modified by |
      | My report | Payments      | Admin User  |

  Scenario: Admins can generate a reportbuilder payments customreport without default setup
    When I log in as "admin"
    And I change window size to "large"
    When I navigate to "Reports > Report builder > Custom reports" in site administration
    And I click on "New report" "button"
    And I set the following fields in the "New report" "dialogue" to these values:
      | Name                  | My report |
      | Report source         | Payments  |
      | Include default setup | 0         |
    And I click on "Save" "button" in the "New report" "dialogue"
    Then I should see "My report"
    And I should see "Nothing to display"
    And I click on "Close 'My report' editor" "button"
    And the following should exist in the "reportbuilder-table" table:
      | Name      | Report source | Modified by |
      | My report | Payments      | Admin User  |

  Scenario Outline: Download payments report in different formats
    And the following "core_reportbuilder > Reports" exist:
      | name            | source                                            |
      | Report payments | report_payments\reportbuilder\datasource\payments |
    When I am on the "Report payments" "reportbuilder > Editor" page logged in as "admin"
    And I click on "Switch to preview mode" "button"
    Then I set the field "Download table data as" to "<format>"
    And I press "Download"
    Examples:
      | format                             |
      | Comma separated values (.csv)      |
      | Microsoft Excel (.xlsx)            |
      | HTML table                         |
      | Javascript Object Notation (.json) |
      | OpenDocument (.ods)                |
      | Portable Document Format (.pdf)    |
