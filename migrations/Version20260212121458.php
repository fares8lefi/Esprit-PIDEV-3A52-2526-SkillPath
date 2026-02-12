<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212121458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY `FK_FDCA8C9CAFC2B591`');
        $this->addSql('DROP INDEX IDX_FDCA8C9CAFC2B591 ON cours');
        $this->addSql('ALTER TABLE cours ADD description LONGTEXT DEFAULT NULL, ADD level VARCHAR(30) DEFAULT NULL, ADD image VARCHAR(255) DEFAULT NULL, ADD categorie VARCHAR(50) DEFAULT NULL, DROP contenu, DROP type, DROP module_id');
        $this->addSql('ALTER TABLE module ADD type VARCHAR(50) DEFAULT NULL, ADD contenu LONGTEXT DEFAULT NULL, ADD cours_id INT NOT NULL, CHANGE name name VARCHAR(120) NOT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE date_creation date_creation DATETIME NOT NULL, CHANGE level level VARCHAR(30) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT FK_C2426287ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('CREATE INDEX IDX_C2426287ECF78B0 ON module (cours_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours ADD contenu LONGTEXT NOT NULL, ADD type VARCHAR(50) NOT NULL, ADD module_id INT NOT NULL, DROP description, DROP level, DROP image, DROP categorie');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT `FK_FDCA8C9CAFC2B591` FOREIGN KEY (module_id) REFERENCES module (id)');
        $this->addSql('CREATE INDEX IDX_FDCA8C9CAFC2B591 ON cours (module_id)');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY FK_C2426287ECF78B0');
        $this->addSql('DROP INDEX IDX_C2426287ECF78B0 ON module');
        $this->addSql('ALTER TABLE module DROP type, DROP contenu, DROP cours_id, CHANGE name name VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE date_creation date_creation DATE NOT NULL, CHANGE level level VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) NOT NULL');
    }
}
