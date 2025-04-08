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
 * Tests for payments report events.
 *
 * @package   report_payments
 * @copyright Medical Access Uganda Limited (e-learning.medical-access.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_payments\reportbuilder;

use core_reportbuilder_generator;
use core_reportbuilder_testcase;
use core_reportbuilder\system_report_factory;
use enrol_fee\payment\service_provider;
use report_payments\reportbuilder\datasource\payments;
use report_payments\reportbuilder\local\systemreports\{payments_course, payments_global, payments_user};

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("{$CFG->dirroot}/reportbuilder/tests/helpers.php");


/**
 * Class report payments global report tests
 *
 * @package   report_payments
 * @copyright Medical Access Uganda Limited (e-learning.medical-access.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_test extends core_reportbuilder_testcase {

    /**
     * Setup testcase.
     */
    public function setUp(): void {
        global $DB;
        parent::setUp();
        $this->setAdminUser();
        $this->resetAfterTest();
        $gen = $this->getDataGenerator();
        $pgen = $gen->get_plugin_generator('core_payment');
        $course = $gen->create_course();
        $userid = $gen->create_user()->id;
        $feeplugin = enrol_get_plugin('fee');
        $account = $pgen->create_payment_account(['gateways' => 'paypal']);
        $accountid = $account->get('id');
        $data = [
            'courseid' => $course->id,
            'customint1' => $accountid,
            'cost' => 250,
            'currency' => 'USD',
            'roleid' => 5,
        ];
        $id = $feeplugin->add_instance($course, $data);
        $paymentid = $pgen->create_payment(['accountid' => $accountid, 'amount' => 10, 'userid' => $userid]);
        service_provider::deliver_order('fee', $id, $paymentid, $userid);
        $records = $DB->get_records('payments', []);
        foreach ($records as $record) {
            $DB->set_field('payments', 'paymentarea', 'fee', ['id' => $record->id]);
        }

    }

    /**
     * Test for report content
     *
     * @covers \report_payments\reportbuilder\local\systemreports\payments_global
     * @covers \report_payments\reportbuilder\local\entities\payment
     * @return void
     */
    public function test_content(): void {
        /** @var \core_reportbuilder_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('core_reportbuilder');
        $context = \context_system::instance();
        $generator->create_report([
            'name' => 'Payments global',
            'source' => payments_global::class,
            'default' => 0,
            'type' => \core_reportbuilder\local\report\base::TYPE_SYSTEM_REPORT,
            'contextid' => $context->id,
            'component' => 'report_payments',
        ]);
    }
}
