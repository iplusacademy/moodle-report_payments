<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Step definitions to process a payment
 *
 * @package    report_payments
 * @copyright  Medical Access Uganda Limited (e-learning.medical-access.org)
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.
// For that reason, we can't even rely on $CFG->admin being available here.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ElementNotFoundException;

/**
 * Step definitions to process a payment
 *
 * @package    report_payments
 * @copyright  Medical Access Uganda Limited (e-learning.medical-access.org)
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_report_payments extends behat_base {
    /**
     * Pay for a course
     * @Then /^I pay for course "(?P<course>[^"]*)"$/
     * @param string $course
     */
    public function i_pay_for_course($course) {
        global $DB;
        $courseid = $this->get_course_id($course);
        $context = \context_course::instance($courseid);
        $user = $this->get_session_user();
        $enrol = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'fee']);
        $record = new \stdClass();
        $record->userid = $user->id;
        $record->courseid = $courseid;
        $record->enrolid = $enrol->id;
        $record->status = 0;
        $record->timestart = time();
        $DB->insert_record('user_enrolments', $record);

        $account = $DB->get_record('payment_accounts', ['id' => $enrol->customint1]);
        $record = new \stdClass();
        $record->component = 'enrol_fee';
        $record->paymentarea = 'fee';
        $record->itemid = $context->id;
        $record->userid = $user->id;
        $record->amount = $enrol->cost;
        $record->currency = $enrol->currency;
        $record->gateway = 'paypal';
        $record->accountid = $account->id;
        $record->timecreated = $record->timemodified = time();
        $DB->insert_record('payments', $record);
    }
}
