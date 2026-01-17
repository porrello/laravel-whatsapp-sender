<?php

namespace Dogfromthemoon\LaravelWhatsappSender\Tests;

use Dogfromthemoon\LaravelWhatsappSender\LaravelWhatsappSender;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class DownloadMediaTest extends TestCase
{
    public function test_download_media_writes_file_and_returns_a_public_url(): void
    {
        $sender = $this->app->make('laravel-whatsapp-sender');
        $folderName = 'laravel-whatsapp-sender-tests';

        // Ensure base public dir exists for public_path().
        File::ensureDirectoryExists(public_path());

        $mediaInfo = (object) [
            'url' => 'https://example.test/media/abc',
            'mime_type' => 'image/jpeg',
            'id' => 'abc',
        ];

        Http::fake([
            $mediaInfo->url => Http::response('file-bytes', 200),
        ]);

        $publicUrl = $sender->downloadMedia($mediaInfo, 'ACCESS_TOKEN', $folderName);

        $this->assertIsString($publicUrl);
        $this->assertStringContainsString($folderName, $publicUrl);
        $this->assertStringContainsString('abc.jpeg', $publicUrl);

        $expectedPath = public_path($folderName . '/abc.jpeg');
        $this->assertFileExists($expectedPath);
        $this->assertSame('file-bytes', file_get_contents($expectedPath));
    }
}
