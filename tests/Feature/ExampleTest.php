<?php

namespace Bnw\Skeleton\Tests\Feature;

use Bnw\Skeleton\Tests\TestCase;

class ExampleTest extends TestCase
{
    /** @test */
    public function basicOne()
    {
        $response = $this->get('/test');
        $response->assertStatus(200);
    }

    /** @test */
    public function basicTwo()
    {
        $response = $this->get('/test/two');
        $response->assertStatus(200);
    }
}
