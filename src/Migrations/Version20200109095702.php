<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200109095702 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE activity_log FROM activity_log JOIN user ON activity_log.user_id = user.id WHERE user.username LIKE \'%.student\'');
        $this->addSql('DELETE course_user FROM course_user JOIN user ON course_user.user_id = user.id WHERE user.username LIKE \'%.student\'');
        $this->addSql('UPDATE user SET dummy_student_id = NULL');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6493C531ECB');
        $this->addSql('DELETE FROM user WHERE username LIKE \'%.student\'');

        $this->addSql('ALTER TABLE user DROP type, DROP school_urn, DROP date_of_birth, DROP home_postcode, DROP title, DROP dummy_student_id');
        $this->addSql('ALTER TABLE user ADD last_login_date date DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD type VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, ADD school_urn VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, ADD date_of_birth DATE DEFAULT NULL, ADD home_postcode VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD title VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD dummy_student_id INT DEFAULT NULL');
    }
}
