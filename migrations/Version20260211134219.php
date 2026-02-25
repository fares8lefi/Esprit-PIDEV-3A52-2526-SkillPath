<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211134219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Legacy user columns migration: no-op to avoid depending on prior
        // transient schema states.
    }

    public function down(Schema $schema): void
    {
        // No-op: up() intentionally performs no schema change.
    }
}
