/******************************************************************************************
 * Nom du fichier : 000_P2P_TYPES.sql
 * Projet         : P2P
 * Base           : PostgreSQL
 * Description    : Création et documentation des types énumérés (ENUMs) de production
 *
 * Auteur         : DJIATSA DUNAMIS JUNIOR
 * Date création  : 2026-05-24
 * Dernière modif : 2026-05-24
 * Version        : 1.0.0
 *
 * Dépendances    : Aucune
 * Notes          :
 * - Script 100 % idempotent grâce à l'interrogation du catalogue pg_type.
 * - Sécurisé par transaction globale.
 * - À exécuter obligatoirement AVANT le script de création des tables.
 ******************************************************************************************/

-- =======================================================================================
-- Configuration de l'environnement
-- ========================================================================================
CREATE SCHEMA IF NOT EXISTS p2p;


-- =======================================================================================
-- Début de la transaction globale
-- ========================================================================================
BEGIN;


-- =======================================================================================
-- Création Idempotente des Types Énumérés (Blocs PL/pgSQL protecteurs)
-- =======================================================================================

DO $$
BEGIN
    -- 1. Rôles Système
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_role_systeme' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_role_systeme AS ENUM ('CLIENT', 'PRESTATAIRE', 'EXPERT', 'ADMIN');
    END IF;

    -- 2. Niveaux de Badge (Moteur de Confiance)
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_badge_type' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_badge_type AS ENUM ('GRIS', 'BLEU', 'VERT', 'OR');
    END IF;

    -- 3. Statuts d'Activité des Prestataires
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_statut_activite' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_statut_activite AS ENUM ('DISPONIBLE', 'EN_MISSION', 'BLOQUE');
    END IF;

    -- 4. Types de Client
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_type_client' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_type_client AS ENUM ('PARTICULIER', 'ENTREPRISE');
    END IF;

    -- 5. Modes d'Exécution des Missions
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_mode_execution' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_mode_execution AS ENUM ('VIP', 'CLASSIQUE');
    END IF;

    -- 6. Cycle de Vie de la Mission (Machine à États)
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_statut_lifecycle' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_statut_lifecycle AS ENUM ('PUBLIEE', 'ASSIGNEE', 'CHECK_IN_EN_COURS', 'EN_COURS', 'TERMINEE', 'EN_GARANTIE', 'CLOTUREE', 'EN_LITIGE');
    END IF;

    -- 7. Types de Flux / Preuves Terrain
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_type_flux' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_type_flux AS ENUM ('SCAN_OCR_CNI', 'SELFIE_CONTROLE', 'PHOTO_AVANT', 'PHOTO_APRES', 'NOTE_VOCALE_DIAGNOSTIC');
    END IF;

    -- 8. Résultats de Validation des Preuves
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_resultat_validation' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_resultat_validation AS ENUM ('VALIDE_AUTOMATIQUE', 'VALIDE_EXPERT', 'REJETE');
    END IF;

    -- 9. Statuts du Séquestre Financier (Règle 70/30)
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_statut_sequestre' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_statut_sequestre AS ENUM ('BLOQUE', '70_LIBERE', '30_LIBERE', 'REMBOURSE');
    END IF;

    -- 10. Types d'Anomalies Opérationnelles
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_type_anomalie' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_type_anomalie AS ENUM ('LATE_PRESENCE', 'SHUNTING', 'CONTESTATION_QUALITE');
    END IF;

    -- 11. Statuts d'Arbitrage des Litiges
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_statut_litige' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_statut_litige AS ENUM ('OUVERT', 'EN_COURS_ARBITRAGE', 'RESOLU', 'ANNULE');
    END IF;

    -- 12. Types d'Actions de Gouvernance (Multi-Signature)
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_type_action_gouv' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_type_action_gouv AS ENUM ('MODIFICATION_MANUELLE_BADGE', 'REAUDIT_EXCEPTIONNEL', 'MUTATION_PARAMETRE_SYSTEME', 'EXCLUSION_PARTIE');
    END IF;

    -- 13. Statuts des Actions de Gouvernance
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_statut_action_gouv' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_statut_action_gouv AS ENUM ('EN_ATTENTE_SIGNATURES', 'APPROUVEE_EXECUTEE', 'REJETEE_EXPIREE');
    END IF;

    -- 14. Clés des Membres Fondateurs
    IF NOT EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE t.typname = 'enum_cle_fondateur' AND n.nspname = 'p2p') THEN
        CREATE TYPE p2p.enum_cle_fondateur AS ENUM ('ALPHA', 'BETA', 'GAMMA');
    END IF;
END $$;


-- =======================================================================================
-- Injection des Commentaires Métiers dans le Catalogue Système (Data Dictionary)
-- =======================================================================================

COMMENT ON TYPE p2p.enum_role_systeme IS 'Rôles des acteurs : CLIENT (émetteur du besoin), PRESTATAIRE (artisan terrain), EXPERT (validateur technique/CNI), ADMIN (gestionnaire des litiges et de la plateforme).';

COMMENT ON TYPE p2p.enum_badge_type IS 'Cycle de confiance : GRIS (anonyme inscrit), BLEU (identité vérifiée via OCR CNI + Selfie), VERT (compétences validées par les pairs), OR (élite cooptée, min 50 missions sans litige).';

COMMENT ON TYPE p2p.enum_statut_activite IS 'Disponibilité de l''artisan : DISPONIBLE (prêt à recevoir des missions), EN_MISSION (actuellement affecté), BLOQUE (suspendu manuellement ou automatiquement pour fraude/IMEI).';

COMMENT ON TYPE p2p.enum_type_client IS 'Segmentation client : PARTICULIER (besoin domestique spontané), ENTREPRISE (besoin corporatif avec contraintes de facturation/label spécifique).';

COMMENT ON TYPE p2p.enum_mode_execution IS 'Routage de la mission : VIP (attribution algorithmique directe et prioritaire selon proximité/recommandation), CLASSIQUE (système d''appels d''offres et de propositions de devis).';

COMMENT ON TYPE p2p.enum_statut_lifecycle IS 'Cycle nominal et exceptionnel : PUBLIEE (en attente), ASSIGNEE (artisan trouvé), CHECK_IN_EN_COURS (prestataire en route/sur site), EN_COURS (jumelage validé), TERMINEE (fin déclarée), EN_GARANTIE (couverture de 48h active), CLOTUREE (archivée avec succès), EN_LITIGE (bloquée pour arbitrage).';

COMMENT ON TYPE p2p.enum_type_flux IS 'Nature des livrables de traçabilité : SCAN_OCR_CNI et SELFIE_CONTROLE (Onboarding), PHOTO_AVANT (état initial), PHOTO_APRES (preuve de fin), NOTE_VOCALE_DIAGNOSTIC (explications techniques de l''artisan).';

COMMENT ON TYPE p2p.enum_resultat_validation IS 'État du pipeline de vérification : VALIDE_AUTOMATIQUE (approuvé par l''OCR/IA), VALIDE_EXPERT (revu et approuvé manuellement par un Expert), REJETE (preuve non conforme ou frauduleuse).';

COMMENT ON TYPE p2p.enum_statut_sequestre IS 'Flux monétaires : BLOQUE (fonds sécurisés au dépôt), 70_LIBERE (première part versée dès la validation des travaux), 30_LIBERE (solde versé après les 48h de garantie sans plainte), REMBOURSE (fonds restitués au client après arbitrage).';

COMMENT ON TYPE p2p.enum_type_anomalie IS 'Déclencheurs de litige : LATE_PRESENCE (absence de mouvement GPS ou retard 30 min avant), SHUNTING (tentative de contournement financier de la plateforme), CONTESTATION_QUALITE (client insatisfait du travail rendu).';

COMMENT ON TYPE p2p.enum_statut_litige IS 'États du contentieux : OUVERT (généré automatiquement ou par plainte), EN_COURS_ARBITRAGE (revu par un administrateur), RESOLU (arbitrage rendu et appliqué), ANNULE (fausse alerte ou accord amiable).';

COMMENT ON TYPE p2p.enum_type_action_gouv IS 'Opérations critiques exigeant un consensus : MODIFICATION_MANUELLE_BADGE (octroi d''un badge Or), REAUDIT_EXCEPTIONNEL (réévaluation forcée), MUTATION_PARAMETRE_SYSTEME (changement de taux de commission/frais), EXCLUSION_PARTIE (application du Kill-Switch pour fraude).';

COMMENT ON TYPE p2p.enum_statut_action_gouv IS 'Cycle du consensus : EN_ATTENTE_SIGNATURES (quorum non atteint), APPROUVEE_EXECUTEE (min 2/3 signatures valides, modifications appliquées en cascade), REJETEE_EXPIREE (refusée ou délai dépassé).';

COMMENT ON TYPE p2p.enum_cle_fondateur IS 'Identifiants des clés du protocole Multisig : ALPHA (Propriété Intellectuelle / Vision), BETA (Code Source / Clés), GAMMA (Accès Réseau / Database).';


-- =======================================================================================
-- Fin de la transaction globale avec persistance
-- ========================================================================================
COMMIT;



/******************************************************************************************
 * Nom du fichier : 001_P2P_CREATE.sql
 * Projet         : P2P
 * Base           : PostgreSQL
 * Description    : Scripts de définition (DDL) du schema et des tables (Architecture d'Entreprise)
 *
 * Auteur         : DJIATSA DUNAMIS JUNIOR
 * Date création  : 2026-05-22
 * Dernière modif : 2026-05-24
 * Version        : 2.0.0
 *
 * Dépendances    :
 * - 000_P2P_TYPES.sql : Script de creation de type
 *
 * Notes          :
 * - Script idempotent (peut être exécuté plusieurs fois sans erreur)
 * - Utilise des transactions pour la sécurité
 * - Soft Deletion active sur les entités critiques
 * - IDs attendus sous forme de UUIDv7 générés par la couche Applicative (Go)
 ******************************************************************************************/

-- =======================================================================================
-- Configuration de environnement
-- ========================================================================================
CREATE SCHEMA IF NOT EXISTS p2p;

-- =======================================================================================
-- Début transaction
-- ========================================================================================
BEGIN ;

-- =======================================================================================
-- Fonction utilitaire pour la mise à jour automatique de updated_at
-- =======================================================================================
CREATE OR REPLACE FUNCTION p2p.trigger_set_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- =======================================================================================
-- Table : Utilisateur
-- ========================================================================================
CREATE TABLE IF NOT EXISTS p2p.utilisateur (
/*******************************************************************
* L'utilisateur de la plateforme identifié par "id_utilisateur",
* possédant le rôle système "role_systeme" avec nom "nom" email "email"
* et mot de passe hashee "password_hash"
*******************************************************************/
    id_utilisateur  UUID PRIMARY KEY NOT NULL ,
    nom             VARCHAR(60) NOT NULL ,
    email           VARCHAR(60) NOT NULL UNIQUE ,
    password_hash   TEXT NOT NULL ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMPTZ -- Implémentation du Soft Delete
);

CREATE OR REPLACE TRIGGER set_timestamp_utilisateur
    BEFORE UPDATE ON p2p.utilisateur
    FOR EACH ROW EXECUTE FUNCTION p2p.trigger_set_timestamp();

-- =======================================================================================
-- Table : Compte de securite
-- ========================================================================================
CREATE TABLE IF NOT EXISTS p2p.compte_securite (
/*******************************************************************
* Le compte de securite identifie par l'utilisateur "id_utilisateur"
* avec le numero de telephone reel "telephone_reel", le numero proxy
* assigne "numero_proxy" et l'empreinte numérique de l'appareil "imei_appareil"
* avec numero de carte nationale d'identité "numero_cni".
* NOTE SÉCURITÉ: "imei_appareil" et "numero_cni" doivent stocker des tokens ou des hash depuis l'application.
*******************************************************************/
    id_utilisateur  UUID PRIMARY KEY NOT NULL ,
    telephone_reel  VARCHAR(25) NOT NULL UNIQUE,
    numero_proxy    VARCHAR(25) NOT NULL UNIQUE,
    imei_appareil   VARCHAR(255) NOT NULL UNIQUE,
    numero_cni      VARCHAR(255) NOT NULL UNIQUE,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT compte_securite_fk_utilisateur
        FOREIGN KEY (id_utilisateur)
        REFERENCES p2p.utilisateur(id_utilisateur)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE OR REPLACE TRIGGER set_timestamp_compte_securite
    BEFORE UPDATE ON p2p.compte_securite
    FOR EACH ROW EXECUTE FUNCTION p2p.trigger_set_timestamp();

-- =======================================================================================
-- Table : Categorie de Service (Domaine Marketplace)
-- ========================================================================================
CREATE TABLE IF NOT EXISTS p2p.categorie_service (
/*******************************************************************
* La catégorie de service identifiée par "id_categorie", nommée
* "nom_service" avec la description "description_service"
*******************************************************************/
    id_categorie        UUID PRIMARY KEY NOT NULL,
    nom_service         VARCHAR(100) NOT NULL UNIQUE,
    description_service TEXT,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE OR REPLACE TRIGGER set_timestamp_categorie
    BEFORE UPDATE ON p2p.categorie_service
    FOR EACH ROW EXECUTE FUNCTION p2p.trigger_set_timestamp();

-- =======================================================================================
-- Table : Prestataire
-- ========================================================================================
CREATE TABLE IF NOT EXISTS p2p.prestataire (
/*******************************************************************
* Le prestataire idéntifié par "id_prestataire" associe au compte de
* securite "id_media_com", caractérise par le badge actuel "badge_actuel"
* modifié le "date_modif_badge", avec une date d'expiration ou de reaudit
* obligatoire fixée au "date_expiration_badge", possédant un score de reputation
* transferable de "srt_score", un compteur de "compteur_missions_sans_litige" et
* ayant pour état de disponibilité "statut_activite"
*******************************************************************/
    id_prestataire                  UUID    PRIMARY KEY NOT NULL ,
    id_media_com                    UUID    NOT NULL ,
    badge_actuel                    p2p.enum_badge_type NOT NULL DEFAULT 'GRIS'::p2p.enum_badge_type,
    date_modif_badge                TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_expiration_badge           TIMESTAMPTZ NOT NULL ,
    srt_score                       NUMERIC(10, 4) NOT NULL DEFAULT 0.0000,
    compteur_missions_sans_litige   INTEGER NOT NULL DEFAULT 0,
    statut_activite                 p2p.enum_statut_activite NOT NULL DEFAULT 'DISPONIBLE'::p2p.enum_statut_activite,
    created_at                      TIMESTAMPTZ          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                      TIMESTAMPTZ          NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT prestataire_fk_compte_securite
        FOREIGN KEY (id_media_com)
        REFERENCES p2p.compte_securite(id_utilisateur)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT prestataire_srt_score_valid
        CHECK ( srt_score >= 0 ),

    CONSTRAINT prestataire_compteur_missions_sans_litige_valid
        CHECK ( compteur_missions_sans_litige >= 0 )
);

CREATE INDEX IF NOT EXISTS idx_prestataire_id_media_com ON p2p.prestataire(id_media_com);

CREATE OR REPLACE TRIGGER set_timestamp_prestataire
    BEFORE UPDATE ON p2p.prestataire
    FOR EACH ROW EXECUTE FUNCTION p2p.trigger_set_timestamp();

-- =======================================================================================
-- Table : Prestataire Competence (Mapping Artisan <-> Métier)
-- ========================================================================================
CREATE TABLE IF NOT EXISTS p2p.prestataire_competence (
/*******************************************************************
* La compétence du prestataire liant le prestataire "id_prestataire"
* à la catégorie de service "id_categorie", validée le "created_at"
*******************************************************************/
    id_prestataire  UUID NOT NULL,
    id_categorie    UUID NOT NULL,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_prestataire, id_categorie),

    CONSTRAINT fk_competence_prestataire
        FOREIGN KEY (id_prestataire) REFERENCES p2p.prestataire(id_prestataire) ON DELETE CASCADE,
    CONSTRAINT fk_competence_categorie
        FOREIGN KEY (id_categorie) REFERENCES p2p.categorie_service(id_categorie) ON DELETE CASCADE
);

-- =======================================================================================
-- Table : Prestataire tracking
-- ========================================================================================
CREATE TABLE IF NOT EXISTS p2p.prestataire_tracking (
/*******************************************************************
* Le suivi géographique du prestataire identifié par "id_prestataire",
* enregistrant sa position physique par la latitude "latitude_gps" et
* la longitude "longitude_gps", relevées à l'instant précis défini par
* l'horodatage "horodatage_gps"
*******************************************************************/
    id_tracking     UUID PRIMARY KEY NOT NULL ,
    id_prestataire  UUID NOT NULL ,
    latitude_gps    NUMERIC(10, 6) NOT NULL ,
    longitude_gps   NUMERIC(10, 6) NOT NULL ,
    horodatage_gps  TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT prestataire_tracking_fk_prestataire
        FOREIGN KEY (id_prestataire)
        REFERENCES p2p.prestataire(id_prestataire)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT prestataire_tracking_latitude_gps_valid
        CHECK ( latitude_gps BETWEEN -90 AND 90 ),
    CONSTRAINT prestataire_tracking_longitude_gps_valid
        CHECK ( longitude_gps BETWEEN -180 AND 180 )
);

CREATE INDEX IF NOT EXISTS idx_tracking_prestataire_time ON p2p.prestataire_tracking(id_prestataire, horodatage_gps DESC);

-- =======================================================================================
-- Table : Client
-- ========================================================================================
CREATE TABLE IF NOT EXISTS p2p.client (
/*******************************************************************
* Le client identifié par "id_client" associé au compte de sécurité
* "id_media_com", caractérisé par son type de profil "type_client"
*******************************************************************/
    id_client               UUID    PRIMARY KEY NOT NULL ,
    id_media_com            UUID    NOT NULL ,
    type_client             p2p.enum_type_client NOT NULL ,
    created_at              TIMESTAMPTZ          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMPTZ          NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT client_fk_compte_securite
        FOREIGN KEY (id_media_com)
        REFERENCES p2p.compte_securite(id_utilisateur)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_client_id_media_com ON p2p.client(id_media_com);

CREATE OR REPLACE TRIGGER set_timestamp_client
    BEFORE UPDATE ON p2p.client
    FOR EACH ROW EXECUTE FUNCTION p2p.trigger_set_timestamp();

-- =======================================================================================
-- Table : Client Evenement Fiabilite (Journal d'audit comportemental)
-- ========================================================================================
CREATE TABLE IF NOT EXISTS p2p.client_evenement_fiabilite (
/*******************************************************************
* L'événement de fiabilité identifié par "id_evenement" associé au client
* "id_client", de type "type_evenement", ayant un impact "score_impact"
* sur sa fiabilité, décrit par "description" et survenu le "created_at"
*******************************************************************/
    id_evenement    UUID PRIMARY KEY NOT NULL,
    id_client       UUID NOT NULL,
    type_evenement  VARCHAR(50) NOT NULL, -- Ex: ANNULATION_TARDIVE, NON_PAIEMENT, MISSION_REUSSIE
    score_impact    NUMERIC(5, 2) NOT NULL, -- Ex: -2.5 ou +1.0
    description     TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT evenement_fk_client
        FOREIGN KEY (id_client) REFERENCES p2p.client(id_client) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_evenement_client ON p2p.client_evenement_fiabilite(id_client);

-- =======================================================================================
-- Table : Mission (Enrichie du Domaine Métier)
-- ========================================================================================
CREATE TABLE IF NOT EXISTS p2p.mission (
/*******************************************************************
* La mission identifiée par "id_mission" initiée par le client "id_client",
* assignée au prestataire "id_prestataire", s'exécutant selon le mode
* "mode_execution", caractérisée par son état d'avancement "statut_lifecycle",
* son code de jumelage unique "code_jumelage", ses données métiers ("titre",
* "adresse_intervention", "prix_estime"), son horodatage de démarrage planifié
* "horodatage_prevu", son horodatage d'achèvement réel "horodatage_fin"
* et son horodatage de fin de couverture technique défini par "expiration_garantie"
*******************************************************************/
    id_mission          UUID    PRIMARY KEY NOT NULL ,
    id_client           UUID    NOT NULL ,
    id_prestataire      UUID    ,
    id_categorie        UUID    NOT NULL , -- Lien vers le métier requis
    titre               VARCHAR(150) NOT NULL,
    description         TEXT,
    adresse_intervention TEXT NOT NULL,
    prix_estime         NUMERIC(12, 2) NOT NULL,
    prix_final          NUMERIC(12, 2),
    niveau_urgence      VARCHAR(25) NOT NULL DEFAULT 'NORMAL',
    mode_execution      p2p.enum_mode_execution NOT NULL ,
    statut_lifecycle    p2p.enum_statut_lifecycle NOT NULL ,
    code_jumelage       VARCHAR(25) UNIQUE NOT NULL ,
    horodatage_prevu    TIMESTAMPTZ NOT NULL ,
    horodatage_fin      TIMESTAMPTZ ,
    expiration_garantie TIMESTAMPTZ ,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at          TIMESTAMPTZ , -- Soft Delete

    CONSTRAINT mission_fk_client
        FOREIGN KEY (id_client) REFERENCES p2p.client(id_client) ON DELETE CASCADE ON UPDATE CASCADE ,

    CONSTRAINT mission_fk_prestataire
        FOREIGN KEY (id_prestataire) REFERENCES p2p.prestataire(id_prestataire) ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT mission_fk_categorie
        FOREIGN KEY (id_categorie) REFERENCES p2p.categorie_service(id_categorie) ON DELETE RESTRICT,

    CONSTRAINT mission_prix_estime_valid
        CHECK (prix_estime >= 0),

    -- Contraintes Opérationnelles Temporelles Cruciales
    CONSTRAINT mission_chronologie_fin_valid
        CHECK (horodatage_fin IS NULL OR horodatage_fin >= horodatage_prevu),

    CONSTRAINT mission_chronologie_garantie_valid
        CHECK (expiration_garantie IS NULL OR expiration_garantie >= horodatage_fin),

    CONSTRAINT chk_niveau_urgence
        CHECK (niveau_urgence IN ('NORMAL', 'HAUT', 'CRITIQUE'))
);

CREATE INDEX IF NOT EXISTS idx_mission_id_client ON p2p.mission(id_client);
CREATE INDEX IF NOT EXISTS idx_mission_id_prestataire ON p2p.mission(id_prestataire);
CREATE UNIQUE INDEX IF NOT EXISTS uq_active_mission_prestataire ON p2p.mission(id_prestataire) WHERE statut_lifecycle IN ('EN_COURS', 'ASSIGNEE') AND deleted_at IS NULL;

CREATE OR REPLACE TRIGGER set_timestamp_mission
    BEFORE UPDATE ON p2p.mission
    FOR EACH ROW EXECUTE FUNCTION p2p.trigger_set_timestamp();

-- =======================================================================================
-- Table : Evaluation (Notation et Reviews)
-- ========================================================================================
CREATE TABLE IF NOT EXISTS p2p.evaluation (
/*******************************************************************
* L'évaluation identifiée par "id_evaluation" laissée pour la mission
* "id_mission" avec une note "note" sur 5 et un commentaire "commentaire"
*******************************************************************/
    id_evaluation   UUID PRIMARY KEY NOT NULL,
    id_mission      UUID NOT NULL UNIQUE,
    note            INTEGER NOT NULL,
    commentaire     TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT evaluation_fk_mission
        FOREIGN KEY (id_mission) REFERENCES p2p.mission(id_mission) ON DELETE CASCADE,
    CONSTRAINT chk_note_valid
        CHECK (note BETWEEN 1 AND 5)
);

-- =======================================================================================
-- Table : Portefeuille (Wallet Physique)
-- ========================================================================================
CREATE TABLE IF NOT EXISTS p2p.portefeuille (
/*******************************************************************
* Le portefeuille financier identifié par "id_portefeuille" appartenant
* à l'utilisateur "id_utilisateur" avec un solde courant "solde_courant"
*******************************************************************/
    id_portefeuille UUID PRIMARY KEY NOT NULL,
    id_utilisateur  UUID NOT NULL UNIQUE,
    solde_courant   NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT portefeuille_fk_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES p2p.utilisateur(id_utilisateur) ON DELETE RESTRICT
);

CREATE OR REPLACE TRIGGER set_timestamp_portefeuille
    BEFORE UPDATE ON p2p.portefeuille
    FOR EACH ROW EXECUTE FUNCTION p2p.trigger_set_timestamp();

-- =======================================================================================
-- Table : Transaction Financiere (Grand Livre / Ledger)
-- ========================================================================================
CREATE TABLE IF NOT EXISTS p2p.transaction_financiere (
/*******************************************************************
* La transaction financière identifiée par "id_transaction" affectant le
* portefeuille "id_portefeuille" d'un montant "montant", de type
* "type_transaction" avec la référence externe "reference_externe"
*******************************************************************/
    id_transaction      UUID PRIMARY KEY NOT NULL,
    id_portefeuille     UUID NOT NULL,
    montant             NUMERIC(12, 2) NOT NULL, -- Positif (Crédit) ou Négatif (Débit)
    type_transaction    VARCHAR(50) NOT NULL, -- Ex: DEPOT, RETRAIT, PAIEMENT_MISSION, REMBOURSEMENT
    reference_externe   VARCHAR(100), -- ID de transaction Mobile Money
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT transaction_fk_portefeuille
        FOREIGN KEY (id_portefeuille) REFERENCES p2p.portefeuille(id_portefeuille) ON DELETE RESTRICT,

    CONSTRAINT chk_type_transaction
        CHECK (type_transaction IN ('DEPOT', 'RETRAIT', 'PAIEMENT_MISSION', 'REMBOURSEMENT', 'COMMISSION_PLATEFORME'))
);

CREATE INDEX IF NOT EXISTS idx_transaction_portefeuille ON p2p.transaction_financiere(id_portefeuille);


-- =======================================================================================
-- Table : Mission verification terrain
-- ========================================================================================

CREATE TABLE IF NOT EXISTS p2p.mission_verification_terrain (
/*******************************************************************
* La vérification terrain de la mission identifiée par "id_mission",
* enregistrant les coordonnées géographiques théoriques de l'intervention
* par la latitude "gps_theorique_lat" et la longitude "gps_theorique_lon",
* ainsi que la position physique réelle du prestataire lors du check-in
* par la latitude "gps_checkin_lat" et la longitude "gps_checkin_lon",
* validée à l'instant précis défini par l'horodatage "horodatage_checkin"
*******************************************************************/
    id_mission          UUID PRIMARY KEY NOT NULL ,
    gps_theorique_lat   NUMERIC(10, 6) NOT NULL ,
    gps_theorique_lon   NUMERIC(10, 6) NOT NULL ,
    gps_checkin_lat     NUMERIC(10, 6) ,
    gps_checkin_lon     NUMERIC(10, 6) ,
    horodatage_checkin  TIMESTAMPTZ ,

    CONSTRAINT verification_terrain_fk_mission
        FOREIGN KEY (id_mission)
        REFERENCES p2p.mission(id_mission)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- =======================================================================================
-- Table : Fichier preuve
-- ========================================================================================

CREATE TABLE IF NOT EXISTS p2p.fichier_preuve (
/*******************************************************************
* Le fichier physique de preuve identifié par "id_preuve", lié à la
* mission "id_mission", stocké à l'adresse sécurisée "lien_stockage"
* et téléversé à l'instant précis défini par l'horodatage "horodatage_capture"
*******************************************************************/
    id_preuve           UUID PRIMARY KEY NOT NULL ,
    id_mission          UUID NOT NULL ,
    lien_stockage       VARCHAR(512) NOT NULL ,
    horodatage_capture  TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fichier_preuve_fk_mission
        FOREIGN KEY (id_mission)
        REFERENCES p2p.mission(id_mission)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_fichier_preuve_id_mission ON p2p.fichier_preuve(id_mission);


-- =======================================================================================
-- Table : Validation preuve
-- ========================================================================================

CREATE TABLE IF NOT EXISTS p2p.validation_preuve (
/*******************************************************************
* La validation de la preuve identifiée par "id_preuve", qualifiée par
* son type de flux technique "type_flux", ayant pour résultat d'évaluation
* de conformité "resultat_validation" et arbitrée par le validateur
* système ou l'expert identifié par "id_validateur"
*******************************************************************/
    id_preuve           UUID PRIMARY KEY NOT NULL ,
    type_flux           p2p.enum_type_flux NOT NULL ,
    resultat_validation p2p.enum_resultat_validation NOT NULL ,
    id_validateur       UUID NOT NULL ,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT validation_preuve_fk_fichier
        FOREIGN KEY (id_preuve)
        REFERENCES p2p.fichier_preuve(id_preuve)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT validation_preuve_fk_utilisateur
        FOREIGN KEY (id_validateur)
        REFERENCES p2p.utilisateur(id_utilisateur)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_validation_preuve_id_validateur ON p2p.validation_preuve(id_validateur);


-- =======================================================================================
-- Table : Livret financier
-- ========================================================================================

CREATE TABLE IF NOT EXISTS p2p.livret_financier (
/*******************************************************************
* Le livret financier de séquestre identifié par "id_livret", adossé
* de manière univoque à la mission "id_mission", enregistrant le montant
* total engagé par le client "montant_total", caractérisé par son état
* de verrouillage "statut_sequestre", lié à la référence de la passerelle
* de paiement mobile "ref_transaction", bloqué à l'instant "horodatage_verrouillage"
* et reversé à l'instant précis défini par l'horodatage "horodatage_liberation"
*******************************************************************/
    id_livret               UUID PRIMARY KEY NOT NULL ,
    id_mission              UUID UNIQUE NOT NULL ,
    montant_total           NUMERIC(12, 2) NOT NULL ,
    statut_sequestre        p2p.enum_statut_sequestre NOT NULL DEFAULT 'BLOQUE'::p2p.enum_statut_sequestre,
    ref_transaction         VARCHAR(100) NOT NULL ,
    horodatage_verrouillage TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    horodatage_liberation   TIMESTAMP ,
    created_at              TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT livret_financier_fk_mission
        FOREIGN KEY (id_mission)
        REFERENCES p2p.mission(id_mission)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT livret_financier_montant_total_valid
        CHECK ( montant_total >= 0 )
);

CREATE OR REPLACE TRIGGER set_timestamp_livret_financier
    BEFORE UPDATE ON p2p.livret_financier
    FOR EACH ROW EXECUTE FUNCTION p2p.trigger_set_timestamp();

-- =======================================================================================
-- Table : Litige
-- ========================================================================================

CREATE TABLE IF NOT EXISTS p2p.litige (
/*******************************************************************
* Le litige identifié par "id_litige" associé à la mission "id_mission",
* caractérisé par son type d'anomalie ou de comportement défaillant
* "type_anomalie", ayant pour état d'avancement d'arbitrage "statut_litige",
* affecté à l'administrateur ou arbitre "id_arbitre", contenant la résolution finale
* et les notes d'arbitrage "decision_notes", ayant pour valeur de malus de réputation
* calculée "malus_srt" et enregistré à l'instant précis défini par son horodatage de
* déclenchement "horodatage_declenchement"
*******************************************************************/
    id_litige                   UUID PRIMARY KEY NOT NULL ,
    id_mission                  UUID UNIQUE NOT NULL ,
    type_anomalie               p2p.enum_type_anomalie NOT NULL ,
    statut_litige               p2p.enum_statut_litige NOT NULL DEFAULT 'OUVERT'::p2p.enum_statut_litige,
    id_arbitre                  UUID ,
    decision_notes              TEXT ,
    malus_srt                   NUMERIC(10, 4) NOT NULL DEFAULT 0.0000,
    horodatage_declenchement    TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                  TIMESTAMPTZ ,

    CONSTRAINT litige_fk_mission
        FOREIGN KEY (id_mission)
        REFERENCES p2p.mission(id_mission)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT litige_malus_srt_valid
        CHECK ( malus_srt >= 0 ),

    CONSTRAINT litige_fk_arbitre
        FOREIGN KEY (id_arbitre)
        REFERENCES p2p.utilisateur(id_utilisateur)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_litige_id_arbitre ON p2p.litige(id_arbitre);

CREATE OR REPLACE TRIGGER set_timestamp_litige
    BEFORE UPDATE ON p2p.litige
    FOR EACH ROW EXECUTE FUNCTION p2p.trigger_set_timestamp();


-- =======================================================================================
-- Table : Gouvernance action
-- ========================================================================================

CREATE TABLE IF NOT EXISTS p2p.gouvernance_action (
/*******************************************************************
* L'action de gouvernance critique identifiée par "id_action_gouv",
* qualifiée par son type d'intervention structurelle "type_action",
* contenant les données brutes de mutation système "payload_brut",
* caractérisée par son état de cycle de signature "statut_action"
* et enregistrée ou exécutée à l'horodatage "horodatage_creation_execution"
*******************************************************************/
    id_action_gouv                  UUID PRIMARY KEY NOT NULL ,
    type_action                     p2p.enum_type_action_gouv NOT NULL ,
    payload_brut                    TEXT NOT NULL ,
    statut_action                   p2p.enum_statut_action_gouv NOT NULL DEFAULT 'EN_ATTENTE_SIGNATURES'::p2p.enum_statut_action_gouv,
    horodatage_creation_execution   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                      TIMESTAMPTZ
);


-- =======================================================================================
-- Table : Gouvernance signature
-- ========================================================================================

CREATE TABLE IF NOT EXISTS p2p.gouvernance_signature (
/*******************************************************************
* La signature de gouvernance identifiée par l'action de modification
* critique "id_action_gouv" et validée individuellement par la clé du
* membre fondateur unique "cle_fondateur"
*******************************************************************/
    id_action_gouv  UUID NOT NULL ,
    cle_fondateur   VARCHAR(50) NOT NULL ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_action_gouv, cle_fondateur),

    CONSTRAINT gouvernance_signature_fk_action
        FOREIGN KEY (id_action_gouv)
        REFERENCES p2p.gouvernance_action(id_action_gouv)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- =======================================================================================
-- Fin transaction
-- ========================================================================================
COMMIT;
