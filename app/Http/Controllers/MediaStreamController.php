<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaStreamController extends Controller
{
    /**
     * Stream a video file with range request support for seeking
     */
    public function streamVideo(Request $request, string $path)
    {
        // Fix: Resolve relative to public disk root to avoid double "videos/videos" nesting
        // If path starts with "videos/", referencing 'app/public' works. 
        // If path is just filename, we might need to prepend.
        // Let's assume path represents the full relative path from storage/app/public.
        
        $fullPath = storage_path('app/public/' . $path);
        
        // Fallback: If not found, try adding 'videos/' prefix if missing
        if (!file_exists($fullPath) && !str_starts_with($path, 'videos/')) {
             $fullPath = storage_path('app/public/videos/' . $path);
        }
        
        if (!file_exists($fullPath)) {
            abort(404);
        }

        $size = filesize($fullPath);
        $mimeType = mime_content_type($fullPath) ?: 'video/mp4';
        
        $start = 0;
        $end = $size - 1;
        $length = $size;
        
        // Handle range requests for seeking support
        if ($request->header('Range')) {
            $range = $request->header('Range');
            
            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = intval($matches[1]);
                $end = $matches[2] !== '' ? intval($matches[2]) : $end;
                $length = $end - $start + 1;
            }
        }

        $response = new StreamedResponse(function () use ($fullPath, $start, $length) {
            $stream = fopen($fullPath, 'rb');
            fseek($stream, $start);
            
            $chunkSize = 8192; // 8KB chunks
            $bytesRead = 0;
            
            while (!feof($stream) && $bytesRead < $length) {
                $bytesToRead = min($chunkSize, $length - $bytesRead);
                echo fread($stream, $bytesToRead);
                $bytesRead += $bytesToRead;
                flush();
            }
            
            fclose($stream);
        }, $request->header('Range') ? 206 : 200);

        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Length', $length);
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Cache-Control', 'public, max-age=31536000');
        
        if ($request->header('Range')) {
            $response->headers->set('Content-Range', "bytes {$start}-{$end}/{$size}");
        }

        return $response;
    }

    /**
     * Stream an audio file with range request support
     */
    public function streamAudio(Request $request, string $path)
    {
        // Fix: Resolve relative to public disk root
        $fullPath = storage_path('app/public/' . $path);

        // Fallback for imports folder
        if (!file_exists($fullPath) && !str_starts_with($path, 'imports/')) {
             $fullPath = storage_path('app/public/imports/' . $path);
        }
        
        if (!file_exists($fullPath)) {
            abort(404);
        }

        $size = filesize($fullPath);
        $mimeType = mime_content_type($fullPath) ?: 'audio/mpeg';
        
        $start = 0;
        $end = $size - 1;
        $length = $size;
        
        // Handle range requests
        if ($request->header('Range')) {
            $range = $request->header('Range');
            
            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = intval($matches[1]);
                $end = $matches[2] !== '' ? intval($matches[2]) : $end;
                $length = $end - $start + 1;
            }
        }

        $response = new StreamedResponse(function () use ($fullPath, $start, $length) {
            $stream = fopen($fullPath, 'rb');
            fseek($stream, $start);
            
            $chunkSize = 8192;
            $bytesRead = 0;
            
            while (!feof($stream) && $bytesRead < $length) {
                $bytesToRead = min($chunkSize, $length - $bytesRead);
                echo fread($stream, $bytesToRead);
                $bytesRead += $bytesToRead;
                flush();
            }
            
            fclose($stream);
        }, $request->header('Range') ? 206 : 200);

        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Length', $length);
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Cache-Control', 'public, max-age=31536000');
        
        if ($request->header('Range')) {
            $response->headers->set('Content-Range', "bytes {$start}-{$end}/{$size}");
        }

        return $response;
    }
}
