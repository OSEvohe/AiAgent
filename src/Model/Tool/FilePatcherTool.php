<?php

namespace App\Model\Tool;

use App\Entity\PendingChange;
use App\Model\Core\Message\ToolResultResponse;
use App\Model\Core\Tool\AITool;
use App\Repository\PendingChangeRepository;
use OpenAI\Responses\Chat\CreateResponseToolCall;
use Exception;

/**
 * A tool to create a pending change for a file in the workspace.
 */
class FilePatcherTool extends AITool
{
    private PendingChangeRepository $pendingChangeRepository;

    public function __construct(PendingChangeRepository $pendingChangeRepository)
    {
        $this->pendingChangeRepository = $pendingChangeRepository;

        $name = 'edit_file_with_patch';
        $description = 'Creates a pending change for a file in the workspace using a patch format. Use `// ...existing code...` to represent unchanged regions.';
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
            $pendingChange = new PendingChange();
            $pendingChange->setFilePath($filePath);
            $pendingChange->setPatchContent($patchContent);

            $this->pendingChangeRepository->save($pendingChange);

            $result = [
                'status' => 'success',
                'message' => "Pending change created for file {$filePath}.",
                'pending_change_id' => $pendingChange->getId(),
            ];
        } catch (Exception $e) {
            $result = [
                'status' => 'error',
                'message' => "Failed to create pending change for file {$filePath}: " . $e->getMessage(),
            ];
        }

        return ToolResultResponse::fromArray([
            'tool_call_id' => $toolCall->id,
            'tool_name' => $this->getName(),
            'content' => json_encode($result),
        ]);
    }
}
