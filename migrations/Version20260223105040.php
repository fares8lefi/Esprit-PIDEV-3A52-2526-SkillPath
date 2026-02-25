<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223105040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_course_view (id INT AUTO_INCREMENT NOT NULL, time_spent INT NOT NULL, quiz_score DOUBLE PRECISION DEFAULT NULL, engagement_level VARCHAR(20) DEFAULT NULL, is_enrolled TINYINT NOT NULL, user_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_9F44BF1DA76ED395 (user_id), INDEX IDX_9F44BF1D591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_course_view ADD CONSTRAINT FK_9F44BF1DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_course_view ADD CONSTRAINT FK_9F44BF1D591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE course ADD duration INT DEFAULT NULL, ADD price DOUBLE PRECISION DEFAULT NULL, ADD rating DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation DROP piece_jointe');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_course_view DROP FOREIGN KEY FK_9F44BF1DA76ED395');
        $this->addSql('ALTER TABLE user_course_view DROP FOREIGN KEY FK_9F44BF1D591CC992');
        $this->addSql('DROP TABLE user_course_view');
        $this->addSql('ALTER TABLE course DROP duration, DROP price, DROP rating');
        $this->addSql('ALTER TABLE reclamation ADD piece_jointe VARCHAR(255) DEFAULT NULL');
    }
}
