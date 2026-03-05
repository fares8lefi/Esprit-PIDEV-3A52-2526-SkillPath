<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260226001334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE user_course');
        $this->addSql('DROP TABLE user_favorite_events');
        $this->addSql('DROP TABLE user_joined_events');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY `FK_C242628591CC992`');
        $this->addSql('DROP INDEX IDX_C242628591CC992 ON module');
        $this->addSql('ALTER TABLE module ADD name VARCHAR(255) NOT NULL, ADD date_creation DATE NOT NULL, DROP title, DROP created_at, DROP updated_at, DROP type, DROP content, DROP course_id, CHANGE description description VARCHAR(255) NOT NULL, CHANGE level level VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY `FK_A412FA92591CC992`');
        $this->addSql('DROP INDEX IDX_A412FA92591CC992 ON quiz');
        $this->addSql('ALTER TABLE quiz ADD id_cours INT NOT NULL, DROP course_id');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92134FCDAC FOREIGN KEY (id_cours) REFERENCES cours (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A412FA92134FCDAC ON quiz (id_cours)');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, level VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, category VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, event_date DATE NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, image VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, location_id INT DEFAULT NULL, INDEX IDX_3BAE0AA764D218E (location_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, building VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, room_number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, max_capacity INT NOT NULL, image VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_course (user_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_73CC7484A76ED395 (user_id), INDEX IDX_73CC7484591CC992 (course_id), PRIMARY KEY (user_id, course_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_favorite_events (user_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_7DEE573FA76ED395 (user_id), INDEX IDX_7DEE573F71F7E88B (event_id), PRIMARY KEY (user_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_joined_events (user_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_1EAA3DB071F7E88B (event_id), INDEX IDX_1EAA3DB0A76ED395 (user_id), PRIMARY KEY (user_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE module ADD title VARCHAR(120) NOT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD type VARCHAR(50) DEFAULT NULL, ADD content LONGTEXT DEFAULT NULL, ADD course_id INT NOT NULL, DROP name, DROP date_creation, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE level level VARCHAR(30) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT `FK_C242628591CC992` FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('CREATE INDEX IDX_C242628591CC992 ON module (course_id)');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92134FCDAC');
        $this->addSql('DROP INDEX UNIQ_A412FA92134FCDAC ON quiz');
        $this->addSql('ALTER TABLE quiz ADD course_id INT DEFAULT NULL, DROP id_cours');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT `FK_A412FA92591CC992` FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('CREATE INDEX IDX_A412FA92591CC992 ON quiz (course_id)');
    }
}
