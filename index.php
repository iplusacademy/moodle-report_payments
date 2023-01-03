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
 * Global payments.
 *
 * @package    report_payments
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once("{$CFG->libdir}/adminlib.php");

use report_payments\reportbuilder\local\systemreports\payment_global;
use core_reportbuilder\system_report_factory;

$courseid = optional_param('course', 1, PARAM_INT);
$course = get_course($courseid);
$context = context_course::instance($courseid);

$PAGE->set_url(new \moodle_url('/report/payments/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$strheading = get_string('payments');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);

require_login();

//admin_externalpage_setup('payments');

$download = optional_param('download', false, PARAM_BOOL);
$filter = optional_param('filter', null, PARAM_TEXT);

echo $OUTPUT->header();
$report = system_report_factory::create(payment_global::class, \context_system::instance());
// Trigger a report viewed event.
$event = \report_payments\event\report_viewed::create(['context' => $context]);
$event->trigger();

if (!empty($filter)) {
    $report->set_filter_values(['payment:name_values' => $filter]);
}

echo $report->output();
echo $OUTPUT->footer();
