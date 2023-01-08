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
use context_course;
use context_coursecat;
use context_system;
use context_user;
use core_reportbuilder\system_report_factory;
use enrol_fee\payment\service_provider;
use moodle_url;
use report_payments\reportbuilder\datasource\payments;
use report_payments\reportbuilder\local\systemreports\payments_course;
use report_payments\reportbuilder\local\systemreports\payments_global;
use report_payments\reportbuilder\local\systemreports\payments_user;


/**
 * Class report payments global report tests
 *
 * @package   report_payments
 * @copyright 2020 Medical Access Uganda Limited
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reports_test extends advanced_testcase {

    /** @var stdClass Course. */
    private $course;

    /** @var int User. */
    private $userid;

    /**
     * Setup testcase.
     */
    public function setUp():void {
        global $DB;
        $this->setAdminUser();
        $this->resetAfterTest();
        $gen = $this->getDataGenerator();
        $pgen = $gen->get_plugin_generator('core_payment');
        $gen->create_category();
        $category = $gen->create_category();
        $subcategory = $gen->create_category(['parent' => $category->id]);
        $gen->create_course(['category' => $subcategory->id]);
        $gen->create_course(['category' => $subcategory->id]);
        $course = $gen->create_course(['category' => $category->id]);
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
        service_provider::deliver_order('fee', $id, $paymentid, $userid);
        $this->course = $course;
        $this->userid = $userid;
    }

    /**
     * Test the global report.
     *
     * @covers \report_payments\reportbuilder\local\systemreports\payments_global
     * @covers \report_payments\reportbuilder\local\entities\payment
     */
    public function test_global() {
        $context = context_system::instance();
        $report = system_report_factory::create(payments_global::class, $context);
        $this->assertEquals($report->get_name(), 'Payments');
        $context = context_coursecat::instance($this->course->category);
        $report = system_report_factory::create(payments_global::class, $context);
        $this->assertEquals($report->get_name(), 'Payments');
    }

    /**
     * Test the course report.
     *
     * @covers \report_payments\reportbuilder\local\systemreports\payments_course
     * @covers \report_payments\reportbuilder\local\entities\payment
     */
    public function test_course() {
        $report = system_report_factory::create(payments_course::class, context_course::instance($this->course->id));
        $this->assertEquals($report->get_name(), 'Payments');
    }

    /**
     * Test the course report.
     *
     * @covers \report_payments\reportbuilder\local\systemreports\payments_user
     * @covers \report_payments\reportbuilder\local\entities\payment
     */
    public function test_user() {
        $report = system_report_factory::create(payments_user::class, context_user::instance($this->userid));
        $this->assertEquals($report->get_name(), 'Payments');
    }

    /**
     * Test the datasource.
     *
     * @covers \report_payments\reportbuilder\datasource\payments
     * @covers \report_payments\reportbuilder\local\entities\payment
     */
    public function test_datasource() {
        $gen = self::getDataGenerator()->get_plugin_generator('core_reportbuilder');
        $report = $gen->create_report(['name' => 'Pay', 'source' => payments::class, 'default' => true]);
        $this->assertEquals($report->get_formatted_name(), 'Pay');
        $preport = new payments($report);
        $this->assertEquals($preport->get_name(), 'Payments');
    }

}
