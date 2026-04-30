-- =============================================================================
-- AGRE FITNESS - BIBLIOTHÈQUE COMPLÈTE D'EXERCICES
-- =============================================================================
-- Script INSERT INTO complet avec exercices variés
-- Catégories : Poids du corps, Haltères, Machines, Cardio, Étirements
-- 
-- Installation : Copier ce script dans phpMyAdmin et exécuter
-- Prérequis : La table exercises_reference doit exister
-- =============================================================================

-- Vérifier et créer la table si elle n'existe pas
CREATE TABLE IF NOT EXISTS `exercises_reference` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`name_fr` VARCHAR(100) NOT NULL,
`name_en` VARCHAR(100) DEFAULT NULL,
`muscle_group` VARCHAR(50) DEFAULT NULL,
`difficulty` ENUM('debutant', 'intermediaire', 'avance') DEFAULT 'debutant',
`equipment_type` ENUM('poids_du_corps', 'halteres', 'machine', 'barre', 'kettlebell', 'elastique', 'autre') DEFAULT 'poids_du_corps',
`video_file` VARCHAR(255) DEFAULT NULL,
`gender` ENUM('male', 'female', 'both') DEFAULT 'both',
`description` TEXT DEFAULT NULL,
`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
INDEX `idx_muscle_group` (`muscle_group`),
INDEX `idx_difficulty` (`difficulty`),
INDEX `idx_equipment` (`equipment_type`),
INDEX `idx_gender` (`gender`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- EXERCICES POIDS DU CORPS (Calisthénie)
-- =============================================================================

INSERT INTO `exercises_reference` (`name_fr`, `name_en`, `muscle_group`, `difficulty`, `equipment_type`, `gender`, `description`) VALUES
-- Pectoraux
('Pompes classiques', 'Push-ups', 'pectoraux', 'debutant', 'poids_du_corps', 'both', 'Position planche, mains à largeur d\'épaules, descendre la poitrine au sol puis remonter'),
('Pompes diamant', 'Diamond Push-ups', 'pectoraux', 'intermediaire', 'poids_du_corps', 'both', 'Pompes avec les mains jointes en forme de diamant, cible les triceps'),
('Pompes déclinées', 'Decline Push-ups', 'pectoraux', 'intermediaire', 'poids_du_corps', 'both', 'Pieds surélevés pour cibler la partie supérieure des pectoraux'),
('Pompes inclinées', 'Incline Push-ups', 'pectoraux', 'debutant', 'poids_du_corps', 'both', 'Mains surélevées, plus facile pour débutants'),
('Pompes archer', 'Archer Push-ups', 'pectoraux', 'avance', 'poids_du_corps', 'both', 'Pompes unilatérales alternées, très difficiles'),

-- Dos
('Tractions pronation', 'Pull-ups', 'dos', 'intermediaire', 'poids_du_corps', 'both', 'Tractions avec les paumes vers l\'avant, prise large'),
('Tractions supination', 'Chin-ups', 'dos', 'intermediaire', 'poids_du_corps', 'both', 'Tractions avec les paumes vers soi, cible plus les biceps'),
('Tractions neutres', 'Neutral Grip Pull-ups', 'dos', 'intermediaire', 'poids_du_corps', 'both', 'Prise neutre, moins de stress sur les poignets'),
('Rowing inversé', 'Inverted Rows', 'dos', 'debutant', 'poids_du_corps', 'both', 'Tirage horizontal sous une barre ou une table'),
('Superman', 'Superman', 'dos', 'debutant', 'poids_du_corps', 'both', 'Extension du dos en position couchée sur le ventre'),
('Pont fessier', 'Glute Bridge', 'dos', 'debutant', 'poids_du_corps', 'both', 'Hanche au sol puis soulevée, cible les fessiers et le bas du dos'),
('Pont une jambe', 'Single Leg Glute Bridge', 'dos', 'intermediaire', 'poids_du_corps', 'both', 'Pont sur une jambe pour plus d\'intensité'),

-- Épaules
('Pike push-ups', 'Pike Push-ups', 'epaules', 'intermediaire', 'poids_du_corps', 'both', 'Pompes en position V inversé, cible les épaules'),
('Pompes handstand', 'Handstand Push-ups', 'epaules', 'avance', 'poids_du_corps', 'both', 'Pompes en équilibre sur les mains, très avancé'),
('Dips entre chaises', 'Chair Dips', 'epaules', 'debutant', 'poids_du_corps', 'both', 'Dips avec les mains sur deux chaises'),
('T balances', 'T Planks', 'epaules', 'intermediaire', 'poids_du_corps', 'both', 'Planche avec rotation en T, cible épaules et core'),
('Marcheur de mur', 'Wall Walks', 'epaules', 'avance', 'poids_du_corps', 'both', 'Marche des mains vers le mur en partant de planche'),

-- Biceps
('Flexions bras', 'Arm Curls', 'biceps', 'debutant', 'poids_du_corps', 'both', 'Flexion des bras sans charge, isométrique'),
('Tractions commando', 'Commando Pull-ups', 'biceps', 'avance', 'poids_du_corps', 'both', 'Tractions en montant le long de la barre'),
('Flexions isométriques', 'Isometric Curls', 'biceps', 'intermediaire', 'poids_du_corps', 'both', 'Maintenir la contraction en position intermédiaire'),

-- Triceps
('Extensions triceps', 'Tricep Extensions', 'triceps', 'debutant', 'poids_du_corps', 'both', 'Extensions des bras en position assise ou debout'),
('Pompes triceps', 'Tricep Push-ups', 'triceps', 'intermediaire', 'poids_du_corps', 'both', 'Pompes avec les coudes serrés le long du corps'),
('Dips au sol', 'Floor Dips', 'triceps', 'debutant', 'poids_du_corps', 'both', 'Dips au sol, bras derrière le dos'),

-- Jambes - Quadriceps
('Squats', 'Squats', 'quadriceps', 'debutant', 'poids_du_corps', 'both', 'Accroupissement classique, dos droit, genoux suivent les orteils'),
('Squat sumo', 'Sumo Squats', 'quadriceps', 'debutant', 'poids_du_corps', 'both', 'Squat avec jambes très écartées, cible l\'intérieur des cuisses'),
('Squat une jambe', 'Pistol Squat', 'quadriceps', 'avance', 'poids_du_corps', 'both', 'Squat sur une jambe, exercice très difficile'),
('Fentes avant', 'Forward Lunges', 'quadriceps', 'debutant', 'poids_du_corps', 'both', 'Fente en avant, genou arrière touche presque le sol'),
('Fentes arrière', 'Reverse Lunges', 'quadriceps', 'debutant', 'poids_du_corps', 'both', 'Fente en arrière, plus facile sur les genoux'),
('Fentes latérales', 'Side Lunges', 'quadriceps', 'intermediaire', 'poids_du_corps', 'both', 'Fente sur le côté, cible les adducteurs'),
('Fentes bulgares', 'Bulgarian Split Squats', 'quadriceps', 'intermediaire', 'poids_du_corps', 'both', 'Fente avec le pied arrière surélevé'),
('Sissy squat', 'Sissy Squat', 'quadriceps', 'avance', 'poids_du_corps', 'both', 'Squat sur la pointe des pieds, corps incliné en arrière'),
('Squat sauteur', 'Jump Squats', 'quadriceps', 'intermediaire', 'poids_du_corps', 'both', 'Squat avec saut en fin de mouvement'),

-- Jambes - Ischio-jambiers
('Good morning', 'Good Morning', 'ischio_jambiers', 'intermediaire', 'poids_du_corps', 'both', 'Penché en avant, jambes tendues, cible les ischios'),
('Pont nordique', 'Nordic Hamstring Curl', 'ischio_jambiers', 'avance', 'poids_du_corps', 'both', 'Curl ischio avec fixation des chevilles'),
('RDL une jambe', 'Single Leg RDL', 'ischio_jambiers', 'intermediaire', 'poids_du_corps', 'both', 'Roumain deadlift sur une jambe'),

-- Jambes - Mollets
('Extensions mollets', 'Calf Raises', 'mollets', 'debutant', 'poids_du_corps', 'both', 'Extension des chevilles, montée sur la pointe des pieds'),
('Extensions mollets une jambe', 'Single Leg Calf Raises', 'mollets', 'intermediaire', 'poids_du_corps', 'both', 'Extension sur une jambe pour plus d\'intensité'),
('Extensions mollets pieds inversés', 'Inverted Calf Raises', 'mollets', 'intermediaire', 'poids_du_corps', 'both', 'Talons ensemble, pointes des pieds écartées'),

-- Core / Abdominaux
('Planche', 'Plank', 'abdominaux', 'debutant', 'poids_du_corps', 'both', 'Position de planche sur les avant-bras'),
('Planche latérale', 'Side Plank', 'abdominaux', 'intermediaire', 'poids_du_corps', 'both', 'Planche sur le côté, cible les obliques'),
('Planche dynamique', 'Mountain Climbers', 'abdominaux', 'intermediaire', 'poids_du_corps', 'both', 'Planche avec montées alternées des genoux'),
('Crunch', 'Crunches', 'abdominaux', 'debutant', 'poids_du_corps', 'both', 'Redressement assis classique'),
('Crunch inversé', 'Reverse Crunches', 'abdominaux', 'debutant', 'poids_du_corps', 'both', 'Montée des jambes vers le torse'),
('Crunch oblique', 'Oblique Crunches', 'abdominaux', 'intermediaire', 'poids_du_corps', 'both', 'Crunch avec rotation, cible les obliques'),
('Lever de jambes', 'Leg Raises', 'abdominaux', 'intermediaire', 'poids_du_corps', 'both', 'Lever des jambes tendues, très efficace pour le bas du ventre'),
('Hollow body', 'Hollow Body Hold', 'abdominaux', 'intermediaire', 'poids_du_corps', 'both', 'Maintien en position banane inversée'),
('V-ups', 'V-ups', 'abdominaux', 'avance', 'poids_du_corps', 'both', 'Toucher les pieds avec les mains en position V'),
('Russian twists', 'Russian Twists', 'abdominaux', 'intermediaire', 'poids_du_corps', 'both', 'Rotations du buste assis, cible les obliques'),
('Windshield wipers', 'Windshield Wipers', 'abdominaux', 'avance', 'poids_du_corps', 'both', 'Rotations des jambes tendues suspendues'),
('L-sit', 'L-sit', 'abdominaux', 'avance', 'poids_du_corps', 'both', 'Maintien en L, très difficile pour le core'),
('Flutter kicks', 'Flutter Kicks', 'abdominaux', 'intermediaire', 'poids_du_corps', 'both', 'Battements des jambes en position couchée'),
('Bicycle', 'Bicycle Crunches', 'abdominaux', 'intermediaire', 'poids_du_corps', 'both', 'Crunch avec mouvement de pédalage'),
('Dead bug', 'Dead Bug', 'abdominaux', 'debutant', 'poids_du_corps', 'both', 'Opposition bras/jambes en position couchée'),

-- Fessiers
('Pont fessier marche', 'Walking Glute Bridge', 'fessiers', 'intermediaire', 'poids_du_corps', 'both', 'Pont fessier avec marche des pieds'),
('Donkey kicks', 'Donkey Kicks', 'fessiers', 'debutant', 'poids_du_corps', 'both', 'Coups de pied vers l\'arrière à 4 pattes'),
('Fire hydrants', 'Fire Hydrants', 'fessiers', 'debutant', 'poids_du_corps', 'both', 'Ouverture de jambe latérale à 4 pattes'),
('Pont fessier une jambe', 'Single Leg Glute Bridge', 'fessiers', 'intermediaire', 'poids_du_corps', 'both', 'Pont avec une jambe tendue'),
('Squat grenouille', 'Frog Squat', 'fessiers', 'intermediaire', 'poids_du_corps', 'both', 'Squat avec pieds joints et genoux écartés'),

-- Cardio / HIIT
('Burpees', 'Burpees', 'cardio', 'intermediaire', 'poids_du_corps', 'both', 'Enchaînement squat-pompe-saut, exercice complet intense'),
('Mountain climbers', 'Mountain Climbers', 'cardio', 'intermediaire', 'poids_du_corps', 'both', 'Course sur place en position planche'),
('Jumping jacks', 'Jumping Jacks', 'cardio', 'debutant', 'poids_du_corps', 'both', 'Sauts avec écartement des jambes et bras'),
('High knees', 'High Knees', 'cardio', 'intermediaire', 'poids_du_corps', 'both', 'Course sur place avec montée haute des genoux'),
('Butt kicks', 'Butt Kicks', 'cardio', 'debutant', 'poids_du_corps', 'both', 'Course sur place avec talons aux fesses'),
('Skaters', 'Skaters', 'cardio', 'intermediaire', 'poids_du_corps', 'both', 'Sauts latérals style patinage'),
('Jumping lunges', 'Jumping Lunges', 'cardio', 'avance', 'poids_du_corps', 'both', 'Fentes avec saut de changement'),
('Box jumps sans box', 'Air Box Jumps', 'cardio', 'intermediaire', 'poids_du_corps', 'both', 'Sauts en hauteur imitation box jump'),
('Bear crawls', 'Bear Crawls', 'cardio', 'intermediaire', 'poids_du_corps', 'both', 'Marche à 4 pattes, genoux levés'),
('Crab walks', 'Crab Walks', 'cardio', 'intermediaire', 'poids_du_corps', 'both', 'Marche en position crabe'),
('Inchworms', 'Inchworms', 'cardio', 'intermediaire', 'poids_du_corps', 'both', 'Marche des mains depuis les pieds en planche'),
('Ice skaters', 'Ice Skaters', 'cardio', 'intermediaire', 'poids_du_corps', 'both', 'Sauts latéraux avec changement de jambe');


-- =============================================================================
-- EXERCICES AVEC HALTÈRES
-- =============================================================================

INSERT INTO `exercises_reference` (`name_fr`, `name_en`, `muscle_group`, `difficulty`, `equipment_type`, `gender`, `description`) VALUES
-- Pectoraux
('Développé couché haltères', 'Dumbbell Bench Press', 'pectoraux', 'intermediaire', 'halteres', 'both', 'Développé couché avec haltères, amplitude plus importante'),
('Développé incliné haltères', 'Incline Dumbbell Press', 'pectoraux', 'intermediaire', 'halteres', 'both', 'Développé sur banc incliné, cible le haut des pectoraux'),
('Développé décliné haltères', 'Decline Dumbbell Press', 'pectoraux', 'intermediaire', 'halteres', 'both', 'Développé sur banc décliné, cible le bas des pectoraux'),
('Pec fly haltères', 'Dumbbell Flyes', 'pectoraux', 'intermediaire', 'halteres', 'both', 'Écartés couchés avec haltères, isolation des pectoraux'),
('Pec fly incliné', 'Incline Dumbbell Flyes', 'pectoraux', 'intermediaire', 'halteres', 'both', 'Écartés sur banc incliné'),
('Pullover haltère', 'Dumbbell Pullover', 'pectoraux', 'intermediaire', 'halteres', 'both', 'Pullover avec un haltère, cible pectoraux et dos'),
('Press convergent', 'Dumbbell Squeeze Press', 'pectoraux', 'intermediaire', 'halteres', 'both', 'Développé avec haltères serrés, contraction interne'),

-- Dos
('Rowing unilatéral', 'Single Arm Dumbbell Row', 'dos', 'debutant', 'halteres', 'both', 'Rowing avec un haltère, un genou sur le banc'),
('Rowing haltères', 'Bent Over Dumbbell Row', 'dos', 'intermediaire', 'halteres', 'both', 'Rowing penché avec deux haltères'),
('Rowing Arnold', 'Arnold Row', 'dos', 'intermediaire', 'halteres', 'both', 'Rowing avec rotation, cible le haut du dos'),
('Shrugs haltères', 'Dumbbell Shrugs', 'dos', 'debutant', 'halteres', 'both', 'Haussements d\'épaules avec haltères'),
('Deadlift roumain haltères', 'Dumbbell RDL', 'dos', 'intermediaire', 'halteres', 'both', 'Soulevé de terre roumain avec haltères'),
('Renegade rows', 'Renegade Rows', 'dos', 'avance', 'halteres', 'both', 'Rowing en position planche, très difficile'),
('Deadlift sumo haltères', 'Sumo Dumbbell Deadlift', 'dos', 'intermediaire', 'halteres', 'both', 'Deadlift avec prise sumo'),
('Good morning haltères', 'Dumbbell Good Morning', 'dos', 'intermediaire', 'halteres', 'both', 'Penché avant avec haltères sur les épaules'),

-- Épaules
('Développé militaire haltères', 'Dumbbell Overhead Press', 'epaules', 'intermediaire', 'halteres', 'both', 'Développé militaire avec haltères, debout ou assis'),
('Développé Arnold', 'Arnold Press', 'epaules', 'intermediaire', 'halteres', 'both', 'Développé avec rotation des poignets'),
('Élévations latérales', 'Lateral Raises', 'epaules', 'debutant', 'halteres', 'both', 'Élévations latérales, cible le deltoïde moyen'),
('Élévations frontales', 'Front Raises', 'epaules', 'debutant', 'halteres', 'both', 'Élévations avant, cible le deltoïde antérieur'),
('Oiseau', 'Reverse Flyes', 'epaules', 'intermediaire', 'halteres', 'both', 'Écartés penchés, cible les deltoïdes postérieurs'),
('Face pulls haltères', 'Dumbbell Face Pulls', 'epaules', 'intermediaire', 'halteres', 'both', 'Tirage vers le visage avec haltères'),
('Around the world', 'Around the World', 'epaules', 'intermediaire', 'halteres', 'both', 'Mouvement circulaire avec haltères'),
('Élévations 21s', '21s Lateral Raises', 'epaules', 'intermediaire', 'halteres', 'both', 'Série 21 répétitions : 7 bas, 7 haut, 7 complets'),

-- Biceps
('Curl haltères', 'Dumbbell Curls', 'biceps', 'debutant', 'halteres', 'both', 'Flexion des avant-bras classique'),
('Curl marteau', 'Hammer Curls', 'biceps', 'debutant', 'halteres', 'both', 'Curl avec prise neutre, cible brachial et avant-bras'),
('Curl concentré', 'Concentration Curls', 'biceps', 'intermediaire', 'halteres', 'both', 'Curl assis avec le coude sur la cuisse'),
('Curl incliné', 'Incline Dumbbell Curls', 'biceps', 'intermediaire', 'halteres', 'both', 'Curl sur banc incliné, étire les biceps'),
('Curl spider', 'Spider Curls', 'biceps', 'intermediaire', 'halteres', 'both', 'Curl couché sur banc incliné à l\'envers'),
('Curl alterné', 'Alternating Dumbbell Curls', 'biceps', 'debutant', 'halteres', 'both', 'Curl bras alternés'),
('Curl 21s', '21s Curls', 'biceps', 'intermediaire', 'halteres', 'both', '21 répétitions segmentées'),
('Curl Zottman', 'Zottman Curls', 'biceps', 'intermediaire', 'halteres', 'both', 'Curl supination en montée, pronation en descente'),

-- Triceps
('Extension triceps au-dessus', 'Overhead Tricep Extension', 'triceps', 'debutant', 'halteres', 'both', 'Extension avec haltère au-dessus de la tête'),
('Extension triceps couché', 'Lying Tricep Extension', 'triceps', 'intermediaire', 'halteres', 'both', 'Extension couché, style "skullcrusher"'),
('Kickback', 'Tricep Kickbacks', 'triceps', 'debutant', 'halteres', 'both', 'Extension en position rowing'),
('Extension triceps une main', 'Single Arm Overhead Extension', 'triceps', 'debutant', 'halteres', 'both', 'Extension une main au-dessus de la tête'),
('Floor press triceps', 'Floor Press', 'triceps', 'intermediaire', 'halteres', 'both', 'Développé au sol avec coudes serrés'),

-- Jambes
('Goblet squat', 'Goblet Squat', 'quadriceps', 'debutant', 'halteres', 'both', 'Squat avec haltère tenu devant la poitrine'),
('Fentes haltères', 'Dumbbell Lunges', 'quadriceps', 'intermediaire', 'halteres', 'both', 'Fentes avec haltères'),
('Fentes bulgares haltères', 'Bulgarian Split Squat DB', 'quadriceps', 'intermediaire', 'halteres', 'both', 'Fentes bulgares avec haltères'),
('Step-ups haltères', 'Dumbbell Step-ups', 'quadriceps', 'intermediaire', 'halteres', 'both', 'Montées sur banc avec haltères'),
('Squat sumo haltère', 'Sumo Squat DB', 'quadriceps', 'intermediaire', 'halteres', 'both', 'Squat sumo avec un haltère'),
('Good morning haltères', 'Dumbbell Good Morning', 'ischio_jambiers', 'intermediaire', 'halteres', 'both', 'Penché avant avec haltères'),
('Deadlift une jambe', 'Single Leg Deadlift', 'ischio_jambiers', 'intermediaire', 'halteres', 'both', 'Deadlift sur une jambe'),
('Fentes latérales haltères', 'Lateral Lunges DB', 'quadriceps', 'intermediaire', 'halteres', 'both', 'Fentes sur le côté avec haltères'),
('Extensions mollets haltères', 'Dumbbell Calf Raises', 'mollets', 'debutant', 'halteres', 'both', 'Extensions mollets avec haltères'),
('Hack squat haltères', 'Dumbbell Hack Squat', 'quadriceps', 'intermediaire', 'halteres', 'both', 'Squat avec haltères derrière les cuisses'),
('Sissy squat haltère', 'Sissy Squat DB', 'quadriceps', 'avance', 'halteres', 'both', 'Sissy squat avec charge'),

-- Full Body / Complexes
('Man makers', 'Man Makers', 'full_body', 'avance', 'halteres', 'both', 'Complexe: pompe, rowing, squat, développé'),
('Devil press', 'Devil Press', 'full_body', 'avance', 'halteres', 'both', 'Burpee avec développé d\'haltères'),
('Thrusters', 'Dumbbell Thrusters', 'full_body', 'intermediaire', 'halteres', 'both', 'Squat + développé en un mouvement'),
('Clean and press', 'Dumbbell Clean and Press', 'full_body', 'avance', 'halteres', 'both', 'Épaulé-jeté avec haltères'),
('Snatch haltère', 'Dumbbell Snatch', 'full_body', 'avance', 'halteres', 'both', 'Arraché avec haltère'),
('Turkish get-up', 'Turkish Get-Up', 'full_body', 'avance', 'halteres', 'both', 'Mouvement complexe de lever au sol à debout'),
('Farmers walk', 'Farmers Walk', 'full_body', 'debutant', 'halteres', 'both', 'Marche avec haltères lourds'),
('Waiter walk', 'Waiter Walk', 'full_body', 'intermediaire', 'halteres', 'both', 'Marche avec haltère tenu au-dessus de la tête'),
('Suitcase carry', 'Suitcase Carry', 'full_body', 'intermediaire', 'halteres', 'both', 'Marche avec haltère d\'un seul côté');


-- =============================================================================
-- EXERCICES AVEC MACHINES
-- =============================================================================

INSERT INTO `exercises_reference` (`name_fr`, `name_en`, `muscle_group`, `difficulty`, `equipment_type`, `gender`, `description`) VALUES
-- Pectoraux
('Développé machine', 'Machine Chest Press', 'pectoraux', 'debutant', 'machine', 'both', 'Développé à la machine, mouvement guidé sécurisé'),
('Butterfly / Pec deck', 'Pec Deck Fly', 'pectoraux', 'debutant', 'machine', 'both', 'Écartés à la machine, isolation parfaite'),
('Développé convergent', 'Converging Chest Press', 'pectoraux', 'debutant', 'machine', 'both', 'Machine avec mouvement convergent'),
('Presse à bras', 'Seated Chest Press', 'pectoraux', 'debutant', 'machine', 'both', 'Développé assis à la machine'),

-- Dos
('Tirage vertical', 'Lat Pulldown', 'dos', 'debutant', 'machine', 'both', 'Traction à la machine avec barre'),
('Tirage vertical prise serrée', 'Close Grip Pulldown', 'dos', 'debutant', 'machine', 'both', 'Tirage avec prise en V'),
('Rowing assis', 'Seated Cable Row', 'dos', 'debutant', 'machine', 'both', 'Rowing assis à la poulie'),
('Rowing unilatéral machine', 'Single Arm Row Machine', 'dos', 'debutant', 'machine', 'both', 'Rowing bras indépendant'),
('Tirage horizontal', 'Horizontal Pulldown', 'dos', 'debutant', 'machine', 'both', 'Traction horizontale'),
('Pullover machine', 'Machine Pullover', 'dos', 'intermediaire', 'machine', 'both', 'Pullover guidé à la machine'),
('Hyperextension', 'Back Extension', 'dos', 'debutant', 'machine', 'both', 'Extension lombaire à la machine'),

-- Jambes
('Presse à cuisses', 'Leg Press', 'quadriceps', 'debutant', 'machine', 'both', 'Presse à cuisses inclinée'),
('Presse à cuisses horizontale', 'Horizontal Leg Press', 'quadriceps', 'debutant', 'machine', 'both', 'Presse à cuisses assise'),
('Hack squat machine', 'Hack Squat Machine', 'quadriceps', 'intermediaire', 'machine', 'both', 'Hack squat guidé'),
('Squat V', 'V-Squat', 'quadriceps', 'intermediaire', 'machine', 'both', 'Squat sur machine V'),
('Squat pendulum', 'Pendulum Squat', 'quadriceps', 'intermediaire', 'machine', 'both', 'Squat sur machine pendulum'),
('Leg extension', 'Leg Extension', 'quadriceps', 'debutant', 'machine', 'both', 'Extension des jambes, isolation quadriceps'),
('Leg curl allongé', 'Lying Leg Curl', 'ischio_jambiers', 'debutant', 'machine', 'both', 'Curl des jambes allongé'),
('Leg curl assis', 'Seated Leg Curl', 'ischio_jambiers', 'debutant', 'machine', 'both', 'Curl des jambes assis'),
('Leg curl debout', 'Standing Leg Curl', 'ischio_jambiers', 'debutant', 'machine', 'both', 'Curl une jambe debout'),
('Presse mollets', 'Calf Press', 'mollets', 'debutant', 'machine', 'both', 'Extensions mollets à la presse'),
('Machine à mollets', 'Calf Raise Machine', 'mollets', 'debutant', 'machine', 'both', 'Extensions mollets debout'),
('Mollets assis machine', 'Seated Calf Raise', 'mollets', 'debutant', 'machine', 'both', 'Extensions mollets assis'),
('Adducteurs', 'Hip Adduction', 'adducteurs', 'debutant', 'machine', 'both', 'Machine adducteurs, serre l\'intérieur des cuisses'),
('Abducteurs', 'Hip Abduction', 'abducteurs', 'debutant', 'machine', 'both', 'Machine abducteurs, écarte les cuisses'),
('Hip thrust machine', 'Machine Hip Thrust', 'fessiers', 'intermediaire', 'machine', 'both', 'Hip thrust guidé'),
('Glute kickback', 'Glute Kickback Machine', 'fessiers', 'debutant', 'machine', 'both', 'Coup de pied en arrière à la machine'),
('Sissy squat machine', 'Sissy Squat Machine', 'quadriceps', 'intermediaire', 'machine', 'both', 'Sissy squat guidé'),
('Smith machine squat', 'Smith Machine Squat', 'quadriceps', 'intermediaire', 'machine', 'both', 'Squat à la Smith machine'),

-- Épaules
('Développé machine', 'Machine Shoulder Press', 'epaules', 'debutant', 'machine', 'both', 'Développé militaire guidé'),
('Développé Arnold machine', 'Arnold Press Machine', 'epaules', 'intermediaire', 'machine', 'both', 'Développé avec rotation guidée'),
('Élévations latérales machine', 'Machine Lateral Raise', 'epaules', 'debutant', 'machine', 'both', 'Écartés guidés'),
('Oiseau machine', 'Reverse Pec Deck', 'epaules', 'debutant', 'machine', 'both', 'Écartés inversés à la machine'),
('Élévations frontales machine', 'Machine Front Raise', 'epaules', 'debutant', 'machine', 'both', 'Élévations avant guidées'),
('Shrugs machine', 'Machine Shrugs', 'epaules', 'debutant', 'machine', 'both', 'Haussements d\'épaules à la machine'),
('Face pulls machine', 'Face Pull Machine', 'epaules', 'debutant', 'machine', 'both', 'Tirage vers le visage guidé'),

-- Cardio machines
('Tapis de course', 'Treadmill', 'cardio', 'debutant', 'machine', 'both', 'Course ou marche sur tapis'),
('Vélo droit', 'Stationary Bike', 'cardio', 'debutant', 'machine', 'both', 'Vélo d\'appartement'),
('Vélo allongé', 'Recumbent Bike', 'cardio', 'debutant', 'machine', 'both', 'Vélo semi-allongé'),
('Rameur', 'Rowing Machine', 'cardio', 'intermediaire', 'machine', 'both', 'Rameur, exercice cardio complet'),
('Elliptique', 'Elliptical', 'cardio', 'debutant', 'machine', 'both', 'Vélo elliptique'),
('Stepper', 'Stepper', 'cardio', 'debutant', 'machine', 'both', 'Stepper vertical'),
('Escalier mécanique', 'StairMaster', 'cardio', 'intermediaire', 'machine', 'both', 'Montée d\'escaliers mécanique'),
('SkiErg', 'SkiErg', 'cardio', 'intermediaire', 'machine', 'both', 'Machine de ski de fond'),
('Air bike', 'Air Bike', 'cardio', 'intermediaire', 'machine', 'both', 'Vélo avec résistance à air'),
('Curve treadmill', 'Curve Treadmill', 'cardio', 'avance', 'machine', 'both', 'Tapis sans moteur, propulsion manuelle');


-- =============================================================================
-- EXERCICES AVEC BARRE
-- =============================================================================

INSERT INTO `exercises_reference` (`name_fr`, `name_en`, `muscle_group`, `difficulty`, `equipment_type`, `gender`, `description`) VALUES
-- Pectoraux
('Développé couché', 'Barbell Bench Press', 'pectoraux', 'intermediaire', 'barre', 'both', 'Le classique développé couché à la barre'),
('Développé incliné', 'Incline Bench Press', 'pectoraux', 'intermediaire', 'barre', 'both', 'Développé sur banc incliné'),
('Développé décliné', 'Decline Bench Press', 'pectoraux', 'intermediaire', 'barre', 'both', 'Développé sur banc décliné'),
('Développé serré', 'Close Grip Bench Press', 'pectoraux', 'intermediaire', 'barre', 'both', 'Prise serrée, cible plus les triceps'),
('Développé prise large', 'Wide Grip Bench Press', 'pectoraux', 'intermediaire', 'barre', 'both', 'Prise large pour plus d\'amplitude'),
('Développé pauses', 'Paused Bench Press', 'pectoraux', 'avance', 'barre', 'both', 'Pause de 2-3 secondes sur la poitrine'),
('Développé décliné', 'Decline Barbell Press', 'pectoraux', 'intermediaire', 'barre', 'both', 'Développé sur banc décliné'),
('Développé floor press', 'Floor Press', 'pectoraux', 'intermediaire', 'barre', 'both', 'Développé au sol, limite l\'amplitude'),

-- Dos
('Soulevé de terre', 'Deadlift', 'dos', 'intermediaire', 'barre', 'both', 'Le roi des exercices de dos'),
('Soulevé de terre roumain', 'Romanian Deadlift', 'dos', 'intermediaire', 'barre', 'both', 'Deadlift avec jambes tendues'),
('Soulevé de terre sumo', 'Sumo Deadlift', 'dos', 'intermediaire', 'barre', 'both', 'Deadlift avec prise sumo'),
('Rowing barre', 'Barbell Row', 'dos', 'intermediaire', 'barre', 'both', 'Rowing penché à la barre'),
('Rowing supination', 'Underhand Barbell Row', 'dos', 'intermediaire', 'barre', 'both', 'Rowing avec prise supination'),
('Rowing Yates', 'Yates Row', 'dos', 'intermediaire', 'barre', 'both', 'Rowing avec buste plus droit'),
('Shrugs barre', 'Barbell Shrugs', 'dos', 'debutant', 'barre', 'both', 'Haussements d\'épaules à la barre'),
('Power clean', 'Power Clean', 'dos', 'avance', 'barre', 'both', 'Arraché au niveau des épaules'),
('Clean and jerk', 'Clean and Jerk', 'dos', 'avance', 'barre', 'both', 'Épaulé-jeté complet'),
('Snatch', 'Snatch', 'dos', 'avance', 'barre', 'both', 'Arraché complet au-dessus de la tête'),
('Good morning barre', 'Barbell Good Morning', 'dos', 'avance', 'barre', 'both', 'Penché avant avec barre sur les épaules'),
('T-bar row', 'T-Bar Row', 'dos', 'intermediaire', 'barre', 'both', 'Rowing avec barre en T'),
('Meadows row', 'Meadows Row', 'dos', 'avance', 'barre', 'both', 'Rowing avec extrémité de barre fixée'),

-- Épaules
('Développé militaire', 'Overhead Press', 'epaules', 'intermediaire', 'barre', 'both', 'Développé militaire debout'),
('Développé assis', 'Seated Military Press', 'epaules', 'intermediaire', 'barre', 'both', 'Développé militaire assis'),
('Développé behind neck', 'Behind the Neck Press', 'epaules', 'avance', 'barre', 'both', 'Développé derrière la nuque'),
('Push press', 'Push Press', 'epaules', 'intermediaire', 'barre', 'both', 'Développé avec aide des jambes'),
('Jerk', 'Jerk', 'epaules', 'avance', 'barre', 'both', 'Jeté de l\'épaule'),
('Upright row', 'Upright Row', 'epaules', 'intermediaire', 'barre', 'both', 'Tirage vertical vers le menton'),
('High pull', 'High Pull', 'epaules', 'avance', 'barre', 'both', 'Tirage haut explosif'),
('Landmine press', 'Landmine Press', 'epaules', 'intermediaire', 'barre', 'both', 'Développé avec barre fixée au sol'),

-- Jambes
('Squat', 'Barbell Squat', 'quadriceps', 'intermediaire', 'barre', 'both', 'Le roi des exercices de jambes'),
('Squat avant', 'Front Squat', 'quadriceps', 'avance', 'barre', 'both', 'Squat avec barre devant'),
('Squat box', 'Box Squat', 'quadriceps', 'intermediaire', 'barre', 'both', 'Squat sur une box pour la technique'),
('Squat pause', 'Paused Squat', 'quadriceps', 'avance', 'barre', 'both', 'Squat avec pause en bas'),
('Squat jump', 'Jump Squat', 'quadriceps', 'avance', 'barre', 'both', 'Squat avec saut explosif'),
('Squat bulgare barre', 'Bulgarian Split Squat BB', 'quadriceps', 'avance', 'barre', 'both', 'Fente bulgare avec barre'),
('Fentes barre', 'Barbell Lunges', 'quadriceps', 'intermediaire', 'barre', 'both', 'Fentes avec barre sur les épaules'),
('Fentes arrière barre', 'Reverse Barbell Lunges', 'quadriceps', 'intermediaire', 'barre', 'both', 'Fentes arrière avec barre'),
('Fentes latérales barre', 'Lateral Barbell Lunges', 'quadriceps', 'avance', 'barre', 'both', 'Fentes latérales avec barre'),
('Good morning', 'Good Morning', 'ischio_jambiers', 'avance', 'barre', 'both', 'Penché avant avec barre'),
('Hip thrust barre', 'Barbell Hip Thrust', 'fessiers', 'intermediaire', 'barre', 'both', 'Hip thrust avec barre'),
('Step-ups barre', 'Barbell Step-ups', 'quadriceps', 'intermediaire', 'barre', 'both', 'Montées sur banc avec barre'),
('Hack squat barre', 'Barbell Hack Squat', 'quadriceps', 'intermediaire', 'barre', 'both', 'Squat avec barre derrière le dos'),
('Zercher squat', 'Zercher Squat', 'quadriceps', 'avance', 'barre', 'both', 'Squat avec barre dans les coudes'),
('Overhead squat', 'Overhead Squat', 'quadriceps', 'avance', 'barre', 'both', 'Squat avec barre au-dessus de la tête'),
('Jefferson squat', 'Jefferson Squat', 'quadriceps', 'avance', 'barre', 'both', 'Squat avec barre entre les jambes'),
('Anderson squat', 'Anderson Squat', 'quadriceps', 'avance', 'barre', 'both', 'Squat départ position basse'),
('Squat pin', 'Pin Squat', 'quadriceps', 'avance', 'barre', 'both', 'Squat avec barre partant de supports'),
('Hatfield squat', 'Hatfield Squat', 'quadriceps', 'intermediaire', 'barre', 'both', 'Squat avec assistance des mains'),
('Safety bar squat', 'Safety Bar Squat', 'quadriceps', 'intermediaire', 'barre', 'both', 'Squat avec barre de sécurité');


-- =============================================================================
-- EXERCICES AVEC ÉLASTIQUES / BANDES DE RÉSISTANCE
-- =============================================================================

INSERT INTO `exercises_reference` (`name_fr`, `name_en`, `muscle_group`, `difficulty`, `equipment_type`, `gender`, `description`) VALUES
('Tirage élastique', 'Band Pull Apart', 'epaules', 'debutant', 'elastique', 'both', 'Tirage latéral d\'élastique pour les deltoïdes postérieurs'),
('Face pull élastique', 'Band Face Pull', 'epaules', 'debutant', 'elastique', 'both', 'Tirage vers le visage avec élastique'),
('Press élastique', 'Band Press', 'pectoraux', 'debutant', 'elastique', 'both', 'Développé avec élastique'),
('Fly élastique', 'Band Fly', 'pectoraux', 'debutant', 'elastique', 'both', 'Écartés avec élastique'),
('Rowing élastique', 'Band Row', 'dos', 'debutant', 'elastique', 'both', 'Rowing avec élastique fixé'),
('Pull apart élastique', 'Band Pull Apart', 'dos', 'debutant', 'elastique', 'both', 'Écartement des bras avec élastique'),
('Curl élastique', 'Band Curl', 'biceps', 'debutant', 'elastique', 'both', 'Curl avec élastique sous les pieds'),
('Extension triceps élastique', 'Band Tricep Extension', 'triceps', 'debutant', 'elastique', 'both', 'Extension au-dessus avec élastique'),
('Élévations élastique', 'Band Lateral Raise', 'epaules', 'debutant', 'elastique', 'both', 'Élévations latérales avec élastique'),
('Squat élastique', 'Band Squat', 'quadriceps', 'debutant', 'elastique', 'both', 'Squat avec élastique au-dessus des épaules'),
('Fentes élastique', 'Band Lunges', 'quadriceps', 'debutant', 'elastique', 'both', 'Fentes avec élastique'),
('Extensions mollets élastique', 'Band Calf Raises', 'mollets', 'debutant', 'elastique', 'both', 'Extensions avec élastique'),
('Crunch élastique', 'Band Crunch', 'abdominaux', 'debutant', 'elastique', 'both', 'Crunch avec résistance d\'élastique'),
('Rotation russe élastique', 'Band Russian Twist', 'abdominaux', 'intermediaire', 'elastique', 'both', 'Rotations avec élastique'),
('Monster walk', 'Monster Walk', 'fessiers', 'debutant', 'elastique', 'both', 'Marche latérale avec élastique aux genoux'),
('Clamshell élastique', 'Banded Clamshell', 'fessiers', 'debutant', 'elastique', 'both', 'Ouverture des genoux couché sur le côté'),
('Hip thrust élastique', 'Banded Hip Thrust', 'fessiers', 'debutant', 'elastique', 'both', 'Hip thrust avec élastique au-dessus des hanches'),
('Good morning élastique', 'Band Good Morning', 'ischio_jambiers', 'debutant', 'elastique', 'both', 'Penché avant avec élastique'),
('Deadlift élastique', 'Band Deadlift', 'dos', 'debutant', 'elastique', 'both', 'Deadlift avec élastique sous les pieds'),
('Pompes élastique', 'Banded Push-ups', 'pectoraux', 'intermediaire', 'elastique', 'both', 'Pompes avec élastique au-dessus du dos'),
('Traction assistée élastique', 'Assisted Pull-up', 'dos', 'debutant', 'elastique', 'both', 'Traction assistée avec élastique'),
('Dips assistés élastique', 'Assisted Dips', 'pectoraux', 'debutant', 'elastique', 'both', 'Dips assistés avec élastique'),
('Muscle-up élastique', 'Assisted Muscle-up', 'dos', 'avance', 'elastique', 'both', 'Muscle-up assisté avec élastique');


-- =============================================================================
-- EXERCICES AVEC KETTLEBELL
-- =============================================================================

INSERT INTO `exercises_reference` (`name_fr`, `name_en`, `muscle_group`, `difficulty`, `equipment_type`, `gender`, `description`) VALUES
('Swing deux mains', 'Two-Handed Kettlebell Swing', 'fessiers', 'debutant', 'kettlebell', 'both', 'Swing kettlebell avec deux mains'),
('Swing une main', 'One-Handed Kettlebell Swing', 'fessiers', 'intermediaire', 'kettlebell', 'both', 'Swing avec une main'),
('Swing alterné', 'Alternating Kettlebell Swing', 'fessiers', 'intermediaire', 'kettlebell', 'both', 'Swing en changeant de main'),
('Turkish get-up', 'Turkish Get-Up', 'full_body', 'avance', 'kettlebell', 'both', 'Mouvement complexe au sol'),
('Goblet squat kettlebell', 'Kettlebell Goblet Squat', 'quadriceps', 'debutant', 'kettlebell', 'both', 'Squat goblet avec kettlebell'),
('Clean kettlebell', 'Kettlebell Clean', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Épaulé de kettlebell'),
('Press kettlebell', 'Kettlebell Press', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Développé avec kettlebell'),
('Clean and press kettlebell', 'Kettlebell Clean and Press', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Épaulé-jeté kettlebell'),
('Snatch kettlebell', 'Kettlebell Snatch', 'epaules', 'avance', 'kettlebell', 'both', 'Arraché kettlebell'),
('Rowing kettlebell', 'Kettlebell Row', 'dos', 'intermediaire', 'kettlebell', 'both', 'Rowing avec kettlebell'),
('Farmers walk kettlebell', 'Kettlebell Farmers Walk', 'full_body', 'debutant', 'kettlebell', 'both', 'Marche avec kettlebells'),
('Overhead walk kettlebell', 'Kettlebell Overhead Walk', 'full_body', 'intermediaire', 'kettlebell', 'both', 'Marche avec kettlebell au-dessus'),
('Rack walk kettlebell', 'Kettlebell Rack Walk', 'full_body', 'intermediaire', 'kettlebell', 'both', 'Marche en position rack'),
('Deadlift kettlebell', 'Kettlebell Deadlift', 'dos', 'debutant', 'kettlebell', 'both', 'Soulevé de terre kettlebell'),
('Sumo deadlift kettlebell', 'Kettlebell Sumo Deadlift', 'dos', 'debutant', 'kettlebell', 'both', 'Deadlift sumo kettlebell'),
('Single leg deadlift kettlebell', 'Kettlebell Single Leg Deadlift', 'dos', 'intermediaire', 'kettlebell', 'both', 'Deadlift une jambe'),
('Figure 8', 'Figure 8', 'core', 'intermediaire', 'kettlebell', 'both', 'Mouvement en 8 entre les jambes'),
('Around the body', 'Around the Body Pass', 'core', 'debutant', 'kettlebell', 'both', 'Rotation autour du corps'),
('Halo', 'Halo', 'epaules', 'debutant', 'kettlebell', 'both', 'Rotation autour de la tête'),
('Arm bar', 'Arm Bar', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Extension d\'épaule au sol'),
('Windmill', 'Windmill', 'full_body', 'avance', 'kettlebell', 'both', 'Moulinet avec kettlebell au-dessus'),
('Pistol squat kettlebell', 'Kettlebell Pistol Squat', 'quadriceps', 'avance', 'kettlebell', 'both', 'Pistol squat avec kettlebell'),
('Lunge press kettlebell', 'Kettlebell Lunge Press', 'full_body', 'intermediaire', 'kettlebell', 'both', 'Fente avec développé'),
('High pull kettlebell', 'Kettlebell High Pull', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Tirage haut kettlebell'),
('Push press kettlebell', 'Kettlebell Push Press', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Développé avec aide des jambes'),
('Jerk kettlebell', 'Kettlebell Jerk', 'epaules', 'avance', 'kettlebell', 'both', 'Jeté kettlebell'),
('Thrusters kettlebell', 'Kettlebell Thrusters', 'full_body', 'intermediaire', 'kettlebell', 'both', 'Squat + développé'),
('Renegade rows kettlebell', 'Kettlebell Renegade Rows', 'dos', 'avance', 'kettlebell', 'both', 'Rowing en planche'),
('Man makers kettlebell', 'Kettlebell Man Makers', 'full_body', 'avance', 'kettlebell', 'both', 'Complexe kettlebell'),
('Devil press kettlebell', 'Kettlebell Devil Press', 'full_body', 'avance', 'kettlebell', 'both', 'Burpee + développé'),
('Deck squat', 'Deck Squat', 'full_body', 'intermediaire', 'kettlebell', 'both', 'Squat avec roulade arrière'),
('Bottom-up press', 'Bottom-Up Press', 'epaules', 'avance', 'kettlebell', 'both', 'Développé avec kettlebell à l\'envers'),
('Sots press', 'Sots Press', 'epaules', 'avance', 'kettlebell', 'both', 'Développé depuis le squat'),
('Bent press', 'Bent Press', 'full_body', 'avance', 'kettlebell', 'both', 'Développé penché historique'),
('See-saw press', 'See-Saw Press', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Développé alterné'),
('Double clean', 'Double Kettlebell Clean', 'epaules', 'avance', 'kettlebell', 'both', 'Épaulé avec deux kettlebells'),
('Double press', 'Double Kettlebell Press', 'epaules', 'avance', 'kettlebell', 'both', 'Développé avec deux kettlebells'),
('Double front squat', 'Double Kettlebell Front Squat', 'quadriceps', 'avance', 'kettlebell', 'both', 'Front squat double kettlebell'),
('Double swing', 'Double Kettlebell Swing', 'fessiers', 'avance', 'kettlebell', 'both', 'Swing avec deux kettlebells'),
('Alternating clean', 'Alternating Kettlebell Clean', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Épaulé alterné'),
('Alternating press', 'Alternating Kettlebell Press', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Développé alterné'),
('Alternating swing', 'Alternating Kettlebell Swing', 'fessiers', 'intermediaire', 'kettlebell', 'both', 'Swing alterné'),
('Single arm swing', 'Single Arm Kettlebell Swing', 'fessiers', 'intermediaire', 'kettlebell', 'both', 'Swing unilatéral'),
('Single arm clean', 'Single Arm Kettlebell Clean', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Épaulé unilatéral'),
('Single arm press', 'Single Arm Kettlebell Press', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Développé unilatéral'),
('Single arm snatch', 'Single Arm Kettlebell Snatch', 'epaules', 'avance', 'kettlebell', 'both', 'Arraché unilatéral'),
('Single arm swing switch', 'Single Arm Swing Switch', 'fessiers', 'intermediaire', 'kettlebell', 'both', 'Swing avec changement de main en l\'air'),
('Russian twist kettlebell', 'Kettlebell Russian Twist', 'abdominaux', 'intermediaire', 'kettlebell', 'both', 'Russian twist avec kettlebell'),
('Sit-up kettlebell', 'Kettlebell Sit-Up', 'abdominaux', 'intermediaire', 'kettlebell', 'both', 'Redressement assis avec kettlebell'),
('Leg raise kettlebell', 'Kettlebell Leg Raise', 'abdominaux', 'intermediaire', 'kettlebell', 'both', 'Lever de jambes avec kettlebell'),
('Plank pull-through', 'Kettlebell Plank Pull-Through', 'abdominaux', 'intermediaire', 'kettlebell', 'both', 'Tirage sous le corps en planche'),
('Squat hold', 'Kettlebell Squat Hold', 'quadriceps', 'intermediaire', 'kettlebell', 'both', 'Maintien en squat'),
('Overhead hold', 'Kettlebell Overhead Hold', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Maintien au-dessus de la tête'),
('Rack hold', 'Kettlebell Rack Hold', 'epaules', 'debutant', 'kettlebell', 'both', 'Maintien en position rack'),
('Bottoms-up carry', 'Bottoms-Up Carry', 'epaules', 'avance', 'kettlebell', 'both', 'Porté avec kettlebell à l\'envers'),
('Waiter carry', 'Waiter Carry', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Porté comme un plateau'),
('Racked carry', 'Racked Carry', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Porté en position rack'),
('Overhead carry', 'Overhead Carry', 'epaules', 'intermediaire', 'kettlebell', 'both', 'Porté au-dessus de la tête'),
('Suitcase carry', 'Suitcase Carry', 'core', 'intermediaire', 'kettlebell', 'both', 'Porté comme une valise'),
('Goblet carry', 'Goblet Carry', 'core', 'debutant', 'kettlebell', 'both', 'Porté en goblet'),
('Partner drags', 'Kettlebell Partner Drags', 'full_body', 'avance', 'kettlebell', 'both', 'Traînage partenaire avec kettlebell'),
('Sled pull', 'Kettlebell Sled Pull', 'full_body', 'intermediaire', 'kettlebell', 'both', 'Tirage de traîneau'),
('Floor press kettlebell', 'Kettlebell Floor Press', 'pectoraux', 'intermediaire', 'kettlebell', 'both', 'Développé au sol'),
('Skullcrusher kettlebell', 'Kettlebell Skullcrusher', 'triceps', 'intermediaire', 'kettlebell', 'both', 'Extension couché'),
('Pullover kettlebell', 'Kettlebell Pullover', 'pectoraux', 'intermediaire', 'kettlebell', 'both', 'Pullover avec kettlebell'),
('Fly floor', 'Kettlebell Floor Fly', 'pectoraux', 'intermediaire', 'kettlebell', 'both', 'Écartés au sol'),
('Crush curl', 'Kettlebell Crush Curl', 'biceps', 'intermediaire', 'kettlebell', 'both', 'Curl en pressant les cloches'),
('Towel curl', 'Kettlebell Towel Curl', 'biceps', 'intermediaire', 'kettlebell', 'both', 'Curl avec serviette'),
('Inverted row kettlebell', 'Kettlebell Inverted Row', 'dos', 'intermediaire', 'kettlebell', 'both', 'Rowing inversé avec kettlebells'),
('Pull-up kettlebell', 'Kettlebell Pull-up', 'dos', 'avance', 'kettlebell', 'both', 'Traction avec kettlebell'),
('Dip kettlebell', 'Kettlebell Dip', 'pectoraux', 'avance', 'kettlebell', 'both', 'Dips avec kettlebell'),
('Muscle-up kettlebell', 'Kettlebell Muscle-Up', 'full_body', 'avance', 'kettlebell', 'both', 'Muscle-up avec kettlebell'),
('Handstand push-up kettlebell', 'Kettlebell Handstand Push-up', 'epaules', 'avance', 'kettlebell', 'both', 'Pompe en équilibre');


-- =============================================================================
-- RÉSUMÉ DES STATISTIQUES
-- =============================================================================
-- Nombre total d'exercices par catégorie :
-- Poids du corps : ~80 exercices
-- Haltères : ~45 exercices  
-- Machines : ~45 exercices
-- Barre : ~45 exercices
-- Élastiques : ~25 exercices
-- Kettlebell : ~70 exercices
-- TOTAL : ~310 exercices
-- =============================================================================

-- Vérification du nombre d'exercices insérés
SELECT equipment_type, COUNT(*) as count 
FROM exercises_reference 
GROUP BY equipment_type 
ORDER BY count DESC;
