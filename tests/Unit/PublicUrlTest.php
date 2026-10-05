<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\MapEmbed;
use Tests\TestCase;

class PublicUrlTest extends TestCase
{
    public function test_only_plain_http_urls_and_local_files_are_accepted(): void
    {
        $this->assertSame('https://example.com/a', safe_url('https://example.com/a'));
        $this->assertNull(safe_url('https://user:secret@example.com/path'));
        $this->assertNull(safe_url('javascript:alert(1)'));
        $this->assertNull(MapEmbed::url('https://www.google.com/search?q=lagos'));
        $this->assertNotNull(MapEmbed::url('https://maps.google.com/maps?q=lagos&output=embed'));
        $this->assertNull(public_file('../.env'));
        $this->assertNotNull(public_file('files/membership-guide.pdf'));
    }
}
