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
 * Optional interface for AI providers supporting speech-to-text.
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge\bridge;

/**
 * Implemented only by AI bridges with a real audio transcription endpoint.
 */
interface transcription_provider_interface {
    /**
     * Transcribe a private Moodle audio file.
     *
     * @param \stored_file $audio Audio stored through Moodle File API.
     * @param array $config Decrypted connection settings.
     * @param string $model Route model capable of audio transcription.
     * @param array $options Supported provider transcription options.
     * @return response Transcription result.
     */
    public function transcribe(\stored_file $audio, array $config, string $model,
            array $options = []): response;
}
