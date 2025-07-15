# Summary of Agents

## inform_user
- Function: Sends custom messages to the user.
- Parameters: message (string)
- Usage: Used to notify users of important information or updates.

## validator_agent_toolTool
- Function: Validates code quality, checks git status, and errors in files.
- Parameters: task (string)
- Usage: Ensures code meets quality standards and is free of errors.

## coding_agent_toolTool
- Function: Reads, writes, modifies, or creates code files.
- Parameters: task (string)
- Usage: Primary tool for implementing code tasks.

## search_agent_toolTool
- Function: Searches online documentation for information related to coding tasks.
- Parameters: task (string)
- Usage: Gathers information, examples, and best practices.

## sequentialthinking
- Function: Plans steps with thoughts, revisions, etc.
- Parameters: Various (thought, nextThoughtNeeded, etc.)
- Usage: Breaks down complex problems into steps, coordinates between agents.

### Code Report for src/Model Directory

**Directory Structure:**
- Extensions: .gitignore, Tool, Agent, Team
- IO: Terminal.php
- Core: Provider (OpenAIService.php, OpenAIServiceInterface.php), Tool (ToolsHandler.php, AITool.php, AgentTool.php), Message (ToolResultResponse.php, SystemMessage.php, UserMessage.php), Agent (AgentRunner.php, Team.php, MessageContextTrait.php), Team, IOInterface.php, Mcp (McpClient.php, McpTool.php, McpsPool.php)
- Tool: InformUserTool.php, WeatherTool.php
- Team: CodingTeam.php