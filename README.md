# CodingTeam Application

## Overview

The CodingTeam application is a Symfony-based project designed to facilitate code development and project management through a team of specialized AI agents. The application coordinates between different agents to analyze coding requests, implement code solutions, and validate the results.

## Features

- **Orchestrator Agent**: Manages the overall project workflow, breaking down tasks and coordinating between coding and validation agents.
- **CodingAgent**: Implements code solutions based on specifications provided by the Orchestrator.
- **Validator**: Reviews and validates code for quality, functionality, and adherence to requirements.
- **Command-Line Interface**: Provides commands for interacting with the agents and managing the development process.

## Project Structure

- **src/Command**: Contains command-line commands for interacting with the application.
  - `BasicAgentCommand.php`: Command for interacting with the CodingAgent.
  - `BasicChatCommand.php`: Command for starting a chat session with the agents.
- **src/Model/Team**: Contains the core logic for the CodingTeam.
  - `CodingTeam.php`: Implements the team structure and agent coordination.
- **src/Model/Core**: Contains core components and interfaces for the application.
- **src/Model/IO**: Contains input/output handling components.
- **src/Model/Extensions**: Contains extensions for the application.
- **src/Model/Tool**: Contains tools used by the agents.
- **src/Model/Team**: Contains team-related models and components.

## Dependencies

The project uses the following dependencies:
- PHP 8.2+
- Symfony components (Console, Dotenv, Flex, FrameworkBundle, HttpClient, Runtime, Yaml)
- Guzzle HTTP client
- OpenAI PHP client
- PHP-MCP client
- PSR HTTP Message

## Environment Configuration

The application requires environment variables to be set in a `.env` file:
- `LLM_URL`: Base URL for the language model service.
- `LLM_ENDPOINT`: Endpoint for the language model service.
- `AGENT_CONFIG_DIR`: Directory containing agent configuration files.

## Usage

### Basic Agent Command

To interact with the CodingAgent, use the `basic-agent` command:
```bash
php bin/console basic-agent "Your coding task here"
```

### Basic Chat Command

To start a chat session with the agents, use the `basic-chat-agent` command:
```bash
php bin/console basic-chat-agent
```

## Workflow

1. **Receive and Analyze**: The Orchestrator receives and analyzes coding requests.
2. **Plan and Structure**: The Orchestrator uses sequential thinking to break down tasks.
3. **Assign Tasks**: The Orchestrator assigns tasks to the CodingAgent.
4. **Implement Code**: The CodingAgent writes, modifies, and debugs code.
5. **Validate Code**: The Validator reviews and validates the code.
6. **Iterate**: The process iterates based on feedback until completion.
7. **Provide Summary**: The Orchestrator provides a final project summary.

## Best Practices

- Write clean, readable, and well-commented code.
- Follow established coding standards and conventions.
- Test code when possible before submission.
- Explain implementation choices and any trade-offs made.
- Ask for clarification if requirements are unclear.

## Communication Guidelines

- Report implemented features and how they address requirements.
- Highlight challenges encountered or assumptions made.
- Provide context for technical decisions.
- Be ready to iterate based on feedback from the Validator.