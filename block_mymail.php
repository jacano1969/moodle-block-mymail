<?php

//  My mail block for Moodle
//  Copyright © 2013  Institut Obert de Catalunya
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.

/**
 * My mail block
 *
 *
 * @package    block
 * @subpackage mymail
 * @author     Marc Català <mcatala@ioc.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/local/mail/message.class.php');
require_once($CFG->dirroot . '/local/mail/label.class.php');

class block_mymail extends block_base {
    /**
     * block initializations
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_mymail');
    }

    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $USER, $OUTPUT;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $count = local_mail_message::count_menu($USER->id);

        //Compose
        $content = $OUTPUT->container_start('compose');
        $url = new moodle_url('/local/mail/create.php');
        $name = html_writer::tag('span', get_string('compose', 'block_mymail'));
        $content .= html_writer::link($url, $name);
        $content .= $OUTPUT->container_end();
        //Inbox
        $content .= $OUTPUT->container_start('block_mymail_inbox');
        $params = array('t' => 'inbox');
        $url = new moodle_url('/local/mail/view.php', $params);
        $name = html_writer::tag('span', get_string('inbox', 'block_mymail'));;
        $content .= html_writer::link($url, $name);
        if (!empty($count->inbox)) {
            $content .= html_writer::tag('span', '(' . $count->inbox . ')', array('class' => 'block_mymail_count'));
        }
        $content .= $OUTPUT->container_end();
        //Starred
        $content .= $OUTPUT->container_start('starred');
        $params = array('t' => 'starred');
        $url = new moodle_url('/local/mail/view.php', $params);
        $name = html_writer::tag('span', get_string('starred', 'block_mymail'));
        $content .= html_writer::link($url, $name);
        $content .= $OUTPUT->container_end();
        //Drafts
        $content .= $OUTPUT->container_start('drafts');
        $params = array('t' => 'drafts');
        $url = new moodle_url('/local/mail/view.php', $params);
        $name = html_writer::tag('span', get_string('drafts', 'block_mymail'));
        $content .= html_writer::link($url, $name);
        if (!empty($count->drafts)) {
            $content .= html_writer::tag('span', '(' . $count->drafts . ')', array('class' => 'block_mymail_count'));
        }
        $content .= $OUTPUT->container_end();
        //Sent
        $content .= $OUTPUT->container_start('sent');
        $params = array('t' => 'sent');
        $url = new moodle_url('/local/mail/view.php', $params);
        $name = html_writer::tag('span', get_string('sentmail', 'block_mymail'));
        $content .= html_writer::link($url, $name);
        $content .= $OUTPUT->container_end();
        //Courses
        $courses = enrol_get_my_courses();
        $text = '';
        foreach ($courses as $course) {
            $params = array('t' => 'course', 'c' => $course->id);
            $url = new moodle_url('/local/mail/view.php', $params);
            $text .= html_writer::start_tag('div', array('class' => 'block_mymail_course'));
            $name = html_writer::tag('span', $course->shortname);
            $text .= html_writer::link($url, $name);
            if (!empty($count->courses[$course->id])) {
                $text .= html_writer::tag('span', '(' . $count->courses[$course->id] . ')', array('class' => 'block_mymail_count'));
            }
            $text .= html_writer::end_tag('div');
        }
        if (!empty($text)) {
            $content .= $OUTPUT->container_start('courses');
            $content .= $OUTPUT->heading(get_string('courses', 'block_mymail'), 3);
            $content .= $OUTPUT->container_start('block_mymail_courses');
            $content .= $text;
            $content .= $OUTPUT->container_end('block_mymail_courses');
            $content .= $OUTPUT->container_end('courses');
        }

        $labels = local_mail_label::fetch_user($USER->id);
        $text = '';
        if ($labels) {
            foreach ($labels as $label) {
                $params = array('t' => 'label', 'l' => $label->id());
                $url = new moodle_url('/local/mail/view.php', $params);
                $name = html_writer::tag('span', $label->name());
                $text .= html_writer::start_tag('div', array('class' => 'block_mymail_label'));
                $text .= html_writer::link($url, $name);
                if (!empty($count->labels[$label->id()])) {
                    $text .= html_writer::tag('span', '(' . $count->labels[$label->id()] . ')', array('class' => 'block_mymail_count'));
                }
                $text .= html_writer::end_tag('div');
            }
        }
        if (!empty($text)) {
            $content .= $OUTPUT->container_start('labels');
            $content .= $OUTPUT->heading(get_string('labels', 'block_mymail'), 3);
            $content .= $OUTPUT->container_start('block_mymail_labels');
            $content .= $text;
            $content .= $OUTPUT->container_end('block_mymail_labels');
            $content .= $OUTPUT->container_end('labels');
        }
        //Trash
        $content .= $OUTPUT->container_start('trash');
        $params = array('t' => 'trash');
        $url = new moodle_url('/local/mail/view.php', $params);
        $name = html_writer::tag('span', get_string('trash', 'block_mymail'));
        $content .= html_writer::link($url, $name);
        $content .= $OUTPUT->container_end();

        $this->content->text = $content;

        return $this->content;
    }

    /**
     * allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return false;
    }

    /**
     * locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my-index' => true);
    }

    public function instance_can_be_docked() {
        return false;
    }
}
