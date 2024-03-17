<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240317124439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates all necessary tables to store data about currency rates';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE currency (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(3) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exchange_rate (id INT AUTO_INCREMENT NOT NULL, base_currency VARCHAR(3) NOT NULL, target_currency VARCHAR(3) NOT NULL, rate NUMERIC(20, 6) NOT NULL, fetched_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addInitialCurrencies();
    }

    public function addInitialCurrencies()
    {
        $this->addSql("INSERT INTO currency (code) VALUES ('EUR')");
        $this->addSql("INSERT INTO currency (code) VALUES ('GBP')");
        $this->addSql("INSERT INTO currency (code) VALUES ('USD')");
        $this->addSql("INSERT INTO currency (code) VALUES ('AUD')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP TABLE exchange_rate');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
