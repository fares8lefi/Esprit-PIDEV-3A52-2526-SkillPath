<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212202605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Incomplete event-user pivot migration depending on previous unfinished
        // event/cours transitions. Keep as no-op for a stable migration chain.
    }

    public function down(Schema $schema): void
    {
        // No-op: up() intentionally performs no schema change.
    }
}
