<?php

namespace App\Model\Tool;

use App\Entity\PendingChange;
use App\Model\Core\Message\ToolResultResponse;
use App\Model\Core\Tool\AITool;
use App\Repository\PendingChangeRepository;
use App\Service\FilePatcher;
use OpenAI\Responses\Chat\CreateResponseToolCall;
use Exception;

/**
 * A tool to create a pending change for a file in the workspace.
 */
class FilePatcherTool extends AITool
{
    public function __construct(private readonly FilePatcher $filePatcher)
    {


        $name = 'edit_file_with_patch';
        $description = 'Edit a file in the workspace using a patch format. Use `// ...existing code...` to represent unchanged regions.';
        $parameters = [
            'type' => 'object',
            'properties' => [
                'filePath' => [
                    'type' => 'string',
                    'description' => 'The absolute path of the file to edit.',
                ],
                'patchContent' => [
                    'type' => 'string',
                    'description' => 'The patch content to apply to the file. Use `// ...existing code...` for unchanged parts.',
                ],
            ],
            'required' => ['filePath', 'patchContent'],
        ];

        parent::__construct($name, $description, $parameters);
    }

    public function execute(CreateResponseToolCall $toolCall): ToolResultResponse
    {
        $arguments = json_decode($toolCall->function->toArray()['arguments'], true);
        $filePath = $arguments['filePath'];
        $patchContent = $arguments['patchContent'];

        try {
            $this->filePatcher->patchFile($filePath, $patchContent);

            $result = [
                'status' => 'success',
                'message' => "patch change applied for file {$filePath}.",
            ];
        } catch (Exception $e) {
            $result = [
                'status' => 'error',
                'message' => "Failed to apply change for file {$filePath}: " . $e->getMessage(),
            ];
        }

        return ToolResultResponse::fromArray([
            'tool_call_id' => $toolCall->id,
            'tool_name' => $this->getName(),
            'content' => json_encode($result),
        ]);
    }
}
