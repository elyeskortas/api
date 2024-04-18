<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418101451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE item_page (id INT AUTO_INCREMENT NOT NULL, page_id INT DEFAULT NULL, data LONGTEXT DEFAULT NULL, INDEX IDX_DC3AF2DBC4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE item_page ADD CONSTRAINT FK_DC3AF2DBC4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
        $this->addSql('ALTER TABLE page DROP data');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_page DROP FOREIGN KEY FK_DC3AF2DBC4663E4');
        $this->addSql('DROP TABLE item_page');
        $this->addSql('ALTER TABLE page ADD data LONGTEXT DEFAULT NULL');
    }
}
