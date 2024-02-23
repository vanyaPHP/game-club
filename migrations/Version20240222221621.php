<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240222221621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking_request ADD computer_id INT NOT NULL');
        $this->addSql('ALTER TABLE booking_request ADD CONSTRAINT FK_6129CABFA426D518 FOREIGN KEY (computer_id) REFERENCES computer (id)');
        $this->addSql('CREATE INDEX IDX_6129CABFA426D518 ON booking_request (computer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking_request DROP FOREIGN KEY FK_6129CABFA426D518');
        $this->addSql('DROP INDEX IDX_6129CABFA426D518 ON booking_request');
        $this->addSql('ALTER TABLE booking_request DROP computer_id');
    }
}
