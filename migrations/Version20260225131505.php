<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225131505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE certificate (id INT AUTO_INCREMENT NOT NULL, certificate_number VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, cours_id INT NOT NULL, UNIQUE INDEX UNIQ_219CDA4A3005EFE3 (certificate_number), INDEX IDX_219CDA4AA76ED395 (user_id), INDEX IDX_219CDA4A7ECF78B0 (cours_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, is_read TINYINT NOT NULL, user_id INT NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_completed_modules (user_id INT NOT NULL, module_id INT NOT NULL, INDEX IDX_91A41398A76ED395 (user_id), INDEX IDX_91A41398AFC2B591 (module_id), PRIMARY KEY (user_id, module_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A7ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_completed_modules ADD CONSTRAINT FK_91A41398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_completed_modules ADD CONSTRAINT FK_91A41398AFC2B591 FOREIGN KEY (module_id) REFERENCES module (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY `FK_3BAE0AA764D218E`');
        $this->addSql('ALTER TABLE user_favorite_events DROP FOREIGN KEY `FK_7DEE573F71F7E88B`');
        $this->addSql('ALTER TABLE user_favorite_events DROP FOREIGN KEY `FK_7DEE573FA76ED395`');
        $this->addSql('ALTER TABLE user_joined_events DROP FOREIGN KEY `FK_1EAA3DB071F7E88B`');
        $this->addSql('ALTER TABLE user_joined_events DROP FOREIGN KEY `FK_1EAA3DB0A76ED395`');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE user_favorite_events');
        $this->addSql('DROP TABLE user_joined_events');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY `FK_C242628591CC992`');
        $this->addSql('ALTER TABLE module CHANGE title name VARCHAR(120) NOT NULL, CHANGE created_at date_creation DATETIME NOT NULL, CHANGE content contenu LONGTEXT DEFAULT NULL, CHANGE updated_at scheduled_at DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX idx_c242628591cc992 ON module');
        $this->addSql('CREATE INDEX IDX_C2426287ECF78B0 ON module (cours_id)');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT `FK_C242628591CC992` FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY `FK_A412FA92591CC992`');
        $this->addSql('DROP INDEX IDX_A412FA92591CC992 ON quiz');
        $this->addSql('ALTER TABLE quiz ADD id_cours INT NOT NULL, DROP cours_id');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92134FCDAC FOREIGN KEY (id_cours) REFERENCES cours (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A412FA92134FCDAC ON quiz (id_cours)');
        $this->addSql('ALTER TABLE reclamation DROP piece_jointe');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY `FK_5FB6DEC7A76ED395`');
        $this->addSql('DROP INDEX IDX_5FB6DEC7A76ED395 ON reponse');
        $this->addSql('ALTER TABLE reponse DROP user_id');
        $this->addSql('ALTER TABLE user DROP is_verified, DROP verification_code, DROP created_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, event_date DATE NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, image VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, location_id INT DEFAULT NULL, INDEX IDX_3BAE0AA764D218E (location_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, building VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, room_number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, max_capacity INT NOT NULL, image VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_favorite_events (user_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_7DEE573F71F7E88B (event_id), INDEX IDX_7DEE573FA76ED395 (user_id), PRIMARY KEY (user_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_joined_events (user_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_1EAA3DB0A76ED395 (user_id), INDEX IDX_1EAA3DB071F7E88B (event_id), PRIMARY KEY (user_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT `FK_3BAE0AA764D218E` FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE user_favorite_events ADD CONSTRAINT `FK_7DEE573F71F7E88B` FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorite_events ADD CONSTRAINT `FK_7DEE573FA76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_joined_events ADD CONSTRAINT `FK_1EAA3DB071F7E88B` FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_joined_events ADD CONSTRAINT `FK_1EAA3DB0A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AA76ED395');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A7ECF78B0');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE user_completed_modules DROP FOREIGN KEY FK_91A41398A76ED395');
        $this->addSql('ALTER TABLE user_completed_modules DROP FOREIGN KEY FK_91A41398AFC2B591');
        $this->addSql('DROP TABLE certificate');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE user_completed_modules');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY FK_C2426287ECF78B0');
        $this->addSql('ALTER TABLE module CHANGE name title VARCHAR(120) NOT NULL, CHANGE date_creation created_at DATETIME NOT NULL, CHANGE contenu content LONGTEXT DEFAULT NULL, CHANGE scheduled_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX idx_c2426287ecf78b0 ON module');
        $this->addSql('CREATE INDEX IDX_C242628591CC992 ON module (cours_id)');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT FK_C2426287ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92134FCDAC');
        $this->addSql('DROP INDEX UNIQ_A412FA92134FCDAC ON quiz');
        $this->addSql('ALTER TABLE quiz ADD cours_id INT DEFAULT NULL, DROP id_cours');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT `FK_A412FA92591CC992` FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('CREATE INDEX IDX_A412FA92591CC992 ON quiz (cours_id)');
        $this->addSql('ALTER TABLE reclamation ADD piece_jointe VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reponse ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT `FK_5FB6DEC7A76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5FB6DEC7A76ED395 ON reponse (user_id)');
        $this->addSql('ALTER TABLE user ADD is_verified TINYINT NOT NULL, ADD verification_code VARCHAR(6) DEFAULT NULL, ADD created_at DATETIME NOT NULL');
    }
}
