-- Insérer un admin par défaut (mot de passe: admin123)
INSERT INTO users (email, password, first_name, last_name, role)
VALUES (
    'admin@bikerental.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin',
    'System',
    'admin'
) ON CONFLICT (email) DO NOTHING; 