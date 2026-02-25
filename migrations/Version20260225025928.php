<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225025928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS cours (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, level VARCHAR(30) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, category VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, event_date DATE NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, image VARCHAR(255) NOT NULL, location_id INT DEFAULT NULL, INDEX IDX_3BAE0AA764D218E (location_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS location (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, building VARCHAR(255) DEFAULT NULL, room_number VARCHAR(255) NOT NULL, max_capacity INT NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA764D218E FOREIGN KEY (location_id) REFERENCES location (id)');

        $this->addSql('CREATE TABLE IF NOT EXISTS user_cours (user_id INT NOT NULL, cours_id INT NOT NULL, INDEX IDX_73CC7484A76ED395 (user_id), INDEX IDX_73CC7484591CC992 (cours_id), PRIMARY KEY (user_id, cours_id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('ALTER TABLE module DROP FOREIGN KEY `FK_C2426287ECF78B0`');
        $this->addSql('DROP INDEX IDX_C2426287ECF78B0 ON module');
        $this->addSql('ALTER TABLE module ADD updated_at DATETIME DEFAULT NULL, CHANGE name title VARCHAR(120) NOT NULL, CHANGE date_creation created_at DATETIME NOT NULL, CHANGE contenu content LONGTEXT DEFAULT NULL, CHANGE cours_id cours_id INT NOT NULL');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT FK_C242628591CC992 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('CREATE INDEX IDX_C242628591CC992 ON module (cours_id)');

        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY `FK_A412FA927ECF78B0`');
        $this->addSql('DROP INDEX IDX_A412FA927ECF78B0 ON quiz');
        $this->addSql('ALTER TABLE quiz CHANGE cours_id cours_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92591CC992 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('CREATE INDEX IDX_A412FA92591CC992 ON quiz (cours_id)');

        $this->addSql('ALTER TABLE user_cours DROP FOREIGN KEY `FK_1F0877C47ECF78B0`');
        $this->addSql('ALTER TABLE user_cours DROP  FOREIGN KEY `FK_1F0877C4A76ED395`');
        

        $this->addSql('CREATE TABLE IF NOT EXISTS user_joined_events (user_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_1EAA3DB0A76ED395 (user_id), INDEX IDX_1EAA3DB071F7E88B (event_id), PRIMARY KEY (user_id, event_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS user_favorite_events (user_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_7DEE573FA76ED395 (user_id), INDEX IDX_7DEE573F71F7E88B (event_id), PRIMARY KEY (user_id, event_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_joined_events ADD CONSTRAINT FK_1EAA3DB0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_joined_events ADD CONSTRAINT FK_1EAA3DB071F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorite_events ADD CONSTRAINT FK_7DEE573FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorite_events ADD CONSTRAINT FK_7DEE573F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE reclamation ADD piece_jointe VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_cours ADD CONSTRAINT FK_73CC7484A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_cours ADD CONSTRAINT FK_73CC7484591CC992 FOREIGN KEY (cours_id) REFERENCES cours (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Irreversible consolidation migration.
        // Keeping down() as no-op avoids destructive rollbacks on production data.
    }
}
