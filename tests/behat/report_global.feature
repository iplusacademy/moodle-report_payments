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
      | manager2 |
      | manager3 |
      | manager4 |
    And the following "categories" exist:
      | name                   | idnumber | category |
      | Science and technology | scitech  |          |
      | Physics                | st-phys  | scitech  |
      | Philosophy             | phil     |          |
    And the following "role assigns" exist:
      | user     | role    | contextlevel | reference |
      | manager1 | manager | System       |           |
      | manager3 | manager | Category     | scitech   |
      | manager4 | manager | Category     | phil      |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | scitech  |
      | Course 2 | C2        | phil     |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | manager2 | C1     | manager        |
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
  Scenario: Admins can see the global payments report
    When I log in as "admin"
    And I am on site homepage
    And I navigate to "Reports > Payments" in current page administration
    Then I should see "EUR"
    And I should see "USD"
    And I should see "200"
    And I click on "Filters" "button"
    And I set the following fields in the "Currency" "core_reportbuilder > Filter" to these values:
      | Currency operator | Contains |
      | Currency value    | EUR      |
    And I click on "Apply" "button"
    Then I should not see "USD"
    And I click on "Reset all" "button"
    Then I should see "USD"
    And I click on "Filters" "button"
    And I click on "Cost" "button"

  @javascript
  Scenario: Managers can download the global payments report
    When I log in as "manager1"
    And I am on site homepage
    And I navigate to "Reports > Payments" in current page administration
    And I set the field "downloadtype_download" to "json"
    Then following "Download" should download between "1" and "70000" bytes

  @javascript
  Scenario: Managers can see the global payments report
    When I log in as "manager1"
    And I am on site homepage
    And I navigate to "Reports > Payments" in current page administration
    Then I should see "EUR"
    And I should see "USD"
    And I should see "200"
    And I click on "Filters" "button"
    And I set the following fields in the "Cost" "core_reportbuilder > Filter" to these values:
      | Cost operator | Contains |
      | Cost value    | 200      |
    And I click on "Apply" "button"
    Then I should not see "100"
    And I click on "Reset all" "button"
    Then I should see "USD"
    And I click on "Filters" "button"
    And I click on "Course" "button"

  @javascript
  Scenario: Global managers can see the course payments report
    When I log in as "manager1"
    And I am on the "Course 2" course page
    And I navigate to "Reports > Payments" in current page administration
    Then I should see "EUR"
    And I should see "USD"
    And I should see "200"
    And I click on "Filters" "button"
    And I set the following fields in the "Currency" "core_reportbuilder > Filter" to these values:
      | Currency operator | Contains |
      | Currency value    | EUR      |
    And I click on "Apply" "button"
    Then I should not see "USD"
    And I click on "Reset all" "button"
    Then I should see "USD"
    And I click on "Filters" "button"
    And I click on "Cost" "button"

  @javascript
  Scenario: Local managers cannot see the course payments report
    When I log in as "manager4"
    And I am on the "Course 1" course page
    Then I should see "This course requires a payment"

  @javascript
  Scenario: Local managers can see the course payments report
    When I log in as "manager3"
    And I am on the "Course 1" course page
    And I navigate to "Reports > Payments" in current page administration
    Then I should see "EUR"

  @javascript
  Scenario: Admins can see the payments report in category context
    When I log in as "admin"
    And I go to the courses management page
    Then I should see the "Course categories and courses" management page
    And I should see "scitech" in the "#category-listing ul" "css_element"
    And I should see "phil" in the "#category-listing ul" "css_element"
    And I follow "Philosophy"
    And I follow "Payments"
    Then I should not see "Nothing to display"
