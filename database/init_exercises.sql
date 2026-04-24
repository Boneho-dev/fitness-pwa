-- =============================================================================
-- AGRE FITNESS — INIT 48 EXERCICES RÉELS
-- Table : exercises_reference
-- Male  : 31 vidéos (IDs 1-31)
-- Female: 17 vidéos (IDs 32-48)
-- video_path : chemin relatif depuis la racine web (ex: assets/videos/male/...)
-- =============================================================================
-- ⚠️  Fichiers avec anomalies détectées dans le dossier (à ne PAS renommer) :
--     Male  #1  : 1_Developpe_couche_barre.mp4.mp4          (double extension)
--     Male  #3  : 3_Tirage_horizontal_buste_penche_barre.mp4.mp4 (double extension)
--     Male  #6  : 6_Souleve_de_terre _barre.mp4             (espace avant _barre)
--     Male  #8  : 8_Curl_marteau_haltere.mp4.mp4            (double extension)
--     Male  #9  : 9_Développe_incline_haltere.mp4           (accent dans nom)
--     Female #1 : 1_Extension_des triceps_a_la_corde.mp4    (espace dans nom)
--     Female #5 : 5_Saut_en_boite_.mp4                      (underscore final)
--     Female #7 : 7_Assise_contre le_mur.mp4                (espace dans nom)
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Recréation propre de la table
DROP TABLE IF EXISTS `exercises_reference`;

CREATE TABLE `exercises_reference` (
  `id`             INT            NOT NULL,
  `name_fr`        VARCHAR(120)   NOT NULL,
  `name_en`        VARCHAR(120)   DEFAULT NULL,
  `muscle_group`   VARCHAR(50)    DEFAULT NULL,
  `difficulty`     ENUM('debutant','intermediaire','avance') DEFAULT 'debutant',
  `equipment_type` ENUM('poids_du_corps','halteres','machine','barre','kettlebell','elastique','cable','autre') DEFAULT 'autre',
  `gender`         ENUM('male','female','both')               NOT NULL DEFAULT 'both',
  `video_path`     VARCHAR(255)   DEFAULT NULL  COMMENT 'Chemin relatif depuis racine web, ex: assets/videos/male/1_xxx.mp4',
  `description`    TEXT           DEFAULT NULL,
  `created_at`     TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gender`       (`gender`),
  KEY `idx_muscle_group` (`muscle_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- HOMMES — 31 exercices (IDs 1–31)
-- Chemin : assets/videos/male/<filename>
-- =============================================================================
INSERT INTO `exercises_reference`
  (`id`, `name_fr`, `name_en`, `muscle_group`, `difficulty`, `equipment_type`, `gender`, `video_path`) VALUES

-- IDs 1-5
(1,  'Développé couché barre',                      'Barbell Bench Press',              'pectoraux',        'intermediaire', 'barre',          'male', 'assets/videos/male/1_Developpe_couche_barre.mp4.mp4'),
(2,  'Développé couché haltère',                    'Dumbbell Bench Press',             'pectoraux',        'intermediaire', 'halteres',       'male', 'assets/videos/male/2_Developpe_couche_haltere.mp4'),
(3,  'Tirage horizontal buste penché barre',        'Bent-Over Barbell Row',            'dos',              'intermediaire', 'barre',          'male', 'assets/videos/male/3_Tirage_horizontal_buste_penche_barre.mp4.mp4'),
(4,  'Flexion des biceps haltère',                  'Dumbbell Curl',                    'biceps',           'debutant',      'halteres',       'male', 'assets/videos/male/4_Flexion_des_biceps_haltere.mp4'),
(5,  'Crossovers à câble volant',                   'Cable Crossover',                  'pectoraux',        'intermediaire', 'cable',          'male', 'assets/videos/male/5_Crossovers_a_cable_volant.mp4'),

-- IDs 6-10
(6,  'Soulevé de terre barre',                      'Barbell Deadlift',                 'dos',              'intermediaire', 'barre',          'male', 'assets/videos/male/6_Souleve_de_terre _barre.mp4'),
(7,  'Tirage du visage',                            'Face Pull',                        'epaules',          'debutant',      'cable',          'male', 'assets/videos/male/7_Tirage_du_visage.mp4'),
(8,  'Curl marteau haltère',                        'Hammer Curl',                      'biceps',           'debutant',      'halteres',       'male', 'assets/videos/male/8_Curl_marteau_haltere.mp4.mp4'),
(9,  'Développé incliné haltère',                   'Incline Dumbbell Press',           'pectoraux',        'intermediaire', 'halteres',       'male', 'assets/videos/male/9_Développe_incline_haltere.mp4'),
(10, 'Tirage vertical câble',                       'Lat Pulldown',                     'dos',              'debutant',      'machine',        'male', 'assets/videos/male/10_Tirage_vertical_cable.mp4'),

-- IDs 11-15
(11, 'Élévation latérale haltère',                  'Lateral Raise',                    'epaules',          'debutant',      'halteres',       'male', 'assets/videos/male/11_Elevation_laterale_haltere.mp4'),
(12, 'Extension de jambes machine',                 'Leg Extension',                    'quadriceps',       'debutant',      'machine',        'male', 'assets/videos/male/12_Extension_de_jambes_machine.mp4'),
(13, 'Presse à jambes machine',                     'Leg Press',                        'quadriceps',       'debutant',      'machine',        'male', 'assets/videos/male/13_Presse_a_jambes_machine.mp4'),
(14, 'Tirage horizontal poulie basse prise en V',   'Seated Cable Row (V-grip)',        'dos',              'debutant',      'cable',          'male', 'assets/videos/male/14_Tirage_horizontal_a_la_poulie_basse_prise_en_V.mp4'),
(15, 'Flexion des jambes assise machine',           'Seated Leg Curl',                  'ischio_jambiers',  'debutant',      'machine',        'male', 'assets/videos/male/15_Flexion_des_jambes_en_position_assise_machine.mp4'),

-- IDs 16-20
(16, 'Développé épaules haltère',                   'Dumbbell Overhead Press',          'epaules',          'intermediaire', 'halteres',       'male', 'assets/videos/male/16_Developpe_epaules_haltere.mp4'),
(17, 'Squat barre',                                 'Barbell Squat',                    'quadriceps',       'intermediaire', 'barre',          'male', 'assets/videos/male/17_Squat_Barre.mp4'),
(18, 'Extension des triceps',                       'Tricep Extension',                 'triceps',          'debutant',      'cable',          'male', 'assets/videos/male/18_Extension_des_triceps.mp4'),
(19, 'Ciseaux abdominaux',                          'Scissor Kicks',                    'abdominaux',       'debutant',      'poids_du_corps', 'male', 'assets/videos/male/19_Ciseaux_abdominaux.mp4'),
(20, 'Roue abdominale',                             'Ab Wheel Rollout',                 'abdominaux',       'avance',        'autre',          'male', 'assets/videos/male/20_Roue_abdominale.mp4'),

-- IDs 21-25
(21, 'Arnold press haltère',                        'Arnold Press',                     'epaules',          'intermediaire', 'halteres',       'male', 'assets/videos/male/21_Arnold_press_haltere.mp4'),
(22, 'Autour du monde',                             'Around the World',                 'epaules',          'intermediaire', 'halteres',       'male', 'assets/videos/male/22_Autour_du_monde.mp4'),
(23, 'Coups de balle',                              'Battle Ball Strikes',              'cardio',           'intermediaire', 'autre',          'male', 'assets/videos/male/23_Coups_de_balle.mp4'),
(24, 'Pull-overs à bandes',                         'Band Pullovers',                   'pectoraux',        'debutant',      'elastique',      'male', 'assets/videos/male/24_Pull_overs_a_bandes.mp4'),
(25, 'Cordes de combat',                            'Battle Ropes',                     'cardio',           'intermediaire', 'autre',          'male', 'assets/videos/male/25_Cordes_de_combat.mp4'),

-- IDs 26-31
(26, 'Curl derrière le dos câble',                  'Behind-the-Back Cable Curl',       'biceps',           'intermediaire', 'cable',          'male', 'assets/videos/male/26_Curl_derriere_le_dos_cable.mp4'),
(27, 'Dips au banc',                                'Bench Dips',                       'triceps',          'debutant',      'poids_du_corps', 'male', 'assets/videos/male/27_Dips_au_banc.mp4'),
(28, 'Vélo stationnaire',                           'Stationary Bike',                  'cardio',           'debutant',      'machine',        'male', 'assets/videos/male/28_Penurie_de_velo.mp4'),
(29, 'Écrasement de câble',                         'Cable Crush',                      'pectoraux',        'intermediaire', 'cable',          'male', 'assets/videos/male/29_Ecrasement_de_cable.mp4'),
(30, 'Pectoraux isolation',                         'Chest Isolation',                  'pectoraux',        'debutant',      'poids_du_corps', 'male', 'assets/videos/male/30_Pectoraux.mp4'),
(31, 'Courage',                                     'Courage',                          'cardio',           'avance',        'poids_du_corps', 'male', 'assets/videos/male/31_Courage.mp4');

-- =============================================================================
-- FEMMES — 17 exercices (IDs 32–48)
-- Chemin : assets/videos/female/<filename>
-- =============================================================================
INSERT INTO `exercises_reference`
  (`id`, `name_fr`, `name_en`, `muscle_group`, `difficulty`, `equipment_type`, `gender`, `video_path`) VALUES

(32, 'Extension des triceps à la corde',            'Rope Tricep Pushdown',             'triceps',          'debutant',      'cable',          'female', 'assets/videos/female/1_Extension_des triceps_a_la_corde.mp4'),
(33, 'Squats pistolet assistés',                    'Assisted Pistol Squat',            'quadriceps',       'intermediaire', 'poids_du_corps', 'female', 'assets/videos/female/2_Squats_pistolet_assistes.mp4'),
(34, 'Crunch à vélo jambes levées',                 'Bicycle Crunch Legs Up',           'abdominaux',       'intermediaire', 'poids_du_corps', 'female', 'assets/videos/female/3_Crunch_a_velo_jambes_levees.mp4'),
(35, 'Chien d\'oiseau',                             'Bird Dog',                         'dos',              'debutant',      'poids_du_corps', 'female', 'assets/videos/female/4_Chien_d_oiseau.mp4'),
(36, 'Saut en boîte',                               'Box Jump',                         'quadriceps',       'intermediaire', 'autre',          'female', 'assets/videos/female/5_Saut_en_boite_.mp4'),
(37, 'Squat bulgare',                               'Bulgarian Split Squat',            'quadriceps',       'intermediaire', 'poids_du_corps', 'female', 'assets/videos/female/6_Squat_bulgare.mp4'),
(38, 'Assise contre le mur',                        'Wall Sit',                         'quadriceps',       'debutant',      'poids_du_corps', 'female', 'assets/videos/female/7_Assise_contre le_mur.mp4'),
(39, 'V-up',                                        'V-Up',                             'abdominaux',       'avance',        'poids_du_corps', 'female', 'assets/videos/female/8_V_up.mp4'),
(40, 'Tapis roulant',                               'Treadmill',                        'cardio',           'debutant',      'machine',        'female', 'assets/videos/female/9_tapis_roulant.mp4'),
(41, 'Squat sumo haltère',                          'Sumo Squat Dumbbell',              'quadriceps',       'debutant',      'halteres',       'female', 'assets/videos/female/10_Squat_sumo_h_altere.mp4'),
(42, 'Flexion des poignets paumes vers le haut',    'Seated Wrist Curl',                'avant_bras',       'debutant',      'halteres',       'female', 'assets/videos/female/11_Flexion_des_poignets_paumes_vers_le_haut_assis.mp4'),
(43, 'Rameur',                                      'Rowing Machine',                   'cardio',           'intermediaire', 'machine',        'female', 'assets/videos/female/12_rameur.mp4'),
(44, 'Alpiniste',                                   'Mountain Climber',                 'cardio',           'intermediaire', 'poids_du_corps', 'female', 'assets/videos/female/13_Alpiniste.mp4'),
(45, 'Élévation latérale bande',                    'Band Lateral Raise',               'epaules',          'debutant',      'elastique',      'female', 'assets/videos/female/14_Elevation_laterale_bande.mp4'),
(46, 'Élévations latérales des jambes',             'Side Leg Raises',                  'fessiers',         'debutant',      'poids_du_corps', 'female', 'assets/videos/female/15_Elevations_laterales_des_jambes.mp4'),
(47, 'Maintien en L',                               'L-Sit Hold',                       'abdominaux',       'avance',        'poids_du_corps', 'female', 'assets/videos/female/16_Maintien_en_L.mp4'),
(48, 'Redressement assis en forme de couteau',      'Jackknife Sit-Up',                 'abdominaux',       'intermediaire', 'poids_du_corps', 'female', 'assets/videos/female/17_Redressement_assis_en_forme_de_couteau.mp4');

-- =============================================================================
-- Vérification
-- =============================================================================
SELECT gender, COUNT(*) AS total FROM exercises_reference GROUP BY gender;
-- Résultat attendu : male=31, female=17
