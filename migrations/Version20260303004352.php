<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303004352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY `FK_A412FA92591CC992`');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_course_view DROP FOREIGN KEY `FK_9F44BF1D591CC992`');
        $this->addSql('ALTER TABLE user_course_view ADD CONSTRAINT FK_9F44BF1D591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92591CC992');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT `FK_A412FA92591CC992` FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE user_course_view DROP FOREIGN KEY FK_9F44BF1D591CC992');
        $this->addSql('ALTER TABLE user_course_view ADD CONSTRAINT `FK_9F44BF1D591CC992` FOREIGN KEY (course_id) REFERENCES course (id)');
    }
}
