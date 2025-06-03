<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603101454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP CONSTRAINT FK_723705D167B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP CONSTRAINT FK_723705D1591CC992
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD CONSTRAINT FK_723705D167B3B43D FOREIGN KEY (users_id) REFERENCES billing_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD CONSTRAINT FK_723705D1591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP CONSTRAINT fk_723705d167b3b43d
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP CONSTRAINT fk_723705d1591cc992
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD CONSTRAINT fk_723705d167b3b43d FOREIGN KEY (users_id) REFERENCES billing_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD CONSTRAINT fk_723705d1591cc992 FOREIGN KEY (course_id) REFERENCES course (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }
}
