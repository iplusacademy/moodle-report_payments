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
 * The payment report viewed event.
 *
 * @package   report_payments
 * @copyright Medical Access Uganda Limited (e-learning.medical-access.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_payments\event;

/**
 * The payment report viewed event.
 *
 * @package   report_payments
 * @copyright Medical Access Uganda Limited (e-learning.medical-access.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_viewed extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventreportviewed', 'report_payments');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $str = "The user with id '$this->userid' viewed the ";
        switch ($this->contextlevel) {
            case CONTEXT_USER:
                return $str . "payments report about the user with id '$this->relateduserid'.";
            case CONTEXT_COURSE:
                return $str . "payments report for the course with id '$this->courseid'.";
            case CONTEXT_COURSECAT:
                return $str . "payments report for the category with id '$this->contextinstanceid'.";
            default:
                return $str . "global payments report.";
        }
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        $params = [];
        switch ($this->contextlevel) {
            case CONTEXT_USER:
                $params['userid'] = $this->relateduserid;
                break;
            case CONTEXT_COURSE:
                $params['courseid'] = $this->courseid;
                break;
            case CONTEXT_COURSECAT:
                $params['categoryid'] = $this->contextinstanceid;
                break;
        }
        return new \moodle_url('/report/payments/index.php', $params);
    }
}
