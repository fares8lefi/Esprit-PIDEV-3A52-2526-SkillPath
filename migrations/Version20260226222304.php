<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260226222304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE certificate (id INT AUTO_INCREMENT NOT NULL, cert_code VARCHAR(255) NOT NULL, issued_at DATETIME NOT NULL, user_id INT NOT NULL, course_id INT NOT NULL, UNIQUE INDEX UNIQ_219CDA4A1CBA3E5B (cert_code), INDEX IDX_219CDA4AA76ED395 (user_id), INDEX IDX_219CDA4A591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE user_course_view ADD is_completed TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AA76ED395');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A591CC992');
        $this->addSql('DROP TABLE certificate');
        $this->addSql('ALTER TABLE user_course_view DROP is_completed');
    }
}
