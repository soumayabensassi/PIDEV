<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220220175052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rdv DROP FOREIGN KEY FK_10C31F863C105691');
        $this->addSql('ALTER TABLE rdv DROP FOREIGN KEY FK_10C31F86A76ED395');
        $this->addSql('DROP INDEX UNIQ_10C31F86A76ED395 ON rdv');
        $this->addSql('DROP INDEX UNIQ_10C31F863C105691 ON rdv');
        $this->addSql('ALTER TABLE rdv ADD coachs_id INT DEFAULT NULL, ADD users_id INT DEFAULT NULL, DROP coach_id, DROP user_id');
        $this->addSql('ALTER TABLE rdv ADD CONSTRAINT FK_10C31F8660450EA1 FOREIGN KEY (coachs_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE rdv ADD CONSTRAINT FK_10C31F8667B3B43D FOREIGN KEY (users_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_10C31F8660450EA1 ON rdv (coachs_id)');
        $this->addSql('CREATE INDEX IDX_10C31F8667B3B43D ON rdv (users_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profil CHANGE description description VARCHAR(1000) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE username username VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE game game VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE rdv DROP FOREIGN KEY FK_10C31F8660450EA1');
        $this->addSql('ALTER TABLE rdv DROP FOREIGN KEY FK_10C31F8667B3B43D');
        $this->addSql('DROP INDEX IDX_10C31F8660450EA1 ON rdv');
        $this->addSql('DROP INDEX IDX_10C31F8667B3B43D ON rdv');
        $this->addSql('ALTER TABLE rdv ADD coach_id INT DEFAULT NULL, ADD user_id INT DEFAULT NULL, DROP coachs_id, DROP users_id');
        $this->addSql('ALTER TABLE rdv ADD CONSTRAINT FK_10C31F863C105691 FOREIGN KEY (coach_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE rdv ADD CONSTRAINT FK_10C31F86A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_10C31F86A76ED395 ON rdv (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_10C31F863C105691 ON rdv (coach_id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE username username VARCHAR(50) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE password password VARCHAR(50) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE verifpassword verifpassword VARCHAR(50) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(50) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE fullname fullname VARCHAR(50) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE specialite specialite VARCHAR(50) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}