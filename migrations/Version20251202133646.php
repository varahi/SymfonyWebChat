<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251202133646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client_session (id INT AUTO_INCREMENT NOT NULL, phone VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_B6BD307FCD53EDB6`');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_B6BD307FF624B39D`');
        $this->addSql('DROP INDEX IDX_B6BD307FCD53EDB6 ON message');
        $this->addSql('DROP INDEX IDX_B6BD307FF624B39D ON message');
        $this->addSql('ALTER TABLE message ADD role VARCHAR(255) NOT NULL, ADD client_session_id INT DEFAULT NULL, ADD operator_id INT DEFAULT NULL, DROP sender_id, DROP receiver_id');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF16BD19E FOREIGN KEY (client_session_id) REFERENCES client_session (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F584598A3 FOREIGN KEY (operator_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FF16BD19E ON message (client_session_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307F584598A3 ON message (operator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE client_session');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF16BD19E');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F584598A3');
        $this->addSql('DROP INDEX IDX_B6BD307FF16BD19E ON message');
        $this->addSql('DROP INDEX IDX_B6BD307F584598A3 ON message');
        $this->addSql('ALTER TABLE message ADD sender_id INT DEFAULT NULL, ADD receiver_id INT DEFAULT NULL, DROP role, DROP client_session_id, DROP operator_id');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_B6BD307FCD53EDB6` FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_B6BD307FF624B39D` FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FCD53EDB6 ON message (receiver_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FF624B39D ON message (sender_id)');
    }
}
