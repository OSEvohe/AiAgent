<?php

namespace App\Model\MCP;

use PhpMcp\Client\Enum\TransportType;
use PhpMcp\Client\ServerConfig;

/**
 * Jetbrains MCP Client
 * This class provides configuration for the Jetbrains MCP client.
 */
class Jetbrains extends McpClient
{


    /**
     * Get client name
     * This method returns the name of the client.
     *
     * @return string
     */
    protected function getClientName(): string
    {
        return 'Jetbrains';
    }

    /**
     * Get client version
     * This method returns the version of the client.
     *
     * @return string
     */
    protected function getClientVersion(): string
    {
        return '1.0';
    }

    public function listTools(): array
    {
        $list = [];

        $excludedTools = [
            'get_open_in_editor_file_text',
            'get_open_in_editor_file_path',
            'get_selected_in_editor_text',
            'replace_selected_text',
            'replace_current_file_text',
            'get_all_open_file_texts',
            'get_all_open_file_paths',
            'list_available_actions',
            'execute_action_by_id',
            'get_progress_indicator',
            'wait',
            'reformat_current_file',
            'get_terminal_text',
            'find_commit_by_message'
        ];

        foreach (parent::listTools() as $tool) {
            if (!in_array($tool->name, $excludedTools)) {
                $list[] = $tool;
            }
        }

        return $list;
    }


}
