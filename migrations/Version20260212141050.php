<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212141050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Duplicate transitional migration from an inconsistent schema snapshot.
        // Keep no-op to avoid conflicts in the canonical migration chain.
    }

    public function down(Schema $schema): void
    {
        // No-op: up() intentionally performs no schema change.
    }
}
