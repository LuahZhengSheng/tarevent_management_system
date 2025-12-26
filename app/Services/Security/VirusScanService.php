<?php

namespace App\Services\Security;

use App\Exceptions\VirusScanFailedException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class VirusScanService
{
    public function scanOrFail(UploadedFile $file): array
    {
        $result = $this->scan($file);

        if (($result['status'] ?? 'error') !== 'clean') {
            $status = $result['status'] ?? 'error';
            $reason = $result['reason'] ?? 'unknown';

            throw new VirusScanFailedException(
                $this->userMessageFor($status, $reason),
                $reason,
                $result
            );
        }

        return $result;
    }

    public function scan(UploadedFile $file): array
    {
        try {
            $apiKey = config('virus_scan.virustotal.api_key');
            if (!$apiKey) {
                return $this->error('missing_api_key');
            }

            $base = rtrim(config('virus_scan.virustotal.base_url'), '/');
            $pollSeconds = (int) config('virus_scan.virustotal.poll_seconds', 8);
            $maxPolls = (int) config('virus_scan.virustotal.max_polls', 10);

            // VirusTotal: >32MB needs /files/upload_url first [web:138]
            $uploadUrl = $base . '/files';
            if ($file->getSize() > 32 * 1024 * 1024) {
                $resp = Http::withHeaders(['x-apikey' => $apiKey])
                    ->timeout(20)
                    ->get($base . '/files/upload_url');

                if (!$resp->successful()) {
                    return $this->error('get_upload_url_failed', ['http' => $resp->status()]);
                }

                $uploadUrl = $resp->json('data');
                if (!$uploadUrl) {
                    return $this->error('upload_url_missing');
                }
            }

            $upload = Http::withHeaders(['x-apikey' => $apiKey])
                ->timeout(60)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post($uploadUrl);

            if (!$upload->successful()) {
                return $this->error('upload_failed', ['http' => $upload->status()]);
            }

            $analysisId = $upload->json('data.id');
            if (!$analysisId) {
                return $this->error('analysis_id_missing');
            }

            // Poll /analyses/{id} until completed [web:152]
            for ($i = 0; $i < $maxPolls; $i++) {
                sleep($pollSeconds);

                $r = Http::withHeaders(['x-apikey' => $apiKey])
                    ->timeout(20)
                    ->get($base . '/analyses/' . $analysisId);

                if (!$r->successful()) {
                    continue;
                }

                $status = $r->json('data.attributes.status'); // queued|in-progress|completed [web:152]
                if ($status !== 'completed') {
                    continue;
                }

                $stats = $r->json('data.attributes.stats') ?? [];
                $malicious = (int) ($stats['malicious'] ?? 0);
                $suspicious = (int) ($stats['suspicious'] ?? 0);

                if ($malicious > 0 || $suspicious > 0) {
                    return [
                        'status' => 'infected',
                        'engine' => 'virustotal',
                        'reason' => 'detected',
                        'analysis_id' => $analysisId,
                        'stats' => $stats,
                    ];
                }

                return [
                    'status' => 'clean',
                    'engine' => 'virustotal',
                    'reason' => 'ok',
                    'analysis_id' => $analysisId,
                    'stats' => $stats,
                ];
            }

            // fail closed on timeout
            return $this->error('analysis_timeout', ['analysis_id' => $analysisId]);

        } catch (Throwable $e) {
            Log::warning('Virus scan exception (fail closed)', [
                'error' => $e->getMessage(),
            ]);

            return $this->error('exception', ['message' => $e->getMessage()]);
        }
    }

    private function userMessageFor(string $status, string $reason): string
    {
        if ($status === 'infected') {
            return 'Upload blocked: the file was flagged as potentially harmful.';
        }

        // error -> fail closed
        return match ($reason) {
            'missing_api_key' => 'Upload blocked: virus scanning is not configured. Please contact support.',
            'analysis_timeout' => 'Upload blocked: virus scan timed out. Please try again later or upload a smaller file.',
            'upload_failed', 'get_upload_url_failed' => 'Upload blocked: virus scanning service is temporarily unavailable. Please try again later.',
            default => 'Upload blocked: virus scan could not be completed. Please try again later.',
        };
    }

    private function error(string $reason, array $meta = []): array
    {
        return array_merge([
            'status' => 'error',
            'engine' => 'virustotal',
            'reason' => $reason,
        ], $meta);
    }
}
