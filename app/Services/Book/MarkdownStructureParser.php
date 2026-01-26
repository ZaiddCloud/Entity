<?php

namespace App\Services\Book;

use App\Enums\ContentNodeType;

class MarkdownStructureParser
{
    /**
     * Parse markdown into a flat array of nodes with their content blocks.
     */
    public function parse(string $markdown): array
    {
        $lines = explode("\n", $markdown);
        $nodes = [];
        $currentIndex = -1;

        $typeMap = [
            1 => ContentNodeType::SUB_BOOK->value,
            2 => ContentNodeType::PART->value,
            3 => ContentNodeType::BAB->value,
            4 => ContentNodeType::CHAPTER->value,
            5 => ContentNodeType::MASALAH->value,
            6 => ContentNodeType::MASALAH->value,
            7 => ContentNodeType::MASALAH->value,
            8 => ContentNodeType::MASALAH->value
        ];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line))
                continue;

            if (preg_match('/^(#{1,8})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                $title = $matches[2];

                $nodes[] = [
                    'title' => $title,
                    'level' => $level,
                    'type' => $typeMap[$level] ?? ContentNodeType::CHAPTER->value,
                    'blocks' => []
                ];
                $currentIndex = count($nodes) - 1;
            } elseif ($currentIndex !== -1) {
                $nodes[$currentIndex]['blocks'][] = [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $line
                        ]
                    ]
                ];
            }
        }

        return $nodes;
    }

    /**
     * Build a nested hierarchy from the flat list of nodes.
     */
    public function buildHierarchy(string $markdown): array
    {
        $nodes = $this->parse($markdown);
        $hierarchy = [];
        $stack = [];

        foreach ($nodes as &$node) {
            $node['children'] = [];

            while (!empty($stack) && $stack[count($stack) - 1]['level'] >= $node['level']) {
                array_pop($stack);
            }

            if (empty($stack)) {
                $hierarchy[] = &$node;
                $stack[] = &$node;
            } else {
                $stack[count($stack) - 1]['children'][] = &$node;
                $stack[] = &$stack[count($stack) - 1]['children'][count($stack[count($stack) - 1]['children']) - 1];
            }
        }

        return $hierarchy;
    }
}
