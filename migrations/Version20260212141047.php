<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212141047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, event_date DATE NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, image VARCHAR(255) NOT NULL, location_id INT DEFAULT NULL, INDEX IDX_3BAE0AA764D218E (location_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        // $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, building VARCHAR(255) DEFAULT NULL, room_number VARCHAR(255) NOT NULL, max_capacity INT NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        // $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA764D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        // $this->addSql('ALTER TABLE cours DROP FOREIGN KEY `FK_FDCA8C9CAFC2B591`');
        // $this->addSql('DROP INDEX IDX_FDCA8C9CAFC2B591 ON cours');
        // $this->addSql('ALTER TABLE cours ADD description LONGTEXT DEFAULT NULL, ADD level VARCHAR(30) DEFAULT NULL, ADD image VARCHAR(255) DEFAULT NULL, ADD categorie VARCHAR(50) DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, DROP contenu, DROP type, DROP module_id');
        $this->addSql('ALTER TABLE module ADD type VARCHAR(50) DEFAULT NULL, ADD contenu LONGTEXT DEFAULT NULL, ADD cours_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT FK_C2426287ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('CREATE INDEX IDX_C2426287ECF78B0 ON module (cours_id)');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY `FK_A412FA92134FCDAC`');
        $this->addSql('DROP INDEX UNIQ_A412FA92134FCDAC ON quiz');
        $this->addSql('ALTER TABLE quiz ADD cours_id INT DEFAULT NULL, DROP id_cours');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA927ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('CREATE INDEX IDX_A412FA927ECF78B0 ON quiz (cours_id)');
        // $this->addSql('ALTER TABLE reponse ADD user_id INT NOT NULL');
        // $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        // $this->addSql('CREATE INDEX IDX_5FB6DEC7A76ED395 ON reponse (user_id)');
        // $this->addSql('ALTER TABLE user ADD created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA764D218E');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE location');
        $this->addSql('ALTER TABLE cours ADD contenu VARCHAR(255) NOT NULL, ADD type VARCHAR(255) NOT NULL, ADD module_id INT DEFAULT NULL, DROP description, DROP level, DROP image, DROP categorie, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT `FK_FDCA8C9CAFC2B591` FOREIGN KEY (module_id) REFERENCES module (id)');
        $this->addSql('CREATE INDEX IDX_FDCA8C9CAFC2B591 ON cours (module_id)');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY FK_C2426287ECF78B0');
        $this->addSql('DROP INDEX IDX_C2426287ECF78B0 ON module');
        $this->addSql('ALTER TABLE module DROP type, DROP contenu, DROP cours_id, CHANGE name name VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE date_creation date_creation DATE NOT NULL, CHANGE level level VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA927ECF78B0');
        $this->addSql('DROP INDEX IDX_A412FA927ECF78B0 ON quiz');
        $this->addSql('ALTER TABLE quiz ADD id_cours INT NOT NULL, DROP cours_id');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT `FK_A412FA92134FCDAC` FOREIGN KEY (id_cours) REFERENCES cours (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A412FA92134FCDAC ON quiz (id_cours)');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7A76ED395');
        $this->addSql('DROP INDEX IDX_5FB6DEC7A76ED395 ON reponse');
        $this->addSql('ALTER TABLE reponse DROP user_id');
        $this->addSql('ALTER TABLE user DROP created_at');
    }
}
