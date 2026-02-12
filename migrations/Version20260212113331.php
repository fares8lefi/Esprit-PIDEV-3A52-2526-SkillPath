<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212113331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY `FK_A412FA92134FCDAC`');
        $this->addSql('DROP INDEX UNIQ_A412FA92134FCDAC ON quiz');
        $this->addSql('ALTER TABLE quiz ADD cours_id INT DEFAULT NULL, DROP id_cours');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA927ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('CREATE INDEX IDX_A412FA927ECF78B0 ON quiz (cours_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA927ECF78B0');
        $this->addSql('DROP INDEX IDX_A412FA927ECF78B0 ON quiz');
        $this->addSql('ALTER TABLE quiz ADD id_cours INT NOT NULL, DROP cours_id');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT `FK_A412FA92134FCDAC` FOREIGN KEY (id_cours) REFERENCES cours (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A412FA92134FCDAC ON quiz (id_cours)');
    }
}
