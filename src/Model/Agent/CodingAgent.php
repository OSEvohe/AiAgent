<?php

namespace App\Model\Agent;

use App\Model\MCP\McpClient;

class CodingAgent implements Agent
{
    private string $mcpJsonConfig = <<<JSON
{
  "mcpServers": {
    "jetbrains": {
      "command": "npx",
      "args": ["-y", "@jetbrains/mcp-proxy"]
    },
    "sequential-thinking": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-sequential-thinking"
      ],
      "excluded_tools": [
        "get_open_in_editor_file_text",
        "get_open_in_editor_file_path",
        "get_selected_in_editor_text",
        "replace_selected_text",
        "replace_current_file_text",
        "get_all_open_file_texts",
        "get_all_open_file_paths",
        "list_available_actions",
        "execute_action_by_id",
        "get_progress_indicator",
        "wait",
        "reformat_current_file",
        "get_terminal_text",
        "find_commit_by_message"
      ]
    }
  }
}
JSON;

    public function getName(): string
    {
        return 'CodingAgent';
    }

    public function getDescription(): string
    {
        return 'An agent specialized in coding tasks, capable of generating and modifying code based on user input.';
    }

    public function getTools(): array
    {
        return [];
    }

    public function getMcps(): array
    {
        return McpClient::fromJsonConfig($this->mcpJsonConfig);
    }
}
