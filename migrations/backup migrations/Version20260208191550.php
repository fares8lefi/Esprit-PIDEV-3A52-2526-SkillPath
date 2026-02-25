<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208191550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE question (id_question INT AUTO_INCREMENT NOT NULL, enonce LONGTEXT NOT NULL, choix_a VARCHAR(255) NOT NULL, choix_b VARCHAR(255) NOT NULL, choix_c VARCHAR(255) NOT NULL, choix_d VARCHAR(255) NOT NULL, bonne_reponse VARCHAR(1) NOT NULL, points INT NOT NULL, id_quiz INT NOT NULL, INDEX IDX_B6F7494E2F32E690 (id_quiz), PRIMARY KEY (id_question)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz (id_quiz INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, duree INT NOT NULL, note_max INT NOT NULL, date_creation DATETIME NOT NULL, id_cours INT NOT NULL, UNIQUE INDEX UNIQ_A412FA92134FCDAC (id_cours), PRIMARY KEY (id_quiz)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE resultat (id_resultat INT AUTO_INCREMENT NOT NULL, score INT NOT NULL, note_max INT NOT NULL, date_passage DATETIME NOT NULL, id_quiz INT NOT NULL, id_etudiant INT NOT NULL, INDEX IDX_E7DB5DE22F32E690 (id_quiz), INDEX IDX_E7DB5DE221A5CE76 (id_etudiant), PRIMARY KEY (id_resultat)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E2F32E690 FOREIGN KEY (id_quiz) REFERENCES quiz (id_quiz)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92134FCDAC FOREIGN KEY (id_cours) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE22F32E690 FOREIGN KEY (id_quiz) REFERENCES quiz (id_quiz)');
        $this->addSql('ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE221A5CE76 FOREIGN KEY (id_etudiant) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E2F32E690');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92134FCDAC');
        $this->addSql('ALTER TABLE resultat DROP FOREIGN KEY FK_E7DB5DE22F32E690');
        $this->addSql('ALTER TABLE resultat DROP FOREIGN KEY FK_E7DB5DE221A5CE76');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE resultat');
    }
}
