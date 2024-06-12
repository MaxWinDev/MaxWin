<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240610184436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE win (id INT AUTO_INCREMENT NOT NULL, user_id BIGINT NOT NULL, bet NUMERIC(10, 2) NOT NULL, amount NUMERIC(10, 2) NOT NULL, machine_name VARCHAR(255) NOT NULL, INDEX IDX_B0CA3B76A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE win ADD CONSTRAINT FK_B0CA3B76A76ED395 FOREIGN KEY (user_id) REFERENCES user (id_utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user CHANGE currency currency NUMERIC(10, 2) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE win DROP FOREIGN KEY FK_B0CA3B76A76ED395');
        $this->addSql('DROP TABLE win');
        $this->addSql('ALTER TABLE user CHANGE currency currency NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL');
    }
}