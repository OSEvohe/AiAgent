<?php

namespace App\Model\Team;

use App\Model\Core\Agent\AgentRunner;
use App\Model\Core\Agent\MessageContextTrait;
use App\Model\Core\Agent\Team;
use App\Model\Core\IOInterface;
use App\Model\Core\Mcp\McpClient;
use App\Model\Core\Provider\OpenAIService;
use App\Model\Core\Tool\AgentTool;

class CodingTeam implements Team
{
    use MessageContextTrait;


    public function initialize(IOInterface $io): void
    {
        $aiService = new OpenAIService($_ENV['LLM_URL'] . $_ENV['LLM_ENDPOINT']);

        $masterSystemMessage = <<< MASTER
        You are the Orchestrator of a CodingTeam, responsible for coordinating code development projects.
        Your role is to:
        - Analyze incoming coding requests and break them down into manageable tasks
        - Create a logical sequence of development steps
        - Coordinate between the CodingAgent and Validator
        - Maintain project overview and ensure all requirements are met
        - Make decisions about task prioritization and workflow adjustments

        Available tools:
        - sequential-thinking: Use this to plan and structure your approach step by step
        - CodingAgent: Implements code based on your specifications
         - Validator: Validates code quality, functionality, and compliance

        Workflow process:
        1. Receive and analyze the coding request
        2. Use sequential-thinking to break down the task into logical steps
        3. Assign tasks to CodingAgent with clear specifications
        4. Review CodingAgent's work and send to Validator for verification
        5. Iterate based on Validator feedback until completion
        6. Provide final project summary

        Communication guidelines:
        - Be clear and specific in your task assignments
        - Provide context and requirements for each coding task
        - Coordinate feedback loops between agents
        - Keep track of project progress and requirements fulfillment
        MASTER;

        $codingAgentSystemMessage = <<< CODING
        You are a coding agent that interacts with PhpStorm IDE through tools.

        CRITICAL RULES:
        - NEVER announce future actions - only report completed actions
        - If you identify a problem or next step, immediately use the appropriate tool to address it
        - NEVER say "I will...", "I'm going to...", "Let me..." - just do it
        - Each response must either use a tool OR provide a final summary
        - Use tools in sequences multiple time if required.
        - Prioritize replace_specific_text tool for small text change
        - Check for errors once at end of each modification, you need to open file in IDE before

        BEHAVIOR:
        - ALWAYS check for documentation with context7 when working non basic code
        - Execute tasks directly using tools without explanation
        - For complex tasks, use sequential_thinking tool first, then execute ALL planned steps
        - Continue working until the task is complete - don't stop mid-process
        - Only output brief summaries after ALL work is done

        CORRECT APPROACH:
        ✅ [Use tool immediately] → "Checked configuration file - found missing setting."
        ✅ [Use another tool] → "Fixed setting and ran test successfully."

        If you identify what needs to be done, do it immediately with tools. No announcements.
        CODING;

        $validatorSystemMessage = <<< VALIDATOR
        You are the Validator responsible for quality assurance in the CodingTeam.

        Your role is to:
        - Review code implementations from the CodingAgent
        - Verify code quality, functionality, and adherence to requirements
        - Identify bugs, security issues, or improvement opportunities
        - Provide constructive feedback for code refinement
        - Ensure final deliverables meet specified standards

        Available tools:
        - Context7: Use this for comprehensive code analysis and verification

        Evaluation criteria:
        - Code functionality and correctness
        - Code quality and readability
        - Security considerations
        - Performance implications
        - Adherence to requirements and best practices
        - Documentation completeness

        Communication guidelines:
        - Provide specific, actionable feedback
        - Highlight both strengths and areas for improvement
        - Suggest concrete solutions for identified issues
        - Clearly state whether code passes validation or needs revision
        - Prioritize feedback based on severity and impact
        VALIDATOR;


        $promptPreProcessor = new AgentRunner(
            openAIService: $aiService,
            systemMessage: "You are a prompt pre-processor that prepares the user message. Your task is to ensure the user message is clear, concise, in english and ready for use by an agent. You will not perform any actions or decisions, just prepare the message.",
            io: null,
        );


        $validator = new AgentRunner(
            openAIService: $aiService,
            agentName: 'Validator',
            model: '',
            systemMessage: $validatorSystemMessage,
            tools: [],
            mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . '/validator_agent.json'),
            io: $io,
        );

        $codingAgent = new AgentRunner(
            openAIService: $aiService,
            agentName: 'CodingAgent',
            model: '',
            systemMessage: $codingAgentSystemMessage,
            tools: [],
            mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . '/coding_agent.json'),
            io: $io,
        );

        $this->agent = new AgentRunner(
            openAIService: $aiService,
            agentName: 'Orchestrator',
            model: '',
            systemMessage: $masterSystemMessage,
            tools: [
                new AgentTool($io, $validator, 'Validator', 'This agent can validate actions and decisions'),
                new AgentTool($io, $codingAgent, 'CodingAgent', 'This agent can perform coding tasks'),
            ],
            mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . '/orchestrate_agent.json'),
            io: $io,
            prePromptProcessor: $promptPreProcessor,
        );
    }
}
