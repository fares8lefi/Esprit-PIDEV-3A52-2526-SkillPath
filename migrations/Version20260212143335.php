<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212143335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS course (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, level VARCHAR(30) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, category VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS user_course (user_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_73CC7484A76ED395 (user_id), INDEX IDX_73CC7484591CC992 (course_id), PRIMARY KEY (user_id, course_id)) DEFAULT CHARACTER SET utf8mb4');
        // $this->addSql('ALTER TABLE user_course ADD CONSTRAINT FK_73CC7484A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        // $this->addSql('ALTER TABLE user_course ADD CONSTRAINT FK_73CC7484591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        // $this->addSql('ALTER TABLE user_cours DROP FOREIGN KEY `FK_1F0877C47ECF78B0`');
        // $this->addSql('ALTER TABLE user_cours DROP FOREIGN KEY `FK_1F0877C4A76ED395`');
        // $this->addSql('DROP TABLE cours');
        // $this->addSql('DROP TABLE user_cours');
        // $this->addSql('ALTER TABLE module ADD updated_at DATETIME DEFAULT NULL, CHANGE name title VARCHAR(120) NOT NULL, CHANGE date_creation created_at DATETIME NOT NULL, CHANGE contenu content LONGTEXT DEFAULT NULL, CHANGE cours_id course_id INT NOT NULL');
        // $this->addSql('ALTER TABLE module ADD CONSTRAINT FK_C242628591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        // $this->addSql('CREATE INDEX IDX_C242628591CC992 ON module (course_id)');
        // $this->addSql('ALTER TABLE question DROP type');
        // $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY `FK_A412FA927ECF78B0`');
        // $this->addSql('DROP INDEX IDX_A412FA927ECF78B0 ON quiz');
        // $this->addSql('ALTER TABLE quiz CHANGE cours_id course_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('CREATE INDEX IDX_A412FA92591CC992 ON quiz (course_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cours (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, level VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, categorie VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, type VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_cours (user_id INT NOT NULL, cours_id INT NOT NULL, INDEX IDX_1F0877C4A76ED395 (user_id), INDEX IDX_1F0877C47ECF78B0 (cours_id), PRIMARY KEY (user_id, cours_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_cours ADD CONSTRAINT `FK_1F0877C47ECF78B0` FOREIGN KEY (cours_id) REFERENCES cours (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_cours ADD CONSTRAINT `FK_1F0877C4A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_course DROP FOREIGN KEY FK_73CC7484A76ED395');
        $this->addSql('ALTER TABLE user_course DROP FOREIGN KEY FK_73CC7484591CC992');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE user_course');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY FK_C242628591CC992');
        $this->addSql('DROP INDEX IDX_C242628591CC992 ON module');
        $this->addSql('ALTER TABLE module DROP updated_at, CHANGE title name VARCHAR(120) NOT NULL, CHANGE created_at date_creation DATETIME NOT NULL, CHANGE content contenu LONGTEXT DEFAULT NULL, CHANGE course_id cours_id INT NOT NULL');
        $this->addSql('ALTER TABLE question ADD type VARCHAR(255) DEFAULT \'qcm\'');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92591CC992');
        $this->addSql('DROP INDEX IDX_A412FA92591CC992 ON quiz');
        $this->addSql('ALTER TABLE quiz CHANGE course_id cours_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT `FK_A412FA927ECF78B0` FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('CREATE INDEX IDX_A412FA927ECF78B0 ON quiz (cours_id)');
    }
}
