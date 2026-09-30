<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => '\\local_ai_bridge\\task\\sync_tenants',
        'blocking' => 0,
        'minute' => '17',
        'hour' => '2',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];
