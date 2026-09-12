<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260101000100 extends AbstractMigration {

	public function getDescription(): string {
		return 'Neue Spalte "number" in Tabelle "issues"';
	}

	public function up(Schema $schema): void {
		$this->addSql('CREATE SEQUENCE issue_number_seq');
		$this->addSql('ALTER TABLE issues ADD COLUMN number INT NOT NULL DEFAULT nextval(\'issue_number_seq\')');
	}

	public function down(Schema $schema): void {
		$this->addSql('ALTER TABLE issues DROP COLUMN number');
		$this->addSql('DROP SEQUENCE issue_number_seq');
	}

}