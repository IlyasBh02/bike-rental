-- Insérer un client de test (mot de passe: customer123)
WITH new_user AS (
    INSERT INTO users (email, password, first_name, last_name, role, phone, address)
    VALUES (
        'customer@bikerental.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'John',
        'Doe',
        'customer',
        '0123456789',
        '123 rue Example'
    )
    ON CONFLICT (email) DO NOTHING
    RETURNING id
)
INSERT INTO customers (user_id, subscription_type)
SELECT id, 'occasional'
FROM new_user
ON CONFLICT (user_id) DO NOTHING; 