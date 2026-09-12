<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260101000000 extends AbstractMigration {
	public function getDescription(): string {
		return 'Initiales Schema: users, roles, actions, Zuordnungstabellen, issues';
	}

	public function up(Schema $schema): void {
		$this->addSql('CREATE TABLE users (
			id VARCHAR(36) PRIMARY KEY,
			email VARCHAR(190) UNIQUE NOT NULL,
			display_name VARCHAR(190) NOT NULL,
			password_hash VARCHAR(255) NOT NULL,
			active BOOLEAN NOT NULL DEFAULT TRUE
		)');

		$this->addSql('CREATE TABLE roles (
			id VARCHAR(36) PRIMARY KEY,
			name VARCHAR(100) UNIQUE NOT NULL
		)');

		$this->addSql('CREATE TABLE actions (
			id VARCHAR(36) PRIMARY KEY,
			"key" VARCHAR(150) UNIQUE NOT NULL,
			description VARCHAR(255)
		)');

		$this->addSql('CREATE TABLE user_role (
			user_id VARCHAR(36) NOT NULL REFERENCES users(id) ON DELETE CASCADE,
			role_id VARCHAR(36) NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
			PRIMARY KEY (user_id, role_id)
		)');

		$this->addSql('CREATE TABLE role_action (
			role_id VARCHAR(36) NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
			action_id VARCHAR(36) NOT NULL REFERENCES actions(id) ON DELETE CASCADE,
			PRIMARY KEY (role_id, action_id)
		)');

		$this->addSql('CREATE TABLE issues (
			id VARCHAR(36) PRIMARY KEY,
			title VARCHAR(200) NOT NULL,
			description TEXT NOT NULL,
			status VARCHAR(30) NOT NULL,
			reporter_id VARCHAR(36) NOT NULL REFERENCES users(id),
			assignee_id VARCHAR(36) REFERENCES users(id),
			created_at TIMESTAMP NOT NULL,
			updated_at TIMESTAMP NOT NULL
		)');
	}

	public function down(Schema $schema): void {
		$this->addSql('DROP TABLE issues');
		$this->addSql('DROP TABLE role_action');
		$this->addSql('DROP TABLE user_role');
		$this->addSql('DROP TABLE actions');
		$this->addSql('DROP TABLE roles');
		$this->addSql('DROP TABLE users');
	}
}
