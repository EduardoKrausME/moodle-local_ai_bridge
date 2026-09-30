<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace local_ai_bridge\plugininfo;

defined('MOODLE_INTERNAL') || die();

use core\plugininfo\base;

/**
 * Plugin information class for AI bridge provider subplugins.
 */
class aibridge extends base {
    public function is_uninstall_allowed(): bool {
        return true;
    }
}
