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

use context_course;
use context_system;
use context_user;
use context_coursecat;
use core_reportbuilder_generator;
use core_reportbuilder_testcase;
use core_reportbuilder\system_report_factory;
use enrol_fee\payment\service_provider;
use report_payments\reportbuilder\datasource\payments;
use report_payments\reportbuilder\local\systemreports\{payments_course, payments_global, payments_user};
use report_payments\reportbuilder\local\entities\payment;
use PHPUnit\Framework\Attributes\CoversClass;

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
#[CoversClass(local\entities\payment::class)]
#[CoversClass(local\systemreports\payments_global::class)]
#[CoversClass(local\systemreports\payments_user::class)]
#[CoversClass(local\systemreports\payments_course::class)]
final class report_test extends core_reportbuilder_testcase {
    /** @var stdClass Course. */
    private $course;

    /** @var int User. */
    private $userid;

    /**
     * Setup testcase.
     */
    public function setUp(): void {
        global $DB, $CFG;
        parent::setUp();
        require_once("{$CFG->dirroot}/reportbuilder/tests/fixtures/system_report_available.php");
        $this->setAdminUser();
        $this->resetAfterTest();
        $gen = $this->getDataGenerator();
        $pgen = $gen->get_plugin_generator('core_payment');
        $category = $gen->create_category();
        $gen->create_course(['category' => $category->id]);
        $this->course = $gen->create_course(['category' => $category->id]);
        $userid = $gen->create_user()->id;
        $this->userid = $gen->create_user()->id;

        $gen->create_user();

        $feeplugin = enrol_get_plugin('fee');
        $account = $pgen->create_payment_account(['gateways' => 'paypal']);
        $accountid = $account->get('id');
        $data = [
            'courseid' => $this->course->id,
            'customint1' => $accountid,
            'cost' => 250,
            'currency' => 'USD',
            'roleid' => 5,
        ];
        $id = $feeplugin->add_instance($this->course, $data);
        $paymentid = $pgen->create_payment(['accountid' => $accountid, 'amount' => 20, 'userid' => $userid]);
        service_provider::deliver_order('fee', $id, $paymentid, $userid);
        $DB->set_field('user', 'deleted', true, ['id' => $userid]);
        $paymentid = $pgen->create_payment(['accountid' => $accountid, 'amount' => 10, 'userid' => $this->userid]);
        service_provider::deliver_order('fee', $id, $paymentid, $this->userid);
        $records = $DB->get_records('payments', []);
        foreach ($records as $record) {
            $DB->set_field('payments', 'paymentarea', 'fee', ['id' => $record->id]);
        }
    }

    /**
     * Test payment
     */
    public function test_payment(): void {
        $payment = new local\entities\payment();
        $report = $payment->initialise();
        $this->assertEquals('payment', $report->get_entity_name());
        $this->assertEquals(new \lang_string('payments'), $report->get_entity_title());
        $this->assertEquals(['payments'], array_keys($report->get_table_aliases()));
        $this->assertFalse($report->has_table_join_alias('payment'));

        $columns = $report->get_columns();
        $this->assertCount(6, $columns);
        $this->assertTrue($columns['accountid']->get_is_sortable());
        $this->assertEquals($columns['accountid']->get_attributes(), []);
        $this->assertEquals($columns['accountid']->get_title(), 'Name');
        $this->assertTrue($columns['component']->get_is_sortable());
        $this->assertEquals($columns['component']->get_attributes(), []);
        $this->assertEquals($columns['component']->get_title(), 'Plugin');
        $this->assertTrue($columns['gateway']->get_is_sortable());
        $this->assertEquals($columns['gateway']->get_attributes(), []);
        $this->assertEquals($columns['gateway']->get_title(), 'Payment gateway');
        $this->assertTrue($columns['amount']->get_is_sortable());
        $this->assertEquals($columns['amount']->get_attributes(), []);
        $this->assertEquals($columns['amount']->format_value(['amount' => 666]), '0.00');
        $this->assertTrue($columns['currency']->get_is_sortable());
        $this->assertEquals($columns['currency']->get_attributes(), []);
        $this->assertTrue($columns['timecreated']->get_is_sortable());
        $this->assertEquals($columns['timecreated']->get_attributes(), ['class' => 'text-end']);

        $filters = $report->get_filters();
        $this->assertCount(6, $filters);
        $this->assertCount(0, $filters['accountid']->get_joins());
        $this->assertStringContainsString('Test', array_values($filters['accountid']->get_options())[0]);
        $this->assertCount(0, $filters['component']->get_joins());
        $this->assertCount(0, $filters['gateway']->get_joins());
        $this->assertCount(0, $filters['currency']->get_joins());
        $this->assertCount(0, $filters['amount']->get_joins());
        $this->assertCount(0, $filters['timecreated']->get_joins());

        $conditions = $report->get_conditions();
        $this->assertCount(6, $conditions);
        $this->assertEquals('payment', $conditions['accountid']->get_entity_name());
        $this->assertEquals('payment', $conditions['component']->get_entity_name());
        $this->assertEquals('payment', $conditions['gateway']->get_entity_name());
        $this->assertEquals('payment', $conditions['currency']->get_entity_name());
        $this->assertEquals('payment', $conditions['amount']->get_entity_name());
        $this->assertEquals('payment', $conditions['timecreated']->get_entity_name());
    }

    /**
     * Test for report content global
     */
    public function test_content_global(): void {
        $generator = $this->getDataGenerator();
        $context = context_coursecat::instance($this->course->category);
        $report = $generator->get_plugin_generator('core_reportbuilder')->create_report([
            'name' => 'Payments global',
            'source' => payments_global::class,
            'default' => 0,
            'type' => \core_reportbuilder\local\report\base::TYPE_SYSTEM_REPORT,
            'contextid' => $context->id,
            'component' => 'report_payments',
        ]);
        $this->assertEquals($context, $report->get_context());
        $this->assertEquals('Payments global', $report->get_formatted_name());
        $this->assertEquals('payment', $report->get_entity('enrol'));

        $report = system_report_factory::create(payments_global::class, $context);
        $this->assertEquals($report->get_initial_sort_column()->get_name(), 'gateway');

        $condition = $report->get_base_condition();
        $this->assertCount(2, $condition);
        $this->assertStringContainsString('deleted', $condition[0]);
        $this->assertCount(1, $condition[1]);

        $fields = $report->get_base_fields();
        $this->assertCount(1, $fields);
        $this->assertStringContainsString('id', $fields[0]);

        $columns = $report->get_active_columns();
        $this->assertCount(7, $columns);
        $this->assertTrue($columns['payment:accountid']->get_is_sortable());
        $this->assertEquals($columns['payment:accountid']->get_attributes(), []);
        $this->assertEquals($columns['payment:accountid']->get_title(), 'Account name');
        $this->assertTrue($columns['course:coursefullnamewithlink']->get_is_sortable());
        $this->assertEquals($columns['course:coursefullnamewithlink']->get_attributes(), []);
        $this->assertEquals($columns['course:coursefullnamewithlink']->get_title(), 'Course');
        $this->assertTrue($columns['payment:gateway']->get_is_sortable());
        $this->assertEquals($columns['payment:gateway']->get_attributes(), []);
        $this->assertTrue($columns['payment:amount']->get_is_sortable());
        $this->assertEquals($columns['payment:amount']->get_attributes(), []);
        $this->assertTrue($columns['payment:currency']->get_is_sortable());
        $this->assertEquals($columns['payment:currency']->get_attributes(), []);
        $this->assertTrue($columns['payment:timecreated']->get_is_sortable());
        $this->assertEquals($columns['payment:timecreated']->get_attributes(), ['class' => 'text-end']);

        $this->assertCount(7, $report->get_active_filters());
        $this->assertCount(7, $report->get_filters());
        $filters = $report->get_filters();
        $this->assertCount(1, $filters['user:fullname']->get_joins());
        $this->assertCount(0, $filters['payment:gateway']->get_joins());

        $this->assertCount(0, $report->get_active_conditions());
        $this->assertEquals(0, $report->get_applied_filter_count());

        $report->set_initial_sort_column('payment:accountid', SORT_DESC);
        $this->assertEquals($report->get_initial_sort_column(), $columns['payment:accountid']);
        $fields = $report->get_base_fields();
        $this->assertCount(1, $fields);
        $this->assertStringContainsString('id', $fields[0]);
        $condition = $report->get_base_condition();
        $this->assertCount(2, $condition);
        $this->assertStringContainsString('deleted', $condition[0]);
        $this->assertCount(1, $condition[1]);
    }

    /**
     * Test for report content category
     */
    public function test_content_category(): void {
        /** @var \core_reportbuilder_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('core_reportbuilder');
        $context = context_system::instance();
        $generator->create_report([
            'name' => 'Payments global',
            'source' => payments_global::class,
            'default' => false,
            'type' => \core_reportbuilder\local\report\base::TYPE_SYSTEM_REPORT,
            'contextid' => $context->id,
            'component' => 'report_payments',
        ]);

        $report = system_report_factory::create(payments_global::class, $context);
        $this->assertEquals($report->get_initial_sort_column()->get_name(), 'gateway');

        $fields = $report->get_base_fields();
        $this->assertCount(1, $fields);
        $this->assertStringContainsString('id', $fields[0]);

        $condition = $report->get_base_condition();
        $this->assertCount(2, $condition);
        $this->assertStringContainsString('deleted', $condition[0]);
        $this->assertCount(1, $condition[1]);

        $columns = $report->get_active_columns();
        $this->assertCount(7, $columns);
        $this->assertTrue($columns['course:coursefullnamewithlink']->get_is_sortable());
        $this->assertEquals($columns['course:coursefullnamewithlink']->get_attributes(), []);
        $this->assertEquals($columns['course:coursefullnamewithlink']->get_title(), 'Course');
        $this->assertTrue($columns['payment:accountid']->get_is_sortable());
        $this->assertEquals($columns['payment:accountid']->get_attributes(), []);
        $this->assertEquals($columns['payment:accountid']->get_title(), 'Account name');
        $this->assertTrue($columns['payment:gateway']->get_is_sortable());
        $this->assertEquals($columns['payment:gateway']->get_attributes(), []);
        $this->assertTrue($columns['payment:currency']->get_is_sortable());
        $this->assertEquals($columns['payment:currency']->get_attributes(), []);
        $this->assertTrue($columns['payment:amount']->get_is_sortable());
        $this->assertEquals($columns['payment:amount']->get_attributes(), []);
        $this->assertTrue($columns['payment:timecreated']->get_is_sortable());
        $this->assertEquals($columns['payment:timecreated']->get_attributes(), ['class' => 'text-end']);

        $this->assertCount(7, $report->get_active_filters());
        $this->assertCount(7, $report->get_filters());
        $filters = $report->get_filters();
        $this->assertCount(1, $filters['user:fullname']->get_joins());
        $this->assertCount(0, $filters['payment:gateway']->get_joins());

        $report->set_initial_sort_column('payment:accountid', SORT_DESC);
        $this->assertEquals($report->get_initial_sort_column(), $columns['payment:accountid']);
        $fields = $report->get_base_fields();
        $this->assertCount(1, $fields);
        $this->assertStringContainsString('id', $fields[0]);
        $condition = $report->get_base_condition();
        $this->assertCount(2, $condition);
        $this->assertStringContainsString('deleted', $condition[0]);
        $this->assertCount(1, $condition[1]);
    }

    /**
     * Test for report content user
     */
    public function test_content_user(): void {
        $report = system_report_factory::create(payments_user::class, context_user::instance($this->userid));
        $this->assertEquals($report->get_initial_sort_column()->get_name(), 'timecreated');

        $condition = $report->get_base_condition();
        $this->assertCount(2, $condition);
        $this->assertStringContainsString('userid', $condition[0]);
        $this->assertCount(2, $condition[1]);

        $fields = $report->get_base_fields();
        $this->assertCount(1, $fields);
        $this->assertStringContainsString('id', $fields[0]);

        $columns = $report->get_active_columns();
        $this->assertCount(5, $columns);
        $this->assertTrue($columns['payment:gateway']->get_is_sortable());
        $this->assertEquals($columns['payment:gateway']->get_attributes(), []);
        $this->assertTrue($columns['payment:amount']->get_is_sortable());
        $this->assertEquals($columns['payment:amount']->get_attributes(), []);
        $this->assertTrue($columns['payment:currency']->get_is_sortable());
        $this->assertEquals($columns['payment:currency']->get_attributes(), []);
        $this->assertTrue($columns['payment:timecreated']->get_is_sortable());
        $this->assertEquals($columns['payment:timecreated']->get_attributes(), ['class' => 'text-end']);
        $this->assertTrue($columns['course:coursefullnamewithlink']->get_is_sortable());
        $this->assertEquals($columns['course:coursefullnamewithlink']->get_attributes(), []);
        $this->assertEquals($columns['course:coursefullnamewithlink']->get_title(), 'Course');

        $this->assertCount(0, $report->get_active_filters());
        $this->assertCount(0, $report->get_filters());
        $this->assertCount(0, $report->get_active_conditions());
        $this->assertEquals(0, $report->get_applied_filter_count());

        $report->set_initial_sort_column('payment:currency', SORT_DESC);
        $this->assertEquals($report->get_initial_sort_column(), $columns['payment:currency']);
    }

    /**
     * Test for report content course
     */
    public function test_content_course(): void {
        $context = context_course::instance($this->course->id);
        $report = system_report_factory::create(payments_course::class, $context);
        $this->assertEquals($report->get_initial_sort_column()->get_name(), 'timecreated');

        $condition = $report->get_base_condition();
        $this->assertCount(2, $condition);
        $this->assertStringContainsString('deleted', $condition[0]);
        $this->assertCount(2, $condition[1]);

        $fields = $report->get_base_fields();
        $this->assertCount(1, $fields);
        $this->assertStringContainsString('id', $fields[0]);

        $columns = $report->get_active_columns();
        $this->assertCount(6, $columns);
        $this->assertTrue($columns['payment:accountid']->get_is_sortable());
        $this->assertEquals($columns['payment:accountid']->get_attributes(), []);
        $this->assertEquals($columns['payment:accountid']->get_title(), 'Account name');
        $this->assertTrue($columns['user:fullnamewithpicturelink']->get_is_sortable());
        $this->assertEquals($columns['user:fullnamewithpicturelink']->get_attributes(), []);
        $this->assertEquals($columns['user:fullnamewithpicturelink']->get_title(), 'Full name with picture and link');
        $this->assertTrue($columns['payment:amount']->get_is_sortable());
        $this->assertEquals($columns['payment:amount']->get_attributes(), []);
        $this->assertTrue($columns['payment:currency']->get_is_sortable());
        $this->assertEquals($columns['payment:currency']->get_attributes(), []);
        $this->assertTrue($columns['payment:timecreated']->get_is_sortable());
        $this->assertEquals($columns['payment:timecreated']->get_attributes(), ['class' => 'text-end']);

        $filters = $report->get_filters();
        $this->assertCount(1, $filters['user:fullname']->get_joins());
        $this->assertCount(0, $filters['payment:gateway']->get_joins());

        $this->assertCount(5, $report->get_active_filters());
        $this->assertCount(5, $report->get_filters());
        $this->assertCount(0, $report->get_active_conditions());
        $this->assertEquals(0, $report->get_applied_filter_count());

        $report->set_initial_sort_column('payment:currency', SORT_DESC);
        $this->assertEquals($report->get_initial_sort_column(), $columns['payment:currency']);

        $context = context_course::instance(1);
        $report = system_report_factory::create(payments_course::class, $context);
        $this->assertEquals($report->get_initial_sort_column()->get_name(), 'timecreated');
    }
}
