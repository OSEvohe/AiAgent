<?php

namespace App\Service;

use Exception;

/**
 * A service to apply a patch to a file.
 * The patch format uses '// ...existing code...' as a placeholder for unchanged sections.
 */
class FilePatcher
{
    /**
     * Applies a patch to a file.
     *
     * @param string $originalFilePath The path to the file to be patched.
     * @param string $patchContent The content of the patch.
     * @return void
     * @throws Exception If the file cannot be read, the patch is invalid, or writing fails.
     */
    public function patchFile(string $originalFilePath, string $patchContent): void
    {
        if (!is_readable($originalFilePath)) {
            throw new Exception("Original file is not readable: {$originalFilePath}");
        }

        $originalLines = file($originalFilePath, FILE_IGNORE_NEW_LINES);
        $patchLines = preg_split("/\r\n|\n|\r/", $patchContent);

        $newLines = $this->applyPatch($originalLines, $patchLines);

        $result = file_put_contents($originalFilePath, implode(PHP_EOL, $newLines));

        if ($result === false) {
            throw new Exception("Failed to write patched content to file: {$originalFilePath}");
        }
    }

    /**
     * The core logic for applying the patch.
     *
     * @param array $originalLines
     * @param array $patchLines
     * @return array
     * @throws Exception
     */
    private function applyPatch(array $originalLines, array $patchLines): array
    {
        $newFileLines = [];
        $originalLineIndex = 0;
        $patchLineIndex = 0;

        while ($patchLineIndex < count($patchLines)) {
            $patchLine = $patchLines[$patchLineIndex];

            if (str_contains($patchLine, '// ...existing code...')) {
                $patchLineIndex++;
                // Find the next block of non-placeholder lines in the patch
                $contextBlock = [];
                while ($patchLineIndex < count($patchLines) && !str_contains($patchLines[$patchLineIndex], '// ...existing code...')) {
                    $contextBlock[] = $patchLines[$patchLineIndex];
                    $patchLineIndex++;
                }

                if (empty($contextBlock)) {
                    // Placeholder is at the end, so append the rest of the original file
                    while ($originalLineIndex < count($originalLines)) {
                        $newFileLines[] = $originalLines[$originalLineIndex];
                        $originalLineIndex++;
                    }
                    continue;
                }

                // Find this context block in the original file
                $foundIndex = -1;
                for ($i = $originalLineIndex; $i <= count($originalLines) - count($contextBlock); $i++) {
                    $slice = array_slice($originalLines, $i, count($contextBlock));
                    if ($slice == $contextBlock) {
                        $foundIndex = $i;
                        break;
                    }
                }

                if ($foundIndex !== -1) {
                    // Add the lines from the original file before the context block
                    for ($i = $originalLineIndex; $i < $foundIndex; $i++) {
                        $newFileLines[] = $originalLines[$i];
                    }
                    $originalLineIndex = $foundIndex;
                } else {
                    throw new Exception("Could not find context block in original file: \n" . implode("\n", $contextBlock));
                }
            } else {
                // This is a regular line from the patch (added/modified)
                $newFileLines[] = $patchLine;

                // Try to advance the original line index if the line exists there too
                if ($originalLineIndex < count($originalLines) && $originalLines[$originalLineIndex] == $patchLine) {
                    $originalLineIndex++;
                }
                $patchLineIndex++;
            }
        }

        return $newFileLines;
    }
}

