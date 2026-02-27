<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227034508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_rating (id INT AUTO_INCREMENT NOT NULL, score INT NOT NULL, event_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_EA10517071F7E88B (event_id), INDEX IDX_EA105170A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE event_rating ADD CONSTRAINT FK_EA10517071F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event_rating ADD CONSTRAINT FK_EA105170A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event ADD average_rating DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_rating DROP FOREIGN KEY FK_EA10517071F7E88B');
        $this->addSql('ALTER TABLE event_rating DROP FOREIGN KEY FK_EA105170A76ED395');
        $this->addSql('DROP TABLE event_rating');
        $this->addSql('ALTER TABLE event DROP average_rating');
    }
}
