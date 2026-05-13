<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305223009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL, DROP coordinates_latitude, DROP coordinates_longitude');
        $this->addSql('CREATE UNIQUE INDEX user_course_unique ON user_course_view (user_id, course_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location ADD coordinates_latitude DOUBLE PRECISION DEFAULT NULL, ADD coordinates_longitude DOUBLE PRECISION DEFAULT NULL, DROP latitude, DROP longitude');
        $this->addSql('DROP INDEX user_course_unique ON user_course_view');
    }
}
