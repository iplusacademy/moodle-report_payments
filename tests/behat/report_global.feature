@report @report_payments
Feature: View payment report

  Background:
    Given the following "users" exist:
      | username |
      | teacher1 |
      | student1 |
      | student2 |
      | student3 |
      | student4 |
      | student5 |
      | manager1 |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
      | Course 2 | C2        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | manager1 | C1     | manager        |
      | manager1 | C2     | manager        |
    And the following "core_payment > payment accounts" exist:
      | name           | gateways |
      | Dollar account | paypal   |
      | Euro account   | paypal   |
    And I log in as "admin"
    And I navigate to "Plugins > Enrolments > Manage enrol plugins" in site administration
    And I click on "Enable" "link" in the "Enrolment on payment" "table_row"
    And I am on the "Course 1" "enrolment methods" page
    And I select "Enrolment on payment" from the "Add method" singleselect
    And I set the following fields to these values:
      | Payment account | Euro account |
      | Enrolment fee   | 200          |
      | Currency        | Euro         |
    And I press "Add method"
    And I am on the "Course 2" "enrolment methods" page
    And I select "Enrolment on payment" from the "Add method" singleselect
    And I set the following fields to these values:
      | Payment account | Dollar account |
      | Enrolment fee   | 100            |
      | Currency        | USD            |
    And I press "Add method"
    And I log out
    And I log in as "student1"
    And I pay for course "Course 1"
    And I log out
    And I log in as "student2"
    And I pay for course "Course 1"
    And I log out
    And I log in as "student3"
    And I pay for course "Course 1"
    And I log out
    And I log in as "student4"
    And I pay for course "Course 2"
    And I log out
    And I log in as "student5"
    And I pay for course "Course 1"
    And I pay for course "Course 2"
    And I log out

  @javascript
  Scenario: Admins can see the payments report
    And I log in as "admin"
    And I am on the "Course 1" "enrolment methods" page
    And I navigate to "Reports > Payments" in current page administration
    Then I should see "EUR"
    And I should see "USD"
    And I should see "200"
    And I click on "Filters" "button"
    And I set the following fields in the "Currency" "core_reportbuilder > Filter" to these values:
      | Currency operator  | Contains |
      | Currency value     | EUR      |
    And I click on "Apply" "button"
    Then I should not see "USD"
    And I click on "Reset all" "button"
    Then I should see "USD"
    And I click on "Filters" "button"
    And I click on "Cost" "button"
