<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class SystemController extends Controller
{
    /**
     * Run an Artisan command
     */
    public function runCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string',
            'args' => 'nullable|array'
        ]);

        $command = $request->input('command');
        $args = $request->input('args', []);

        // Whitelist allowed commands for security
        $allowedCommands = [
            'media:import-transcripts',
            'project:sync-storage',
            'project:seed-realistic',
            'optimize:clear'
        ];

        if (!in_array($command, $allowedCommands)) {
            return response()->json(['message' => 'Command not allowed'], 403);
        }

        try {
            $output = new BufferedOutput();
            Artisan::call($command, $args, $output);

            return response()->json([
                'status' => 'success',
                'output' => $output->fetch()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'output' => $e->getTraceAsString()
            ], 500);
        }
    }
}
