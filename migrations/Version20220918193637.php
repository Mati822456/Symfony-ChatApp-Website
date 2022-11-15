<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220918193637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, incoming_msg_id INT NOT NULL, outgoing_msg_id INT NOT NULL, msg LONGTEXT NOT NULL, created datetime NOT NULL, status SMALLINT NOT NULL, INDEX IDX_B6BD307F556F0567 (incoming_msg_id), INDEX IDX_B6BD307FC90BB9A9 (outgoing_msg_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F556F0567 FOREIGN KEY (incoming_msg_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FC90BB9A9 FOREIGN KEY (outgoing_msg_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F556F0567');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FC90BB9A9');
        $this->addSql('DROP TABLE message');
    }
}
