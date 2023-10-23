<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231023085521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ingredient (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, quantity INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ingredient_pizza (ingredient_id INT NOT NULL, pizza_id INT NOT NULL, INDEX IDX_D6EFE5AE933FE08C (ingredient_id), INDEX IDX_D6EFE5AED41D1D42 (pizza_id), PRIMARY KEY(ingredient_id, pizza_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ingredient_pizza ADD CONSTRAINT FK_D6EFE5AE933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient_pizza ADD CONSTRAINT FK_D6EFE5AED41D1D42 FOREIGN KEY (pizza_id) REFERENCES pizza (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ingredient_pizza DROP FOREIGN KEY FK_D6EFE5AE933FE08C');
        $this->addSql('ALTER TABLE ingredient_pizza DROP FOREIGN KEY FK_D6EFE5AED41D1D42');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('DROP TABLE ingredient_pizza');
    }
}
