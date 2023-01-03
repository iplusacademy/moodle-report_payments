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
 * @copyright 2023 Medical Access Uganda Limited
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_payments\reportbuilder;

use advanced_testcase;
use context_system;
use moodle_url;
use report_payments\reportbuilder\local\systemreports;
use core_reportbuilder\system_report_factory;

/**
 * Class report payments global report tests
 *
 * @package   report_payments
 * @copyright 2020 Medical Access Uganda Limited
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class global_report_test extends advanced_testcase {

    /**
     * Setup testcase.
     */
    public function setUp():void {
        global $DB;
        $this->setAdminUser();
        $this->resetAfterTest();
        $gen = $this->getDataGenerator();
        $pgen = $gen->get_plugin_generator('core_payment');
        $course = $gen->create_course();
        $userid = $gen->create_user()->id;
        $roleid = $DB->get_record('role', ['shortname' => 'student'])->id;
        $feeplugin = enrol_get_plugin('fee');
        $account = $pgen->create_payment_account(['gateways' => 'paypal']);
        $accountid = $account->get('id');
        $data = [
            'courseid' => $course->id,
            'customint1' => $accountid,
            'cost' => 250,
            'currency' => 'USD',
            'roleid' => $roleid,
        ];
        $id = $feeplugin->add_instance($course, $data);
        $paymentid = $pgen->create_payment(['accountid' => $accountid, 'amount' => 10, 'userid' => $userid]);
        \enrol_fee\payment\service_provider::deliver_order('fee', $id, $paymentid, $userid);
    }

    /**
     * Test the report.
     *
     * @covers \report_payments\reportbuilder\local\systemreports\payment_global
     * @covers \report_payments\reportbuilder\local\entities\payment
     */
    public function test_report() {
        $report = system_report_factory::create(
            \report_payments\reportbuilder\local\systemreports\payment_global::class, context_system::instance());
        $this->assertEquals($report->get_name(), 'Payments');
    }

}
