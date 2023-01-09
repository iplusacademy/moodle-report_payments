@report @report_payments
Feature: View payment report

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | teacher   | 1        |
      | student1 | student   | 1        |
      | student2 | student   | 3        |
      | student3 | student   | 2        |
      | student4 | student   | 4        |
      | student5 | student   | 5        |
      | manager1 | manager   | 1        |
      | manager2 | manager   | 2        |
      | manager3 | manager   | 3        |
      | manager4 | manager   | 4        |
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

  Scenario: Admins can see the global payments report
    When I log in as "admin"
    And I navigate to "Reports > Payments" in site administration
    Then I should see "EUR"
    And I should see "USD"
    And I should see "200"
    And I click on "Cost" "button"

  @javascript
  Scenario: Admins can filter the global payments report
    When I log in as "admin"
    And I navigate to "Reports > Payments" in site administration
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

  Scenario: Managers can download the global payments report
    When I log in as "manager1"
    And I navigate to "Reports > Payments" in site administration
    And I set the field "downloadtype_download" to "json"
    And I press "Download"
    And I wait until the page is ready
    # If the download step is the last in the scenario then we can sometimes run
    # into the situation where the download page causes a http redirect but behat
    # has already conducted its reset (generating an error). By putting a logout
    # step we avoid behat doing the reset until we are off that page.
    And I log out

  Scenario: Managers can see the global payments report
    When I log in as "manager1"
    And I navigate to "Reports > Payments" in site administration
    Then I should see "EUR"
    And I should see "USD"
    And I should see "200"
    And I click on "Course" "button"

  Scenario: Global managers can see the course payments report
    When I log in as "manager1"
    And I am on the "Course 2" course page
    And I navigate to "Reports > Payments" in current page administration
    Then I should see "EUR"
    And I should see "USD"
    And I should see "200"

  Scenario: Local managers cannot see the course payments report
    When I log in as "manager4"
    And I am on the "Course 1" course page
    Then I should see "This course requires a payment"

  Scenario: Local managers can see the course payments report
    When I log in as "manager3"
    And I am on the "Course 1" course page
    And I navigate to "Reports > Payments" in current page administration
    Then I should see "EUR"

  Scenario: Admins can see the payments report in subcategory context
    When I log in as "admin"
    And I go to the courses management page
    Then I should see the "Course categories and courses" management page
    And I should see "scitech" in the "#category-listing ul" "css_element"
    And I should see "phil" in the "#category-listing ul" "css_element"
    And I follow "Philosophy"
    And I follow "Payments"
    Then I should not see "Nothing to display"

  Scenario: Admins can see the payments report in category context
    When I log in as "admin"
    And I go to the courses management page
    Then I should see the "Course categories and courses" management page
    And I follow "Science and technology"
    And I follow "Payments"
    Then I should not see "Nothing to display"

  Scenario: Admins can see the payments report in user context
    When I log in as "admin"
    And I am on the "Course 1" course page
    And I navigate to course participants
    And I follow "student 1"
    And I follow "Payments"
    Then I should not see "Nothing to display"
