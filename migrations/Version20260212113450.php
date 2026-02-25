<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212113450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Legacy alteration generated before baseline table creation.
        // Keep as no-op for deterministic fresh installs.
    }

    public function down(Schema $schema): void
    {
        // No-op: up() intentionally performs no schema change.
    }
}
