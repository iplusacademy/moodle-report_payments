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
 * Lib functions
 *
 * @package   report_payments
 * @copyright Medical Access Uganda Limited (e-learning.medical-access.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_payments_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/payments:view', $context)) {
        $url = new moodle_url('/report/payments/index.php', ['courseid' => $course->id]);
        $txt = get_string('payments');
        $navigation->add($txt, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Adds nodes to category navigation
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param context $context The context of the coursecategory
 * @return void|null return null if we don't want to display the node.
 */
function report_payments_extend_navigation_category_settings($navigation, $context) {
    if (has_capability('report/payments:overview', $context)) {
        $url = new moodle_url('/report/payments/index.php', ['categoryid' => $context->instanceid]);
        $txt = get_string('payments');
        $navigation->add($txt, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}


/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function report_payments_myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (isguestuser($user) || !isloggedin()) {
        return false;
    }
    $context = \context_user::instance($user->id);
    if (has_capability('report/payments:userview', $context)) {
        $url = new moodle_url('/report/payments/index.php', ['userid' => $user->id]);
        $txt = get_string('payments');
        $node = new \core_user\output\myprofile\node('reports', 'payments', $txt, null, $url);
        $tree->add_node($node);
    }
    return true;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array
 */
function report_payments_page_type_list($pagetype, $parentcontext, $currentcontext) {
    return [
        '*' => new \lang_string('page-x', 'pagetype'),
        'report-*' => new \lang_string('page-report-x', 'pagetype'),
        'report-payments-*' => new \lang_string('page-report-payments-x', 'report_payments'),
        'report-payments-index' => new \lang_string('page-report-payments-index', 'report_payments'),
        'report-payments-course' => new \lang_string('page-report-payments-course', 'report_payments'),
        'report-payments-user' => new \lang_string('page-report-payments-user', 'report_payments'),
        ];
}
