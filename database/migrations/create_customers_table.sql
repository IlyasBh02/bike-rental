CREATE TABLE IF NOT EXISTS customers (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL UNIQUE,
    subscription_type VARCHAR(20) CHECK (subscription_type IN ('occasional', 'monthly', 'premium')) NOT NULL DEFAULT 'occasional',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
); 