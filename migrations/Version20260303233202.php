<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303233202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // 1. Rename table to plural (users)
        $this->addSql('RENAME TABLE `user` TO users');
        
        // 2. Update all referencing tables
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY `FK_219CDA4AA76ED395`');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY `FK_169E6FB9896DBBDE`');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY `FK_169E6FB9B03A8386`');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id)');
        
        $this->addSql('ALTER TABLE event_rating DROP FOREIGN KEY `FK_EA105170A76ED395`');
        $this->addSql('ALTER TABLE event_rating ADD CONSTRAINT FK_EA105170A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY `FK_C242628896DBBDE`');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY `FK_C242628B03A8386`');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT FK_C242628896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT FK_C242628B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id)');
        
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `FK_BF5476CA896DBBDE`');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `FK_BF5476CAA76ED395`');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `FK_BF5476CAB03A8386`');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id)');
        
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY `FK_CE606404A76ED395`');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY `FK_5FB6DEC7A76ED395`');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        
        $this->addSql('ALTER TABLE resultat DROP FOREIGN KEY `FK_E7DB5DE2DDEAB1A3`');
        $this->addSql('ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE2DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES users (id) ON DELETE CASCADE');
        
        $this->addSql('ALTER TABLE user_course_view DROP FOREIGN KEY `FK_9F44BF1DA76ED395`');
        $this->addSql('ALTER TABLE user_course_view ADD CONSTRAINT FK_9F44BF1DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        
        $this->addSql('ALTER TABLE user_course DROP FOREIGN KEY `FK_73CC7484A76ED395`');
        $this->addSql('ALTER TABLE user_course ADD CONSTRAINT FK_73CC7484A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        
        $this->addSql('ALTER TABLE user_joined_events DROP FOREIGN KEY `FK_1EAA3DB0A76ED395`');
        $this->addSql('ALTER TABLE user_joined_events ADD CONSTRAINT FK_1EAA3DB0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        
        $this->addSql('ALTER TABLE user_favorite_events DROP FOREIGN KEY `FK_7DEE573FA76ED395`');
        $this->addSql('ALTER TABLE user_favorite_events ADD CONSTRAINT FK_7DEE573FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id BINARY(16) NOT NULL, username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, role VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, is_verified TINYINT NOT NULL, verification_code VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, domaine VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, style_dapprentissage VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, niveau VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, updated_at DATETIME DEFAULT NULL, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_by_id BINARY(16) NOT NULL, updated_by_id BINARY(16) DEFAULT NULL, INDEX IDX_8D93D649B03A8386 (created_by_id), INDEX IDX_8D93D649896DBBDE (updated_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT `FK_1483A5E9896DBBDE` FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT `FK_1483A5E9B03A8386` FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9B03A8386');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9896DBBDE');
        $this->addSql('DROP TABLE users');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AA76ED395');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT `FK_219CDA4AA76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9B03A8386');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9896DBBDE');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT `FK_169E6FB9B03A8386` FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT `FK_169E6FB9896DBBDE` FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event_rating DROP FOREIGN KEY FK_EA105170A76ED395');
        $this->addSql('ALTER TABLE event_rating ADD CONSTRAINT `FK_EA105170A76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY FK_C242628B03A8386');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY FK_C242628896DBBDE');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT `FK_C242628B03A8386` FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT `FK_C242628896DBBDE` FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAB03A8386');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA896DBBDE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `FK_BF5476CAA76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `FK_BF5476CAB03A8386` FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `FK_BF5476CA896DBBDE` FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404A76ED395');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT `FK_CE606404A76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7A76ED395');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT `FK_5FB6DEC7A76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE resultat DROP FOREIGN KEY FK_E7DB5DE2DDEAB1A3');
        $this->addSql('ALTER TABLE resultat ADD CONSTRAINT `FK_E7DB5DE2DDEAB1A3` FOREIGN KEY (etudiant_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_course DROP FOREIGN KEY FK_73CC7484A76ED395');
        $this->addSql('ALTER TABLE user_course ADD CONSTRAINT `FK_73CC7484A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_course_view DROP FOREIGN KEY FK_9F44BF1DA76ED395');
        $this->addSql('ALTER TABLE user_course_view ADD CONSTRAINT `FK_9F44BF1DA76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_favorite_events DROP FOREIGN KEY FK_7DEE573FA76ED395');
        $this->addSql('ALTER TABLE user_favorite_events ADD CONSTRAINT `FK_7DEE573FA76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_joined_events DROP FOREIGN KEY FK_1EAA3DB0A76ED395');
        $this->addSql('ALTER TABLE user_joined_events ADD CONSTRAINT `FK_1EAA3DB0A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }
}
