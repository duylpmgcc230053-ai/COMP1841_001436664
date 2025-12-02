CREATE TABLE users (
    user_id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    PASSWORD VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role VARCHAR(50) DEFAULT 'user',
    PRIMARY KEY (user_id),
    UNIQUE KEY uk_username (username),
    UNIQUE KEY uk_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_created (created_at)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

CREATE TABLE modules (
    module_id INT(11) NOT NULL AUTO_INCREMENT,
    module_code VARCHAR(20) NOT NULL,
    module_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (module_id),
    UNIQUE KEY uk_module_code (module_code),
    INDEX idx_modules_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE posts (
    post_id INT(11) NOT NULL AUTO_INCREMENT,
    body TEXT NOT NULL,
    user_id INT(11) NOT NULL,
    module_id INT(11) NOT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (post_id),
    INDEX idx_posts_user (user_id),
    INDEX idx_posts_module (module_id),
    INDEX idx_posts_created (created_at),
    INDEX idx_posts_updated (updated_at),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(module_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE comments (
    comment_id INT(11) NOT NULL AUTO_INCREMENT,
    post_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    body TEXT NOT NULL,
    created_at DATETIME DEFAULT NULL,
    PRIMARY KEY (comment_id),
    INDEX idx_comments_post (post_id),
    INDEX idx_comments_user (user_id),
    INDEX idx_comments_created (created_at),
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;