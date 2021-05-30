CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password VARCHAR(64) NOT NULL
);

CREATE TABLE IF NOT EXISTS archives (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  uploaded_at DATETIME NOT NULL DEFAULT current_timestamp,
  FOREIGN KEY (user_id)
    REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS nodes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  archive_id      INT NOT NULL,
  name            VARCHAR(2048),
  parent_name     VARCHAR(2048),
  content_length  INT,
  type            VARCHAR(64),
  md5_sum         VARCHAR(32),
  FOREIGN KEY (archive_id)
      REFERENCES archives(id)
        ON DELETE CASCADE
);
