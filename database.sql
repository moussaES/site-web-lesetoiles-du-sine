-- ============================================
-- BASE DE DONNÉES - AGENCE IMMOBILIÈRE
-- ============================================

CREATE DATABASE IF NOT EXISTS agence_immo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agence_immo;

-- Table biens (avec photos intégrées)
CREATE TABLE IF NOT EXISTS biens (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    titre           VARCHAR(255) NOT NULL,
    type            ENUM('maison', 'immeuble', 'terrain') NOT NULL,
    transaction     ENUM('vente', 'location') NOT NULL,
    prix            DECIMAL(15,2) NOT NULL,
    superficie      DECIMAL(10,2),
    nb_pieces       INT DEFAULT NULL,
    description     TEXT,
    localisation    VARCHAR(255),
    ville           VARCHAR(100),
    statut          ENUM('disponible', 'reserve', 'vendu') DEFAULT 'disponible',
    photo1          VARCHAR(255) DEFAULT NULL,
    photo2          VARCHAR(255) DEFAULT NULL,
    photo3          VARCHAR(255) DEFAULT NULL,
    photo4          VARCHAR(255) DEFAULT NULL,
    photo5          VARCHAR(255) DEFAULT NULL,
    photo6          VARCHAR(255) DEFAULT NULL,
    photo7          VARCHAR(255) DEFAULT NULL,
    photo8          VARCHAR(255) DEFAULT NULL,
    photo9          VARCHAR(255) DEFAULT NULL,
    photo10         VARCHAR(255) DEFAULT NULL,
    date_ajout      DATETIME DEFAULT CURRENT_TIMESTAMP,
    admin_id        INT
) ENGINE=InnoDB;

-- Table utilisateurs (clients + admins)
CREATE TABLE IF NOT EXISTS utilisateurs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe    VARCHAR(255) NOT NULL,
    telephone       VARCHAR(20) DEFAULT NULL,
    role            ENUM('client', 'gestionnaire', 'admin') DEFAULT 'client',
    actif           TINYINT(1) DEFAULT 1,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table demandes de contact/visite
CREATE TABLE IF NOT EXISTS demandes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    bien_id         INT NOT NULL,
    client_id       INT DEFAULT NULL,
    nom_visiteur    VARCHAR(150) NOT NULL,
    email_visiteur  VARCHAR(150) NOT NULL,
    telephone       VARCHAR(20) DEFAULT NULL,
    message         TEXT,
    statut          ENUM('nouvelle', 'traitee', 'archivee') DEFAULT 'nouvelle',
    date_envoi      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bien_id) REFERENCES biens(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table favoris
CREATE TABLE IF NOT EXISTS favoris (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    client_id       INT NOT NULL,
    bien_id         INT NOT NULL,
    date_ajout      DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favori (client_id, bien_id),
    FOREIGN KEY (client_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (bien_id) REFERENCES biens(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- DONNÉES DE TEST
-- ============================================

-- Admin par défaut (mot de passe: Admin@1234)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES
('Admin', 'Principal', 'admin@agence.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Biens de test
INSERT INTO biens (titre, type, transaction, prix, superficie, nb_pieces, description, localisation, ville, statut, photo1) VALUES
('Villa moderne 4 chambres', 'maison', 'vente', 185000000, 220.00, 7, 'Magnifique villa moderne avec piscine, jardin paysager et garage double. Matériaux haut de gamme, cuisine équipée, terrasse panoramique.', 'Almadies, Dakar', 'Dakar', 'disponible', 'bien1.jpg'),
('Appartement standing centre-ville', 'immeuble', 'location', 450000, 95.00, 4, 'Bel appartement au 3ème étage avec ascenseur, balcon, parking privé. Vue dégagée, sécurisé 24h/24.', 'Plateau, Dakar', 'Dakar', 'disponible', 'bien2.jpg'),
('Terrain constructible 800m²', 'terrain', 'vente', 45000000, 800.00, NULL, 'Terrain viabilisé en zone résidentielle, toutes commodités à proximité. Titre foncier disponible.', 'Diamniadio', 'Dakar', 'disponible', 'bien3.jpg'),
('Maison familiale 5 pièces', 'maison', 'vente', 95000000, 160.00, 5, 'Belle maison avec cour intérieure, 3 chambres, salon spacieux. Quartier calme et sécurisé.', 'Sacré-Cœur', 'Dakar', 'reserve', 'bien4.jpg'),
('Local commercial 120m²', 'immeuble', 'location', 800000, 120.00, NULL, 'Local commercial en rez-de-chaussée, grande vitrine, climatisation, parking devant.', 'VDN, Dakar', 'Dakar', 'disponible', 'bien5.jpg'),
('Terrain zone industrielle', 'terrain', 'vente', 75000000, 2000.00, NULL, 'Grand terrain en zone industrielle avec accès direct route nationale. Idéal entrepôt ou usine.', 'Thiès', 'Thiès', 'disponible', 'bien6.jpg');
