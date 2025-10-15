<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015123415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire ADD sortie_id INT NOT NULL, ADD date_publication DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCCC72D953 FOREIGN KEY (sortie_id) REFERENCES sortie (id)');
        $this->addSql('CREATE INDEX IDX_67F068BCCC72D953 ON commentaire (sortie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCCC72D953');
        $this->addSql('DROP INDEX IDX_67F068BCCC72D953 ON commentaire');
        $this->addSql('ALTER TABLE commentaire DROP sortie_id, DROP date_publication');
    }
}
