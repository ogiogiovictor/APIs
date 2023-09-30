<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Console\Commands\TokenLookup;
use Illuminate\Database\Eloquent\Collection;

class TokenLookupTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_it_returns_non_empty_collection(): void
    {
        $command = new TokenLookup();

        // Capture the output (e.g., console message)
        $output = [];
        exec('php artisan app:token-lookup', $output);

        // Check if the output contains the success message
        $this->assertStringContainsString('Token lookup completed.', implode("\n", $output));
    }
}
