<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260101000200 extends AbstractMigration {
	public function getDescription(): string {
		return 'Issue-Status als eigene Tabelle statt hardcodierter String-Liste';
	}

	public function up(Schema $schema): void {
		$this->addSql('CREATE TABLE issue_states (
			id VARCHAR(36) PRIMARY KEY,
			"key" VARCHAR(50) UNIQUE NOT NULL,
			sort_order INT NOT NULL
		)');

		// Bestehende drei Stati als Datensaetze anlegen (gen_random_uuid() ist
		// seit PostgreSQL 13 nativ verfuegbar, keine Extension noetig)
		$this->addSql("INSERT INTO issue_states (id, \"key\", sort_order) VALUES
			(gen_random_uuid()::text, 'open', 1),
			(gen_random_uuid()::text, 'in_progress', 2),
			(gen_random_uuid()::text, 'closed', 3)
		");

		// Neue FK-Spalte ergaenzen, aus den bestehenden String-Werten befuellen
		$this->addSql('ALTER TABLE issues ADD COLUMN status_id VARCHAR(36)');
		$this->addSql('UPDATE issues SET status_id = (SELECT id FROM issue_states WHERE "key" = issues.status)');
		$this->addSql('ALTER TABLE issues ALTER COLUMN status_id SET NOT NULL');
		$this->addSql('ALTER TABLE issues ADD CONSTRAINT fk_issues_status FOREIGN KEY (status_id) REFERENCES issue_states(id)');

		// Alte String-Spalte entfernen
		$this->addSql('ALTER TABLE issues DROP COLUMN status');

		$this->addSql('CREATE TABLE notifications (
			id VARCHAR(36) PRIMARY KEY,
			recipient_id VARCHAR(36) NOT NULL REFERENCES users(id) ON DELETE CASCADE,
			message VARCHAR(500) NOT NULL,
			type VARCHAR(30) NOT NULL,
			"read" BOOLEAN NOT NULL DEFAULT FALSE,
			created_at TIMESTAMP NOT NULL
		)');
		$this->addSql('CREATE INDEX idx_notifications_recipient ON notifications (recipient_id, "read")');


	}

	public function down(Schema $schema): void {
		$this->addSql('ALTER TABLE issues ADD COLUMN status VARCHAR(30)');
		$this->addSql('UPDATE issues SET status = (SELECT "key" FROM issue_states WHERE id = issues.status_id)');
		$this->addSql('ALTER TABLE issues ALTER COLUMN status SET NOT NULL');
		$this->addSql('ALTER TABLE issues DROP CONSTRAINT fk_issues_status');
		$this->addSql('ALTER TABLE issues DROP COLUMN status_id');

		$this->addSql('DROP TABLE issue_states');
		$this->addSql('DROP TABLE notifications');
		$this->addSql('DROP INDEX idx_notifications_recipient');
	}
}