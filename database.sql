CREATE TABLE IF NOT EXISTS bikes (
    id SERIAL PRIMARY KEY,
    type VARCHAR(20) CHECK (type IN ('classic', 'electric', 'cargo')) NOT NULL DEFAULT 'classic',
    status VARCHAR(20) CHECK (status IN ('available', 'maintenance', 'rented')) NOT NULL DEFAULT 'available',
    description TEXT,
    hourly_rate NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE IF NOT EXISTS customers (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    subscription_type VARCHAR(20) CHECK (subscription_type IN ('occasional', 'monthly', 'premium')) NOT NULL DEFAULT 'occasional',
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rentals (
    id SERIAL PRIMARY KEY,
    bike_id INT NOT NULL REFERENCES bikes(id),
    customer_id INT NOT NULL REFERENCES customers(id),
    start_time TIMESTAMP NOT NULL,
    planned_end_time TIMESTAMP,
    actual_end_time TIMESTAMP,
    total_amount NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    penalty_amount NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(20) CHECK (status IN ('active', 'completed', 'cancelled')) NOT NULL DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reservations (
    id SERIAL PRIMARY KEY,
    customer_id INT NOT NULL REFERENCES customers(id),
    bike_id INT NOT NULL REFERENCES bikes(id),
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    status VARCHAR(20) CHECK (status IN ('pending', 'confirmed', 'cancelled', 'expired')) NOT NULL DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 

-- Insérer un admin par défaut (mot de passe: admin123)
INSERT INTO users (email, password, first_name, last_name, role)
VALUES (
    'admin@bikerental.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin',
    'System',
    'admin'
) ON CONFLICT (email) DO NOTHING;