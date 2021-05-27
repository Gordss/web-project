CREATE DATABASE IF NOT EXISTS web_project;

CREATE TABLE IF NOT EXISTS web_project.users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL,
  password VARCHAR(64) NOT NULL
);

CREATE TABLE IF NOT EXISTS web_project.archives (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  uploaded_at DATETIME NOT NULL DEFAULT (current_timestamp),

  CONSTRAINT `User ID foreign key` FOREIGN KEY(user_id) REFERENCES web_project.users (id)
);

CREATE TABLE IF NOT EXISTS web_project.nodes (
    archive_id      INT NOT NULL,
    name            VARCHAR(256) NOT NULL,
    parent_name     VARCHAR(256),
    content_length  INT,
    type            VARCHAR(64),

    PRIMARY KEY (name,archive_id),
    CONSTRAINT `Parent name self key` FOREIGN KEY (parent_name) REFERENCES web_project.nodes (name)
);
