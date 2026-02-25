<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212141857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Duplicate baseline migration, superseded by earlier schema creation.
        // Keep no-op to avoid creating existing tables.
    }

    public function down(Schema $schema): void
    {
        // No-op: up() intentionally performs no schema change.
    }
}
