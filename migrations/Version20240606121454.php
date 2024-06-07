<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240606121454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, user_email VARCHAR(255) NOT NULL, pickup_first_name VARCHAR(255) NOT NULL, pickup_last_name VARCHAR(255) NOT NULL, pickup_date DATE NOT NULL, pickup_time TIME NOT NULL, ordered_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, total_amount DOUBLE PRECISION NOT NULL, INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_detail (id INT AUTO_INCREMENT NOT NULL, the_order_id INT NOT NULL, product_id INT DEFAULT NULL, product_name VARCHAR(255) NOT NULL, product_price DOUBLE PRECISION NOT NULL, product_quantity INT NOT NULL, total_amount DOUBLE PRECISION NOT NULL, INDEX IDX_ED896F46C416F85B (the_order_id), INDEX IDX_ED896F464584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F46C416F85B FOREIGN KEY (the_order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F464584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F46C416F85B');
        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F464584665A');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_detail');
    }
}
