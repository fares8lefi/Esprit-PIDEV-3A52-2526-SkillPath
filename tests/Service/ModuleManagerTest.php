<?php

namespace App\Tests\Service;

use App\Service\ModuleManager;
use PHPUnit\Framework\TestCase;

class ModuleManagerTest extends TestCase
{
    private ModuleManager $moduleManager;

    protected function setUp(): void
    {
        $this->moduleManager = new ModuleManager();
    }

    /**
     * Test Rule 1: Valid title (>= 2 chars)
     */
    public function testValidTitle(): void
    {
        $this->assertTrue($this->moduleManager->isValidTitle('Introduction'));
        $this->assertTrue($this->moduleManager->isValidTitle('PHP'));
    }

    /**
     * Test Rule 1: Invalid title (< 2 chars)
     */
    public function testInvalidTitle(): void
    {
        $this->assertFalse($this->moduleManager->isValidTitle('A'));
        $this->assertFalse($this->moduleManager->isValidTitle(' '));
        $this->assertFalse($this->moduleManager->isValidTitle(''));
       
    }

    /**
     * Test Rule 3: Valid description (10-500 chars)
     */
    public function testValidDescription(): void
    {
        $this->assertTrue($this->moduleManager->isValidDescription('Ceci est une description valide de plus de 10 caractères.'));
    }

    /**
     * Test Rule 3: Invalid description (too short or too long)
     */
    public function testInvalidDescription(): void
    {
        $this->assertFalse($this->moduleManager->isValidDescription('Court')); // Trop court
        $this->assertFalse($this->moduleManager->isValidDescription(str_repeat('A', 501))); // Trop long
    }

    /**
     * Test Rule 2: Accessible if scheduledAt is null
     */
    public function testAccessibleWhenNull(): void
    {
        $this->assertTrue($this->moduleManager->isAccessible(null));
    }

    /**
     * Test Rule 2: Accessible if scheduledAt is in the past
     */
    public function testAccessibleWhenPast(): void
    {
        $pastDate = new \DateTime('-1 day');
        $this->assertTrue($this->moduleManager->isAccessible($pastDate));
    }

    /**
     * Test Rule 2: Inaccessible if scheduledAt is in the future
     */
    public function testInaccessibleWhenFuture(): void
    {
        $futureDate = new \DateTime('+1 day');
        $this->assertFalse($this->moduleManager->isAccessible($futureDate));
    }
}
