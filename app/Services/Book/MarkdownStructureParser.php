<?php

namespace App\Services\Book;

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
            1 => 'sub-book',
            2 => 'part',
            3 => 'bab',
            4 => 'chapter',
            5 => 'masala',
            6 => 'masala',
            7 => 'masala',
            8 => 'masala'
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
                    'type' => $typeMap[$level] ?? 'chapter',
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
