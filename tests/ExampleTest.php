<?php

namespace Easoblue\LaraHelper\Tests;

use Orchestra\Testbench\TestCase;
use Easoblue\LaraHelper\EasolarahelpServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [EasolarahelpServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
