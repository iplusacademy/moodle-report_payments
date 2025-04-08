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
namespace report_payments\event;

/**
 * Class report_payments_events_testcase
 *
 * Class for tests related to payments report events.
 *
 * @package   report_payments
 * @copyright Medical Access Uganda Limited (e-learning.medical-access.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class event_test extends \advanced_testcase {
    /**
     * Setup testcase.
     */
    public function setUp(): void {
        parent::setUp();
        $this->setAdminUser();
        $this->resetAfterTest();
    }

    /**
     * Test the report viewed event.
     *
     * It's not possible to use the moodle API to simulate the viewing of log report, so here we
     * simply create the event and trigger it.
     * @covers \report_payments\event\report_viewed
     */
    public function test_report_viewed(): void {
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        $context = \context_course::instance($course->id);
        $event = \report_payments\event\report_viewed::create(['context' => $context, 'courseid' => $course->id]);
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = end($events);
        $this->assertInstanceOf('\report_payments\event\report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals('Payments report viewed', $event->get_name());
        $this->assertStringContainsString('the payments report for the course with id', $event->get_description());
        $url = new \moodle_url('/report/payments/index.php', ['courseid' => $course->id]);
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $context = \context_coursecat::instance($course->category);
        $event = \report_payments\event\report_viewed::create(['context' => $context]);
        $event->trigger();
        $events = $sink->get_events();
        $event = end($events);
        $this->assertInstanceOf('\report_payments\event\report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertStringContainsString('viewed the payments report for the category', $event->get_description());
        $url = new \moodle_url('/report/payments/index.php', ['categoryid' => $course->category]);
        $this->assertEquals($url, $event->get_url());
        $this->assertEquals('Payments report viewed', $event->get_name());

        $context = \context_system::instance();
        $event = \report_payments\event\report_viewed::create(['context' => $context]);
        $event->trigger();
        $events = $sink->get_events();
        $event = end($events);
        $this->assertInstanceOf('\report_payments\event\report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertStringContainsString('viewed the global payments report', $event->get_description());
        $url = new \moodle_url('/report/payments/index.php');
        $this->assertEquals($url, $event->get_url());
        $this->assertEquals('Payments report viewed', $event->get_name());

        $context = \context_user::instance($user->id);
        $event = \report_payments\event\report_viewed::create(['context' => $context, 'relateduserid' => $user->id]);
        $event->trigger();
        $events = $sink->get_events();
        $event = end($events);
        $this->assertInstanceOf('\report_payments\event\report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertStringContainsString('viewed the payments report about the user with id', $event->get_description());
        $url = new \moodle_url('/report/payments/index.php', ['userid' => $user->id]);
        $this->assertEquals($url, $event->get_url());
        $this->assertEquals('Payments report viewed', $event->get_name());

        $sink->close();
    }

    /**
     * Tests the report navigation as an admin.
     * @coversNothing
     */
    public function test_report_payments_navigation(): void {
        global $CFG, $PAGE, $USER;
        require_once($CFG->dirroot . '/report/payments/lib.php');
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $systemcontext = \context_system::instance();
        $coursecontext = \context_course::instance($course->id);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);
        $PAGE->set_course($course);

        $tree = new \global_navigation($PAGE);
        $this->assertEquals(null, report_payments_extend_navigation_course($tree, $course, $coursecontext));
        $this->arrayHasKey('payments', $tree);
        $this->assertEquals(null, report_payments_extend_navigation_course($tree, $course, $systemcontext));
        $this->arrayHasKey('payments', $tree);

        $tree = new \core_user\output\myprofile\tree();
        $this->assertEquals(true, report_payments_myprofile_navigation($tree, $user, true, $course));
        $this->setGuestUser();
        $this->assertEquals(false, report_payments_myprofile_navigation($tree, $USER, true, $course));
        $this->arrayHasKey('payments', $tree);
    }

    /**
     * Tests the report page type list.
     * @coversNothing
     */
    public function test_report_payments_page_type(): void {
        global $CFG, $PAGE;
        $course = $this->getDataGenerator()->create_course();
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);
        require_once($CFG->dirroot . '/report/payments/lib.php');
        $list = report_payments_page_type_list(null, null, null);
        $this->arrayHasKey('payments', $list);
    }
}
