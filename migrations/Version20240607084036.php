<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240607084036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id_utilisateur BIGINT AUTO_INCREMENT NOT NULL, currency NUMERIC(10, 2) DEFAULT \'0\' NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, PRIMARY KEY(id_utilisateur)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE win (id INT AUTO_INCREMENT NOT NULL, user_id BIGINT NOT NULL, bet NUMERIC(10, 2) NOT NULL, amount NUMERIC(10, 2) NOT NULL, machine_name VARCHAR(255) NOT NULL, INDEX IDX_B0CA3B76A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE win ADD CONSTRAINT FK_B0CA3B76A76ED395 FOREIGN KEY (user_id) REFERENCES user (id_utilisateur) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE win DROP FOREIGN KEY FK_B0CA3B76A76ED395');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE win');
    }
}
