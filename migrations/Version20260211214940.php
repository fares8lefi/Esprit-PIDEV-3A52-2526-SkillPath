<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211214940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Legacy content/cours/module/user transformations have already been
        // applied by other migrations / manual updates; treat as no-op.
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE content (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, type VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, contenu LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, module_id INT NOT NULL, INDEX IDX_FEC530A9AFC2B591 (module_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT `FK_FEC530A9AFC2B591` FOREIGN KEY (module_id) REFERENCES module (id)');
        $this->addSql('ALTER TABLE cours ADD contenu VARCHAR(255) NOT NULL, ADD type VARCHAR(255) NOT NULL, ADD module_id INT DEFAULT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, DROP description, DROP level, DROP image');
        $this->addSql('CREATE INDEX IDX_FDCA8C9CAFC2B591 ON cours (module_id)');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY FK_C2426287ECF78B0');
        $this->addSql('DROP INDEX IDX_C2426287ECF78B0 ON module');
        $this->addSql('ALTER TABLE module DROP type, DROP contenu, DROP cours_id, CHANGE name name VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE date_creation date_creation DATE NOT NULL, CHANGE level level VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user DROP password');
    }
}
