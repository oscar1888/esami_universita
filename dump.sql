--
-- PostgreSQL database dump
--

-- Dumped from database version 12.4 (Ubuntu 12.4-0ubuntu0.20.04.1)
-- Dumped by pg_dump version 12.4 (Ubuntu 12.4-0ubuntu0.20.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: esami_universita; Type: SCHEMA; Schema: -; Owner: bdlab
--

CREATE SCHEMA esami_universita;


ALTER SCHEMA esami_universita OWNER TO bdlab;

--
-- Name: codice_cdl; Type: DOMAIN; Schema: esami_universita; Owner: bdlab
--

CREATE DOMAIN esami_universita.codice_cdl AS character(3)
	CONSTRAINT codice_cdl_check CHECK ((VALUE ~ '^[A-Z][0-9][A-Z]$'::text));


ALTER DOMAIN esami_universita.codice_cdl OWNER TO bdlab;

--
-- Name: sesso; Type: DOMAIN; Schema: esami_universita; Owner: bdlab
--

CREATE DOMAIN esami_universita.sesso AS character(1)
	CONSTRAINT sesso_check CHECK (((VALUE = 'M'::bpchar) OR (VALUE = 'F'::bpchar)));


ALTER DOMAIN esami_universita.sesso OWNER TO bdlab;

--
-- Name: check_anno_previsto(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_anno_previsto() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		type_laurea VARCHAR;
	BEGIN
		SELECT c.tipo INTO type_laurea FROM corso_di_laurea c WHERE c.id = NEW.corso_di_laurea;
		IF NEW.anno_previsto > 3 OR NEW.anno_previsto < 1 OR (type_laurea = 'Magistrale' AND NEW.anno_previsto = 3) THEN
			RAISE EXCEPTION 'Non è possibile inserire un insegnamento in un anno non previsto dal tipo di corso di laurea (Triennale/Magistrale).';
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;
$$;


ALTER FUNCTION esami_universita.check_anno_previsto() OWNER TO bdlab;

--
-- Name: check_data_esame(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_data_esame() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		
	BEGIN
		IF NEW.data <= CURRENT_DATE THEN
			RAISE EXCEPTION 'La data dell''esame deve essere posteriore al giorno corrente.';
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;
$$;


ALTER FUNCTION esami_universita.check_data_esame() OWNER TO bdlab;

--
-- Name: check_docente_max_insegnamenti(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_docente_max_insegnamenti() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		anno_acc CHAR(9);
		num_ins INTEGER;
	BEGIN
		SELECT anno_accademico_offerta INTO anno_acc FROM corso_di_laurea c WHERE c.id = NEW.corso_di_laurea;
		SELECT count(*) INTO num_ins FROM insegnamento i INNER JOIN corso_di_laurea c ON i.corso_di_laurea = c.id WHERE i.docente = NEW.docente AND c.anno_accademico_offerta = anno_acc;
		IF num_ins >= 3 THEN
			RAISE EXCEPTION 'Non è possibile inserire un docente che è già responsabile di 3 insegnamenti.';
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;
$$;


ALTER FUNCTION esami_universita.check_docente_max_insegnamenti() OWNER TO bdlab;

--
-- Name: check_esame(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_esame() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		the_year INTEGER;
		cdl insegnamento.corso_di_laurea%TYPE;
	BEGIN
		SELECT anno_previsto INTO the_year FROM insegnamento i WHERE NEW.insegnamento = i.id;
		SELECT corso_di_laurea INTO cdl FROM insegnamento i WHERE NEW.insegnamento = i.id;
		PERFORM * FROM esame e INNER JOIN insegnamento i ON i.id = e.insegnamento WHERE corso_di_laurea = cdl AND anno_previsto = the_year AND data = NEW.data;
		IF FOUND THEN
			RAISE EXCEPTION 'Non si può programmare un esame nella stessa giornata di un altro esame di un insegnamento dello stesso corso di laurea previsto per lo stesso anno.';
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;
$$;


ALTER FUNCTION esami_universita.check_esame() OWNER TO bdlab;

--
-- Name: check_esame_cdl_attivato(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_esame_cdl_attivato() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		activated BOOLEAN;
		cdl corso_di_laurea.id%TYPE;
	BEGIN
		SELECT corso_di_laurea INTO cdl FROM insegnamento WHERE id = NEW.insegnamento;
		SELECT attivato INTO activated FROM corso_di_laurea WHERE id = cdl;
		IF NOT activated THEN
			RAISE EXCEPTION 'Non è possibile creare un esame di un insegnamento appartenente ad un corso di laurea non attivato.';
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;

$$;


ALTER FUNCTION esami_universita.check_esame_cdl_attivato() OWNER TO bdlab;

--
-- Name: check_iscrizione(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_iscrizione() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		cdl_studente corso_di_laurea.id%TYPE;
		cdl_insegnamento corso_di_laurea.id%TYPE;
	BEGIN
		SELECT corso_di_laurea INTO cdl_studente FROM studente_corrente s WHERE s.matricola = NEW.matricola;
		SELECT corso_di_laurea INTO cdl_insegnamento FROM insegnamento i WHERE i.id = NEW.insegnamento;
		IF cdl_studente <> cdl_insegnamento THEN
			RAISE EXCEPTION 'Non si può effettuare un''iscrizione ad un esame di un insegnamento che non appartiene al tuo corso di laurea.';
			RETURN NULL;
		END IF;
		PERFORM insegnamento_1 FROM propedeuticita WHERE insegnamento_2 = NEW.insegnamento EXCEPT SELECT insegnamento FROM carriera WHERE matricola = NEW.matricola AND voto >= 18;
		IF FOUND THEN
			RAISE EXCEPTION 'Non si può effettuare un''iscrizione ad un esame di un insegnamento per il quale non si sono rispettate le propedeuticita.';
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;
$$;


ALTER FUNCTION esami_universita.check_iscrizione() OWNER TO bdlab;

--
-- Name: check_iscrizione_gia_verb(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_iscrizione_gia_verb() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE

	BEGIN
		PERFORM * FROM carriera WHERE matricola = NEW.matricola AND data = NEW.data AND insegnamento = NEW.insegnamento;
		IF FOUND THEN
			RAISE EXCEPTION 'Lo studente ha già verbalizzato un voto in questo esame.';
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;
$$;


ALTER FUNCTION esami_universita.check_iscrizione_gia_verb() OWNER TO bdlab;

--
-- Name: check_iscrizione_iscr_aperte(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_iscrizione_iscr_aperte() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		cond BOOLEAN;
	BEGIN
		SELECT iscrizioni_aperte INTO cond FROM esame WHERE insegnamento = NEW.insegnamento AND data = NEW.data;
		IF NOT cond THEN
			RAISE EXCEPTION 'Non è possibile effettuare un''iscrizione ad un esame con le iscrizioni chiuse.';
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;
$$;


ALTER FUNCTION esami_universita.check_iscrizione_iscr_aperte() OWNER TO bdlab;

--
-- Name: check_on_delete_cdl(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_on_delete_cdl() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		cond BOOLEAN;
	BEGIN
		SELECT attivato INTO cond FROM corso_di_laurea WHERE id = OLD.id;
		IF cond THEN
			RAISE EXCEPTION 'Non è possibile eliminare il corso di laurea perché è stato già attivato.';
			RETURN NULL;
		END IF;
		RETURN OLD;
	END;

$$;


ALTER FUNCTION esami_universita.check_on_delete_cdl() OWNER TO bdlab;

--
-- Name: check_on_delete_docente(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_on_delete_docente() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE

	BEGIN
		PERFORM * FROM insegnamento WHERE docente = OLD.username;
		IF FOUND THEN
			RAISE EXCEPTION 'Non è possibile eliminare il docente perché ha almeno un insegnamento di cui è responsabile.';
			RETURN NULL;
		END IF;
		RETURN OLD;
	END;

$$;


ALTER FUNCTION esami_universita.check_on_delete_docente() OWNER TO bdlab;

--
-- Name: check_on_delete_esame(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_on_delete_esame() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE

	BEGIN
		PERFORM * FROM carriera_completa WHERE id = OLD.insegnamento AND data = OLD.data;
		IF FOUND THEN
			RAISE EXCEPTION 'Non è possibile effettuare l''eliminazione perché è stato già verbalizzato un voto di questo esame.';
			RETURN NULL;
		END IF;
		RETURN OLD;
	END;

$$;


ALTER FUNCTION esami_universita.check_on_delete_esame() OWNER TO bdlab;

--
-- Name: check_on_delete_insegnamento(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_on_delete_insegnamento() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		cond BOOLEAN;
	BEGIN
		SELECT attivato INTO cond FROM corso_di_laurea WHERE id = OLD.corso_di_laurea;
		IF cond THEN
			RAISE EXCEPTION 'Non è possibile eliminare l''insegnamento perché appartiene ad un corso di laurea attivato.';
			RETURN NULL;
		END IF;
		RETURN OLD;
	END;

$$;


ALTER FUNCTION esami_universita.check_on_delete_insegnamento() OWNER TO bdlab;

--
-- Name: check_profilo_utente_docente(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_profilo_utente_docente() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		role varchar;
	BEGIN
		SELECT ruolo INTO role FROM profilo_utente WHERE username = NEW.username;
		IF role <> 'Docente' THEN
			RAISE EXCEPTION 'Non è possibile assegnare questo profilo utente.';
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;
$$;


ALTER FUNCTION esami_universita.check_profilo_utente_docente() OWNER TO bdlab;

--
-- Name: check_profilo_utente_storico_studenti(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_profilo_utente_storico_studenti() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		cond BOOLEAN;
		role varchar;
	BEGIN
		PERFORM * FROM studente_corrente WHERE username = NEW.username;
		cond := FOUND;
		SELECT ruolo INTO role FROM profilo_utente WHERE username = NEW.username;
		IF cond OR role <> 'Studente' THEN
			DELETE FROM storico_studenti WHERE matricola = NEW.matricola;
			RAISE EXCEPTION 'Non è possibile assegnare questo profilo utente.';
		END IF;
		RETURN NULL;
	END;
$$;


ALTER FUNCTION esami_universita.check_profilo_utente_storico_studenti() OWNER TO bdlab;

--
-- Name: check_profilo_utente_studente(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_profilo_utente_studente() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		cond BOOLEAN;
		role varchar;
	BEGIN
		PERFORM * FROM storico_studenti WHERE username = NEW.username;
		cond := FOUND;
		SELECT ruolo INTO role FROM profilo_utente WHERE username = NEW.username;
		IF cond OR role <> 'Studente' THEN
			RAISE EXCEPTION 'Non è possibile assegnare questo profilo utente.';
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;
$$;


ALTER FUNCTION esami_universita.check_profilo_utente_studente() OWNER TO bdlab;

--
-- Name: check_propedeuticita(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_propedeuticita() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		cdl1 insegnamento.corso_di_laurea%TYPE;
		cdl2 insegnamento.corso_di_laurea%TYPE;
		anno_prev1 INTEGER;
		anno_prev2 INTEGER;
		sem1 INTEGER;
		sem2 INTEGER;
	BEGIN
		SELECT corso_di_laurea, anno_previsto, semestre INTO cdl1, anno_prev1, sem1 FROM insegnamento WHERE id = NEW.insegnamento_1;
		SELECT corso_di_laurea, anno_previsto, semestre INTO cdl2, anno_prev2, sem2 FROM insegnamento WHERE id = NEW.insegnamento_2;
		IF cdl1 <> cdl2 THEN
			RAISE EXCEPTION 'Non è possibile definire propedeuticita su due insegnamenti di corsi di laurea differenti.';
			RETURN NULL;
		END IF;
		IF anno_prev1 > anno_prev2 THEN
			RAISE EXCEPTION 'Non è possibile rendere propedeutico un insegnamento con anno previsto % ad un insegnamento con anno previsto %.', anno_prev1, anno_prev2;
			RETURN NULL;
		END IF;
		IF anno_prev1 = anno_prev2 AND sem1 >= sem2 THEN
			RAISE EXCEPTION 'Non è possibile rendere propedeutico un insegnamento con anno previsto % e semestre previsto % ad un insegnamento con anno previsto % e semestre previsto %.', anno_prev1, sem1, anno_prev2, sem2;
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;

$$;


ALTER FUNCTION esami_universita.check_propedeuticita() OWNER TO bdlab;

--
-- Name: check_stor_stud_matr(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_stor_stud_matr() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE

	BEGIN
		PERFORM * FROM studente_corrente WHERE matricola = NEW.matricola;
		IF FOUND THEN
			DELETE FROM storico_studenti WHERE matricola = NEW.matricola;
			RAISE EXCEPTION 'La matricola dello studente inserito appartiene ad uno studente corrente.';
		END IF;
		RETURN NULL;
	END;
$$;


ALTER FUNCTION esami_universita.check_stor_stud_matr() OWNER TO bdlab;

--
-- Name: check_storico_studenti_cdl_attivato(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_storico_studenti_cdl_attivato() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		cond BOOLEAN;
	BEGIN
		SELECT attivato INTO cond FROM corso_di_laurea WHERE id = NEW.corso_di_laurea;
		IF NOT cond THEN
			RAISE EXCEPTION 'Il corso di laurea associato allo studente non è stato mai attivato.';
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;

$$;


ALTER FUNCTION esami_universita.check_storico_studenti_cdl_attivato() OWNER TO bdlab;

--
-- Name: check_stud_corr_matr(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_stud_corr_matr() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE

	BEGIN
		PERFORM * FROM storico_studenti WHERE matricola = NEW.matricola;
		IF FOUND THEN
			RAISE EXCEPTION 'La matricola inserita appartiene ad un ex-studente.';
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;

$$;


ALTER FUNCTION esami_universita.check_stud_corr_matr() OWNER TO bdlab;

--
-- Name: check_studente_cdl_attivato(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.check_studente_cdl_attivato() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		cond BOOLEAN;
	BEGIN
		SELECT attivato INTO cond FROM corso_di_laurea WHERE id = NEW.corso_di_laurea;
		IF NOT cond THEN
			RAISE EXCEPTION 'Il corso di laurea a cui si vuole iscrivere lo studente non è stato ancora attivato.';
			RETURN NULL;
		END IF;
		RETURN NEW;
	END;

$$;


ALTER FUNCTION esami_universita.check_studente_cdl_attivato() OWNER TO bdlab;

--
-- Name: delete_actual_student(); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.delete_actual_student() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE
		current_year INTEGER;
	BEGIN
		SELECT EXTRACT(YEAR FROM CURRENT_DATE)::INTEGER INTO current_year;
		INSERT INTO storico_studenti VALUES (OLD.matricola, OLD.username, OLD.nome, OLD.cognome, OLD.sesso, OLD.data_nascita, OLD.corso_di_laurea, NULL, current_year);
		INSERT INTO storico_carriera SELECT * FROM carriera WHERE matricola = OLD.matricola;
		DELETE FROM carriera WHERE matricola = OLD.matricola;
		RETURN OLD;
	END;
$$;


ALTER FUNCTION esami_universita.delete_actual_student() OWNER TO bdlab;

--
-- Name: verbalizzazione(integer, date, character varying, integer, boolean); Type: FUNCTION; Schema: esami_universita; Owner: bdlab
--

CREATE FUNCTION esami_universita.verbalizzazione(student_code integer, exam_date date, course character varying, voto integer, assente boolean) RETURNS void
    LANGUAGE plpgsql
    AS $$
	DECLARE
		iscr iscrizione%ROWTYPE;
	BEGIN
		SELECT * INTO iscr FROM iscrizione WHERE matricola = student_code AND data = exam_date AND insegnamento = course;
		IF iscr IS NULL THEN
			RAISE EXCEPTION 'Non esiste alcun studente con la matricola inserita iscritto a questo esame.';
			RETURN;
		END IF;
		IF NOT assente THEN
			INSERT INTO carriera VALUES (iscr.matricola, iscr.data, iscr.insegnamento, voto);
		END IF;
		DELETE FROM iscrizione WHERE matricola = student_code AND data = exam_date AND insegnamento = course;
	END;
$$;


ALTER FUNCTION esami_universita.verbalizzazione(student_code integer, exam_date date, course character varying, voto integer, assente boolean) OWNER TO bdlab;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: carriera; Type: TABLE; Schema: esami_universita; Owner: bdlab
--

CREATE TABLE esami_universita.carriera (
    matricola integer NOT NULL,
    data date NOT NULL,
    insegnamento character varying NOT NULL,
    voto integer NOT NULL,
    CONSTRAINT carriera_voto_check CHECK (((voto >= 0) AND (voto <= 30)))
);


ALTER TABLE esami_universita.carriera OWNER TO bdlab;

--
-- Name: insegnamento; Type: TABLE; Schema: esami_universita; Owner: bdlab
--

CREATE TABLE esami_universita.insegnamento (
    id character varying NOT NULL,
    corso_di_laurea esami_universita.codice_cdl NOT NULL,
    codice_interno character varying NOT NULL,
    nome character varying NOT NULL,
    docente character varying NOT NULL,
    anno_previsto integer NOT NULL,
    semestre integer NOT NULL,
    ore_totali integer NOT NULL,
    lingua character varying DEFAULT 'Italiano'::character varying NOT NULL,
    descrizione_testuale text NOT NULL,
    CONSTRAINT insegnamento_ore_totali_check CHECK ((ore_totali >= 0)),
    CONSTRAINT insegnamento_semestre_check CHECK (((semestre = 1) OR (semestre = 2)))
);


ALTER TABLE esami_universita.insegnamento OWNER TO bdlab;

--
-- Name: storico_carriera; Type: TABLE; Schema: esami_universita; Owner: bdlab
--

CREATE TABLE esami_universita.storico_carriera (
    matricola integer NOT NULL,
    data date NOT NULL,
    insegnamento character varying NOT NULL,
    voto integer NOT NULL,
    CONSTRAINT storico_carriera_voto_check CHECK (((voto >= 0) AND (voto <= 30)))
);


ALTER TABLE esami_universita.storico_carriera OWNER TO bdlab;

--
-- Name: carriera_completa; Type: VIEW; Schema: esami_universita; Owner: bdlab
--

CREATE VIEW esami_universita.carriera_completa AS
 SELECT c.matricola,
    i.id,
    i.nome,
    c.data,
    c.voto
   FROM (esami_universita.carriera c
     JOIN esami_universita.insegnamento i ON (((c.insegnamento)::text = (i.id)::text)))
UNION
 SELECT s.matricola,
    i2.id,
    i2.nome,
    s.data,
    s.voto
   FROM (esami_universita.storico_carriera s
     JOIN esami_universita.insegnamento i2 ON (((s.insegnamento)::text = (i2.id)::text)))
  ORDER BY 1, 4;


ALTER TABLE esami_universita.carriera_completa OWNER TO bdlab;

--
-- Name: carriera_valida; Type: VIEW; Schema: esami_universita; Owner: bdlab
--

CREATE VIEW esami_universita.carriera_valida AS
( SELECT DISTINCT ON (c.matricola, i.id) c.matricola,
    i.id,
    i.nome,
    c.data,
    c.voto
   FROM (esami_universita.carriera c
     JOIN esami_universita.insegnamento i ON (((c.insegnamento)::text = (i.id)::text)))
  WHERE (c.voto >= 18)
  ORDER BY c.matricola, i.id, c.data DESC)
UNION
( SELECT DISTINCT ON (s.matricola, i2.id) s.matricola,
    i2.id,
    i2.nome,
    s.data,
    s.voto
   FROM (esami_universita.storico_carriera s
     JOIN esami_universita.insegnamento i2 ON (((s.insegnamento)::text = (i2.id)::text)))
  WHERE (s.voto >= 18)
  ORDER BY s.matricola, i2.id, s.data DESC)
  ORDER BY 2;


ALTER TABLE esami_universita.carriera_valida OWNER TO bdlab;

--
-- Name: corso_di_laurea; Type: TABLE; Schema: esami_universita; Owner: bdlab
--

CREATE TABLE esami_universita.corso_di_laurea (
    id esami_universita.codice_cdl NOT NULL,
    nome character varying NOT NULL,
    tipo character varying NOT NULL,
    anno_accademico_offerta character(9) NOT NULL,
    facolta character varying NOT NULL,
    descrizione text NOT NULL,
    sede character varying NOT NULL,
    accesso character varying(11) NOT NULL,
    attivato boolean DEFAULT false NOT NULL,
    CONSTRAINT corso_di_laurea_accesso_check CHECK ((((accesso)::text = 'Libero'::text) OR ((accesso)::text = 'Programmato'::text))),
    CONSTRAINT corso_di_laurea_anno_accademico_offerta_check CHECK ((anno_accademico_offerta ~ '^\d{4}-\d{4}$'::text)),
    CONSTRAINT corso_di_laurea_tipo_check CHECK ((((tipo)::text = 'Triennale'::text) OR ((tipo)::text = 'Magistrale'::text)))
);


ALTER TABLE esami_universita.corso_di_laurea OWNER TO bdlab;

--
-- Name: docente; Type: TABLE; Schema: esami_universita; Owner: bdlab
--

CREATE TABLE esami_universita.docente (
    username character varying NOT NULL,
    nome character varying NOT NULL,
    cognome character varying NOT NULL,
    sesso esami_universita.sesso NOT NULL,
    data_nascita date NOT NULL,
    istruzione character varying NOT NULL,
    sede_ufficio character varying NOT NULL
);


ALTER TABLE esami_universita.docente OWNER TO bdlab;

--
-- Name: esame; Type: TABLE; Schema: esami_universita; Owner: bdlab
--

CREATE TABLE esami_universita.esame (
    insegnamento character varying NOT NULL,
    data date NOT NULL,
    orario time without time zone NOT NULL,
    tipo character varying(11) NOT NULL,
    luogo character varying NOT NULL,
    iscrizioni_aperte boolean DEFAULT false NOT NULL,
    CONSTRAINT esame_tipo_check CHECK ((((tipo)::text = 'Scritto'::text) OR ((tipo)::text = 'Orale'::text) OR ((tipo)::text = 'Laboratorio'::text)))
);


ALTER TABLE esami_universita.esame OWNER TO bdlab;

--
-- Name: info_cdl; Type: VIEW; Schema: esami_universita; Owner: bdlab
--

CREATE VIEW esami_universita.info_cdl AS
 SELECT i.corso_di_laurea,
    i.id AS id_insegnamento,
    i.nome,
    i.descrizione_testuale,
    (((d.nome)::text || ' '::text) || (d.cognome)::text) AS docente,
    i.anno_previsto,
    i.semestre,
    i.ore_totali,
    i.lingua
   FROM (esami_universita.insegnamento i
     JOIN esami_universita.docente d ON (((i.docente)::text = (d.username)::text)))
  ORDER BY i.corso_di_laurea, i.nome;


ALTER TABLE esami_universita.info_cdl OWNER TO bdlab;

--
-- Name: iscrizione; Type: TABLE; Schema: esami_universita; Owner: bdlab
--

CREATE TABLE esami_universita.iscrizione (
    matricola integer NOT NULL,
    data date NOT NULL,
    insegnamento character varying NOT NULL
);


ALTER TABLE esami_universita.iscrizione OWNER TO bdlab;

--
-- Name: profilo_utente; Type: TABLE; Schema: esami_universita; Owner: bdlab
--

CREATE TABLE esami_universita.profilo_utente (
    username character varying NOT NULL,
    password character varying NOT NULL,
    ruolo character varying NOT NULL,
    CONSTRAINT profilo_utente_ruolo_check CHECK ((((ruolo)::text = 'Segreteria'::text) OR ((ruolo)::text = 'Docente'::text) OR ((ruolo)::text = 'Studente'::text)))
);


ALTER TABLE esami_universita.profilo_utente OWNER TO bdlab;

--
-- Name: propedeuticita; Type: TABLE; Schema: esami_universita; Owner: bdlab
--

CREATE TABLE esami_universita.propedeuticita (
    insegnamento_1 character varying NOT NULL,
    insegnamento_2 character varying NOT NULL
);


ALTER TABLE esami_universita.propedeuticita OWNER TO bdlab;

--
-- Name: storico_studenti; Type: TABLE; Schema: esami_universita; Owner: bdlab
--

CREATE TABLE esami_universita.storico_studenti (
    matricola integer NOT NULL,
    username character varying NOT NULL,
    nome character varying NOT NULL,
    cognome character varying NOT NULL,
    sesso esami_universita.sesso NOT NULL,
    data_nascita date NOT NULL,
    corso_di_laurea esami_universita.codice_cdl NOT NULL,
    motivo character varying(8),
    anno_rimozione integer NOT NULL,
    CONSTRAINT storico_studenti_matricola_check CHECK (((matricola >= 900000) AND (matricola <= 999999)))
);


ALTER TABLE esami_universita.storico_studenti OWNER TO bdlab;

--
-- Name: studente_corrente; Type: TABLE; Schema: esami_universita; Owner: bdlab
--

CREATE TABLE esami_universita.studente_corrente (
    matricola integer NOT NULL,
    username character varying NOT NULL,
    nome character varying NOT NULL,
    cognome character varying NOT NULL,
    sesso esami_universita.sesso NOT NULL,
    data_nascita date NOT NULL,
    corso_di_laurea esami_universita.codice_cdl NOT NULL,
    CONSTRAINT studente_corrente_matricola_check CHECK (((matricola >= 900000) AND (matricola <= 999999)))
);


ALTER TABLE esami_universita.studente_corrente OWNER TO bdlab;

--
-- Data for Name: carriera; Type: TABLE DATA; Schema: esami_universita; Owner: bdlab
--

COPY esami_universita.carriera (matricola, data, insegnamento, voto) FROM stdin;
900000	2023-09-13	F1X002	23
900000	2023-09-22	F1X003	17
900001	2023-09-11	F1X001	20
900001	2023-09-13	F1X002	28
900001	2023-09-22	F1X003	15
900000	2023-09-23	F1X002	30
900001	2023-09-23	F1X002	29
\.


--
-- Data for Name: corso_di_laurea; Type: TABLE DATA; Schema: esami_universita; Owner: bdlab
--

COPY esami_universita.corso_di_laurea (id, nome, tipo, anno_accademico_offerta, facolta, descrizione, sede, accesso, attivato) FROM stdin;
P1S	Fisica	Magistrale	2022-2023	Scienze e Tecnologie	La formazione fornita dalla Laurea Magistrale in Fisica ha l'obiettivo di mettere in grado lo studente o di proseguire con studi superiori o di inserirsi con competenza in un'attività di ricerca o professionale, avendo appreso l'utilizzazione del metodo scientifico, e la base sperimentale, teorica e matematica su cui è fondata la Fisica.	Città Studi	Libero	f
F1X	Informatica	Triennale	2022-2023	Scienze e Tecnologie	Gli obiettivi del corso di laurea in Informatica sono: da una parte fornire una solida conoscenza di base e metodologica dei principali settori delle scienze informatiche e matematiche e dall'altra fornire una buona padronanza delle metodologie e tecnologie proprie dell'Informatica, offrendo una preparazione adeguata per imparare e conoscere i diversi ambiti applicativi della disciplina e poter assimilare, comprendere e valutare l'impatto dei costanti progressi scientifici e tecnologici nell'ambito della disciplina.	Città Studi	Programmato	t
C1X	Chimica	Triennale	2017-2018	Scienze e Tecnologie	Il Corso si propone di fornire agli studenti un'adeguata padronanza dei metodi e contenuti scientifici di base nei principali settori delle Scienze Chimiche per facilitare un agevole inserimento nel mondo del lavoro e/o per accedere ad un successivo corso di Laurea Magistrale.	Città Studi	Programmato	t
C2X	Chimica	Triennale	2022-2023	Scienze e Tecnologie	Il Corso si propone di fornire agli studenti un'adeguata padronanza dei metodi e contenuti scientifici di base nei principali settori delle Scienze Chimiche per facilitare un agevole inserimento nel mondo del lavoro e/o per accedere ad un successivo corso di Laurea Magistrale.	Città Studi	Programmato	t
\.


--
-- Data for Name: docente; Type: TABLE DATA; Schema: esami_universita; Owner: bdlab
--

COPY esami_universita.docente (username, nome, cognome, sesso, data_nascita, istruzione, sede_ufficio) FROM stdin;
guido.nucci1	Guido	Nucci	M	1971-09-15	Dottorato in Scienze biologiche	Via Golgi 19, Milano
luigi.palermo1	Luigi	Palermo	M	1969-09-05	Dottorato in Informatica	Via Celoria 18, Milano
emilio.piazza1	Emilio	Piazza	M	1968-03-17	Dottorato in Chimica	Via Celoria 20, Milano
paolo.beneventi1	Paolo	Beneventi	M	1970-02-02	Dottorato in Informatica	Via Saldini 50, Milano
\.


--
-- Data for Name: esame; Type: TABLE DATA; Schema: esami_universita; Owner: bdlab
--

COPY esami_universita.esame (insegnamento, data, orario, tipo, luogo, iscrizioni_aperte) FROM stdin;
F1X001	2023-09-11	13:30:00	Scritto	Aula Alfa	f
F1X001	2023-09-24	15:30:00	Scritto	Aula G15	t
F1X002	2023-09-13	13:30:00	Laboratorio	Aula Omega	f
F1X002	2023-09-23	13:30:00	Laboratorio	Aula Tau	f
F1X003	2023-09-22	13:30:00	Scritto	Aula Omega	f
F1X003	2023-09-30	09:30:00	Scritto	Aula Beta	t
F1X003	2023-10-07	13:30:00	Scritto	Aula 405	t
F1X004	2023-09-29	13:30:00	Orale	Aula Lambda	f
C1X001	2023-09-14	08:30:00	Scritto	Aula 200	f
C1X002	2023-09-02	08:30:00	Scritto	Aula 104	f
C2X001	2023-09-14	13:30:00	Scritto	Aula 306	f
\.


--
-- Data for Name: insegnamento; Type: TABLE DATA; Schema: esami_universita; Owner: bdlab
--

COPY esami_universita.insegnamento (id, corso_di_laurea, codice_interno, nome, docente, anno_previsto, semestre, ore_totali, lingua, descrizione_testuale) FROM stdin;
F1X001	F1X	001	Architettura degli elaboratori 1	luigi.palermo1	1	1	60	Italiano	L'insegnamento introduce le conoscenze dei principi che sottendono al funzionamento di un elaboratore digitale; partendo dal livello delle porte logiche si arriva, attraverso alcuni livelli di astrazione intermedi, alla progettazione di ALU firmware e di un'architettura MIPS in grado di eseguire il nucleo delle istruzioni in linguaggio macchina.
F1X002	F1X	002	Programmazione 1	paolo.beneventi1	1	1	120	Italiano	Obiettivo dell'insegnamento e' introdurre gli studenti alla programmazione imperativa strutturata e al problem solving in piccolo.
F1X003	F1X	003	Matematica del continuo	paolo.beneventi1	1	1	112	Italiano	L'obiettivo dell'insegnamento è duplice. Anzitutto, fornire agli studenti un linguaggio matematico di base, che li metta grado di formulare correttamente un problema e di comprendere un problema formulato da altri. Inoltre, fornire gli strumenti matematici indispensabili per la soluzione di alcuni problemi specifici, che spaziano dal comportamento delle successioni a quello delle serie e delle funzioni di una variabile.
F1X004	F1X	004	Algoritmi e Strutture Dati	paolo.beneventi1	2	1	112	Italiano	L'obiettivo dell'insegnamento è duplice. Anzitutto, fornire agli studenti un linguaggio matematico di base, che li metta grado di formulare correttamente un problema e di comprendere un problema formulato da altri. Inoltre, fornire gli strumenti matematici indispensabili per la soluzione di alcuni problemi specifici, che spaziano dal comportamento delle successioni a quello delle serie e delle funzioni di una variabile.
C1X001	C1X	001	Istituzioni di matematica	emilio.piazza1	1	1	88	Italiano	L'insegnamento si propone di fornire gli strumenti matematici di base per le applicazioni della Matematica alle altre scienze, in particolare alla Chimica.
C1X002	C1X	002	Chimica generale	emilio.piazza1	1	1	144	Italiano	L'insegnamento ha lo scopo di introdurre gli studenti alle basi della Chimica e dell'attivita' di laboratorio.
C1X003	C1X	003	Chimica inorganica	emilio.piazza1	2	1	80	Italiano	Si intende: presentare i modelli e le teorie necessarie per razionalizzare la stereochimica e la reattivita' dei composti degli elementi dei gruppi principali; analizzare e discutere l'andamento periodico delle proprieta' chimiche; costruire un quadro concettuale che permetta di memorizzare/organizzare i fatti inerenti alla chimica degli elementi dei gruppi principali e dei metalli di transizione (limitatamente ai loro composti binari con i nonmetalli).
C1X004	C1X	004	Chimica biologica	guido.nucci1	2	1	48	Italiano	Comprensione dei fenomeni biologici come proprietà emergenti dall'interazione fisica e chimica tra le componenti molecolari della materia vivente. Comprensione della logica chimica alla base della struttura molecolare degli organismi viventi e delle trasformazioni chimiche che li riguardano.
C2X001	C2X	001	Istituzioni di matematica	emilio.piazza1	1	1	100	Italiano	L'insegnamento si propone di fornire gli strumenti matematici di base per le applicazioni della Matematica alle altre scienze, in particolare alla Chimica.
C2X002	C2X	002	Chimica generale	guido.nucci1	1	1	144	Italiano	L'insegnamento ha lo scopo di introdurre gli studenti alle basi della Chimica e dell'attivita' di laboratorio.
C2X003	C2X	003	Chimica inorganica	guido.nucci1	1	2	70	Italiano	Si intende: presentare i modelli e le teorie necessarie per razionalizzare la stereochimica e la reattivita' dei composti degli elementi dei gruppi principali; analizzare e discutere l'andamento periodico delle proprieta' chimiche; costruire un quadro concettuale che permetta di memorizzare/organizzare i fatti inerenti alla chimica degli elementi dei gruppi principali e dei metalli di transizione (limitatamente ai loro composti binari con i nonmetalli).
C2X004	C2X	004	Chimica biologica	guido.nucci1	2	1	48	Italiano	Comprensione dei fenomeni biologici come proprietà emergenti dall'interazione fisica e chimica tra le componenti molecolari della materia vivente. Comprensione della logica chimica alla base della struttura molecolare degli organismi viventi e delle trasformazioni chimiche che li riguardano.
P1S001	P1S	001	Elettronica 1	emilio.piazza1	1	1	42	Italiano	L'insegnamento si propone di fornire agli studenti i concetti di base della teoria dei circuiti e del funzionamento dei dispositivi elettronici a semiconduttore.
\.


--
-- Data for Name: iscrizione; Type: TABLE DATA; Schema: esami_universita; Owner: bdlab
--

COPY esami_universita.iscrizione (matricola, data, insegnamento) FROM stdin;
900000	2023-09-24	F1X001
900000	2023-09-30	F1X003
900001	2023-10-07	F1X003
\.


--
-- Data for Name: profilo_utente; Type: TABLE DATA; Schema: esami_universita; Owner: bdlab
--

COPY esami_universita.profilo_utente (username, password, ruolo) FROM stdin;
pietro.lucchese1	b5e235847840bb01bcb85f1b705bd0fa	Studente
michele.lucciano1	eef8a4eb3766655f84218370fdb5b679	Studente
vincenzo.colombo1	1e344abc1ea244b64aaa964b6cee9353	Studente
gaetana.cocci1	4039ae88f6b80631e471cf501ddd6196	Studente
segreteria1	19c204c960ac06dea900aae850b43018	Segreteria
paolo.beneventi1	4da66173107e78388824a9fb31790da4	Docente
luigi.palermo1	6a74b2e97baad9c99aeecc5ab64ca880	Docente
emilio.piazza1	fb5f84e1947e13c2dd0b574a8745c215	Docente
guido.nucci1	4ae6bebe5e493b6c38e503b097cfa488	Docente
\.


--
-- Data for Name: propedeuticita; Type: TABLE DATA; Schema: esami_universita; Owner: bdlab
--

COPY esami_universita.propedeuticita (insegnamento_1, insegnamento_2) FROM stdin;
C1X001	C1X003
C1X002	C1X003
C2X002	C2X003
F1X002	F1X004
F1X003	F1X004
\.


--
-- Data for Name: storico_carriera; Type: TABLE DATA; Schema: esami_universita; Owner: bdlab
--

COPY esami_universita.storico_carriera (matricola, data, insegnamento, voto) FROM stdin;
900003	2023-09-14	C2X001	21
\.


--
-- Data for Name: storico_studenti; Type: TABLE DATA; Schema: esami_universita; Owner: bdlab
--

COPY esami_universita.storico_studenti (matricola, username, nome, cognome, sesso, data_nascita, corso_di_laurea, motivo, anno_rimozione) FROM stdin;
900002	vincenzo.colombo1	Vincenzo	Colombo	M	1999-10-17	C1X	Rinuncia	2020
900003	gaetana.cocci1	Gaetana	Cocci	F	2003-01-05	C2X	Rinuncia	2022
\.


--
-- Data for Name: studente_corrente; Type: TABLE DATA; Schema: esami_universita; Owner: bdlab
--

COPY esami_universita.studente_corrente (matricola, username, nome, cognome, sesso, data_nascita, corso_di_laurea) FROM stdin;
900000	michele.lucciano1	Michele	Lucciano	M	2003-02-27	F1X
900001	pietro.lucchese1	Pietro	Lucchese	M	2002-10-17	F1X
\.


--
-- Name: carriera carriera_pkey; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.carriera
    ADD CONSTRAINT carriera_pkey PRIMARY KEY (matricola, data, insegnamento);


--
-- Name: corso_di_laurea corso_di_laurea_nome_sede_anno_accademico_offerta_tipo_key; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.corso_di_laurea
    ADD CONSTRAINT corso_di_laurea_nome_sede_anno_accademico_offerta_tipo_key UNIQUE (nome, sede, anno_accademico_offerta, tipo);


--
-- Name: corso_di_laurea corso_di_laurea_pkey; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.corso_di_laurea
    ADD CONSTRAINT corso_di_laurea_pkey PRIMARY KEY (id);


--
-- Name: docente docente_pkey; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.docente
    ADD CONSTRAINT docente_pkey PRIMARY KEY (username);


--
-- Name: esame esame_pkey; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.esame
    ADD CONSTRAINT esame_pkey PRIMARY KEY (insegnamento, data);


--
-- Name: insegnamento insegnamento_corso_di_laurea_codice_interno_key; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.insegnamento
    ADD CONSTRAINT insegnamento_corso_di_laurea_codice_interno_key UNIQUE (corso_di_laurea, codice_interno);


--
-- Name: insegnamento insegnamento_corso_di_laurea_nome_key; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.insegnamento
    ADD CONSTRAINT insegnamento_corso_di_laurea_nome_key UNIQUE (corso_di_laurea, nome);


--
-- Name: insegnamento insegnamento_pkey; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.insegnamento
    ADD CONSTRAINT insegnamento_pkey PRIMARY KEY (id);


--
-- Name: iscrizione iscrizione_pkey; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.iscrizione
    ADD CONSTRAINT iscrizione_pkey PRIMARY KEY (matricola, data, insegnamento);


--
-- Name: profilo_utente profilo_utente_pkey; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.profilo_utente
    ADD CONSTRAINT profilo_utente_pkey PRIMARY KEY (username);


--
-- Name: propedeuticita propedeuticita_pkey; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.propedeuticita
    ADD CONSTRAINT propedeuticita_pkey PRIMARY KEY (insegnamento_1, insegnamento_2);


--
-- Name: storico_carriera storico_carriera_pkey; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.storico_carriera
    ADD CONSTRAINT storico_carriera_pkey PRIMARY KEY (matricola, data, insegnamento);


--
-- Name: storico_studenti storico_studenti_pkey; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.storico_studenti
    ADD CONSTRAINT storico_studenti_pkey PRIMARY KEY (matricola);


--
-- Name: storico_studenti storico_studenti_username_key; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.storico_studenti
    ADD CONSTRAINT storico_studenti_username_key UNIQUE (username);


--
-- Name: studente_corrente studente_corrente_pkey; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.studente_corrente
    ADD CONSTRAINT studente_corrente_pkey PRIMARY KEY (matricola);


--
-- Name: studente_corrente studente_corrente_username_key; Type: CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.studente_corrente
    ADD CONSTRAINT studente_corrente_username_key UNIQUE (username);


--
-- Name: insegnamento check_anno_previsto_trigger; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_anno_previsto_trigger BEFORE INSERT OR UPDATE ON esami_universita.insegnamento FOR EACH ROW EXECUTE FUNCTION esami_universita.check_anno_previsto();


--
-- Name: esame check_data_esame_trigger; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_data_esame_trigger BEFORE INSERT ON esami_universita.esame FOR EACH ROW EXECUTE FUNCTION esami_universita.check_data_esame();


--
-- Name: insegnamento check_docente_max_insegnamenti_trigger; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_docente_max_insegnamenti_trigger BEFORE INSERT OR UPDATE ON esami_universita.insegnamento FOR EACH ROW EXECUTE FUNCTION esami_universita.check_docente_max_insegnamenti();


--
-- Name: esame check_esame_cdl_attivato; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_esame_cdl_attivato BEFORE INSERT ON esami_universita.esame FOR EACH ROW EXECUTE FUNCTION esami_universita.check_esame_cdl_attivato();


--
-- Name: esame check_esame_trigger; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_esame_trigger BEFORE INSERT ON esami_universita.esame FOR EACH ROW EXECUTE FUNCTION esami_universita.check_esame();


--
-- Name: iscrizione check_iscrizione_gia_verb_trigger; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_iscrizione_gia_verb_trigger BEFORE INSERT ON esami_universita.iscrizione FOR EACH ROW EXECUTE FUNCTION esami_universita.check_iscrizione_gia_verb();


--
-- Name: iscrizione check_iscrizione_iscr_aperte_trigger; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_iscrizione_iscr_aperte_trigger BEFORE INSERT ON esami_universita.iscrizione FOR EACH ROW EXECUTE FUNCTION esami_universita.check_iscrizione_iscr_aperte();


--
-- Name: iscrizione check_iscrizione_trigger; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_iscrizione_trigger BEFORE INSERT ON esami_universita.iscrizione FOR EACH ROW EXECUTE FUNCTION esami_universita.check_iscrizione();


--
-- Name: corso_di_laurea check_on_delete_cdl; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_on_delete_cdl BEFORE DELETE ON esami_universita.corso_di_laurea FOR EACH ROW EXECUTE FUNCTION esami_universita.check_on_delete_cdl();


--
-- Name: docente check_on_delete_docente; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_on_delete_docente BEFORE DELETE ON esami_universita.docente FOR EACH ROW EXECUTE FUNCTION esami_universita.check_on_delete_docente();


--
-- Name: esame check_on_delete_esame; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_on_delete_esame BEFORE DELETE ON esami_universita.esame FOR EACH ROW EXECUTE FUNCTION esami_universita.check_on_delete_esame();


--
-- Name: insegnamento check_on_delete_insegnamento; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_on_delete_insegnamento BEFORE DELETE ON esami_universita.insegnamento FOR EACH ROW EXECUTE FUNCTION esami_universita.check_on_delete_insegnamento();


--
-- Name: docente check_profilo_utente_docente_trigger; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_profilo_utente_docente_trigger BEFORE INSERT ON esami_universita.docente FOR EACH ROW EXECUTE FUNCTION esami_universita.check_profilo_utente_docente();


--
-- Name: storico_studenti check_profilo_utente_storico_studenti_trigger; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE CONSTRAINT TRIGGER check_profilo_utente_storico_studenti_trigger AFTER INSERT ON esami_universita.storico_studenti DEFERRABLE INITIALLY DEFERRED FOR EACH ROW EXECUTE FUNCTION esami_universita.check_profilo_utente_storico_studenti();


--
-- Name: studente_corrente check_profilo_utente_studente_trigger; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_profilo_utente_studente_trigger BEFORE INSERT ON esami_universita.studente_corrente FOR EACH ROW EXECUTE FUNCTION esami_universita.check_profilo_utente_studente();


--
-- Name: propedeuticita check_propedeuticita_trigger; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_propedeuticita_trigger BEFORE INSERT ON esami_universita.propedeuticita FOR EACH ROW EXECUTE FUNCTION esami_universita.check_propedeuticita();


--
-- Name: storico_studenti check_stor_stud_matr_trigger; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE CONSTRAINT TRIGGER check_stor_stud_matr_trigger AFTER INSERT ON esami_universita.storico_studenti DEFERRABLE INITIALLY DEFERRED FOR EACH ROW EXECUTE FUNCTION esami_universita.check_stor_stud_matr();


--
-- Name: storico_studenti check_storico_studenti_cdl_attivato; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_storico_studenti_cdl_attivato BEFORE INSERT ON esami_universita.storico_studenti FOR EACH ROW EXECUTE FUNCTION esami_universita.check_storico_studenti_cdl_attivato();


--
-- Name: studente_corrente check_stud_corr_matr; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_stud_corr_matr BEFORE INSERT ON esami_universita.studente_corrente FOR EACH ROW EXECUTE FUNCTION esami_universita.check_stud_corr_matr();


--
-- Name: studente_corrente check_studente_cdl_attivato; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER check_studente_cdl_attivato BEFORE INSERT ON esami_universita.studente_corrente FOR EACH ROW EXECUTE FUNCTION esami_universita.check_studente_cdl_attivato();


--
-- Name: studente_corrente delete_actual_student_trigger; Type: TRIGGER; Schema: esami_universita; Owner: bdlab
--

CREATE TRIGGER delete_actual_student_trigger BEFORE DELETE ON esami_universita.studente_corrente FOR EACH ROW EXECUTE FUNCTION esami_universita.delete_actual_student();


--
-- Name: carriera carriera_data_insegnamento_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.carriera
    ADD CONSTRAINT carriera_data_insegnamento_fkey FOREIGN KEY (data, insegnamento) REFERENCES esami_universita.esame(data, insegnamento) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: carriera carriera_matricola_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.carriera
    ADD CONSTRAINT carriera_matricola_fkey FOREIGN KEY (matricola) REFERENCES esami_universita.studente_corrente(matricola) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: docente docente_username_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.docente
    ADD CONSTRAINT docente_username_fkey FOREIGN KEY (username) REFERENCES esami_universita.profilo_utente(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: esame esame_insegnamento_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.esame
    ADD CONSTRAINT esame_insegnamento_fkey FOREIGN KEY (insegnamento) REFERENCES esami_universita.insegnamento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: insegnamento insegnamento_corso_di_laurea_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.insegnamento
    ADD CONSTRAINT insegnamento_corso_di_laurea_fkey FOREIGN KEY (corso_di_laurea) REFERENCES esami_universita.corso_di_laurea(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: insegnamento insegnamento_docente_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.insegnamento
    ADD CONSTRAINT insegnamento_docente_fkey FOREIGN KEY (docente) REFERENCES esami_universita.docente(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: iscrizione iscrizione_data_insegnamento_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.iscrizione
    ADD CONSTRAINT iscrizione_data_insegnamento_fkey FOREIGN KEY (data, insegnamento) REFERENCES esami_universita.esame(data, insegnamento) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: iscrizione iscrizione_matricola_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.iscrizione
    ADD CONSTRAINT iscrizione_matricola_fkey FOREIGN KEY (matricola) REFERENCES esami_universita.studente_corrente(matricola) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: propedeuticita propedeuticita_insegnamento_1_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.propedeuticita
    ADD CONSTRAINT propedeuticita_insegnamento_1_fkey FOREIGN KEY (insegnamento_1) REFERENCES esami_universita.insegnamento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: propedeuticita propedeuticita_insegnamento_2_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.propedeuticita
    ADD CONSTRAINT propedeuticita_insegnamento_2_fkey FOREIGN KEY (insegnamento_2) REFERENCES esami_universita.insegnamento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: storico_carriera storico_carriera_data_insegnamento_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.storico_carriera
    ADD CONSTRAINT storico_carriera_data_insegnamento_fkey FOREIGN KEY (data, insegnamento) REFERENCES esami_universita.esame(data, insegnamento) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: storico_carriera storico_carriera_matricola_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.storico_carriera
    ADD CONSTRAINT storico_carriera_matricola_fkey FOREIGN KEY (matricola) REFERENCES esami_universita.storico_studenti(matricola) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: storico_studenti storico_studenti_corso_di_laurea_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.storico_studenti
    ADD CONSTRAINT storico_studenti_corso_di_laurea_fkey FOREIGN KEY (corso_di_laurea) REFERENCES esami_universita.corso_di_laurea(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: storico_studenti storico_studenti_username_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.storico_studenti
    ADD CONSTRAINT storico_studenti_username_fkey FOREIGN KEY (username) REFERENCES esami_universita.profilo_utente(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: studente_corrente studente_corrente_corso_di_laurea_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.studente_corrente
    ADD CONSTRAINT studente_corrente_corso_di_laurea_fkey FOREIGN KEY (corso_di_laurea) REFERENCES esami_universita.corso_di_laurea(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: studente_corrente studente_corrente_username_fkey; Type: FK CONSTRAINT; Schema: esami_universita; Owner: bdlab
--

ALTER TABLE ONLY esami_universita.studente_corrente
    ADD CONSTRAINT studente_corrente_username_fkey FOREIGN KEY (username) REFERENCES esami_universita.profilo_utente(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

