------------------------------------------------------
-- Sessions                                         --
------------------------------------------------------

---- begin
DROP TABLE sessions CASCADE;
CREATE TABLE sessions (
  session_id	varchar(512)	PRIMARY KEY NOT NULL DEFAULT '0',
  expires	int		NOT NULL DEFAULT '0' CHECK ( expires >= 0 ),
  data		text		NOT NULL
);
-- data

-- procedures
-- read not expired session data
CREATE OR REPLACE FUNCTION sessions_read( _session_id varchar ) RETURNS text AS $$
  DECLARE
    _exception varchar := 'sessions_read';
    _data      text    := '';
    _time      integer := cobra_time();
  BEGIN
    SELECT INTO _data data FROM sessions WHERE session_id = _session_id AND expires > _time;
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    RETURN _data;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION sessions_read( varchar ) FROM PUBLIC;


-- read session data anyway
CREATE OR REPLACE FUNCTION sessions_force_read( _session_id varchar ) RETURNS text AS $$
  DECLARE
    _exception varchar := 'sessions_force_read';
    _data      text    := '';
    _time      integer := cobra_time();
  BEGIN
    SELECT INTO _data data FROM sessions WHERE session_id = _session_id;
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    RETURN _data;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION sessions_force_read( varchar ) FROM PUBLIC;


-- check if the session is expired
CREATE OR REPLACE FUNCTION sessions_expired( _session_id varchar ) RETURNS int AS $$
  DECLARE
    _exception varchar := 'sessions_expired';
    _time      integer := cobra_time();
    _expires   integer := 0;
  BEGIN
    SELECT INTO _expires expires FROM sessions WHERE session_id = _session_id;
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    IF _expires < _time THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    RETURN _expires;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION sessions_expired( varchar ) FROM PUBLIC;


-- write session data
-- TODO: consider 35-1 postgres example
CREATE OR REPLACE FUNCTION sessions_write( _session_id varchar, _expires int, _data text ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'sessions_write';
    _time      integer := cobra_time();
    __expires  integer := _time + _expires;
  BEGIN
    UPDATE sessions SET data = _data, expires = __expires WHERE session_id = _session_id AND expires > _time;
    IF NOT FOUND THEN
      INSERT INTO sessions (session_id, expires, data) VALUES ( _session_id, __expires, _data );
      IF NOT FOUND THEN
        RAISE EXCEPTION '%', _exception;
      END IF;
    END IF;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION sessions_write( varchar, int, text ) FROM PUBLIC;


-- change session id
CREATE OR REPLACE FUNCTION sessions_change_id( _old_session_id varchar, _new_session_id varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'sessions_change_id';
    _time      integer := cobra_time();
  BEGIN
    UPDATE sessions SET session_id = _new_session_id WHERE session_id = _old_session_id AND expires > _time;
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION sessions_change_id( varchar, varchar ) FROM PUBLIC;


-- destroy the session
CREATE OR REPLACE FUNCTION sessions_destroy( _session_id varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'sessions_destroy';
    _time      integer := cobra_time();  
  BEGIN
    UPDATE sessions SET expires = '0' WHERE session_id = _session_id AND expires > _time;
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;	
    END IF;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION sessions_destroy( varchar ) FROM PUBLIC;


-- garbage collector
CREATE OR REPLACE FUNCTION sessions_gc() RETURNS int AS $$
  DECLARE
    _exception varchar := 'sessions_gc';
    _time      integer := cobra_time();
    _affrows   integer := 0;
  BEGIN
    DELETE FROM sessions WHERE expires < _time;
    GET DIAGNOSTICS _affrows = ROW_COUNT;
    RETURN _affrows;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION sessions_gc() FROM PUBLIC;


-- mr proper
CREATE OR REPLACE FUNCTION sessions_clean() RETURNS bool AS $$
  BEGIN
    DELETE FROM sessions;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION sessions_clean() FROM PUBLIC;
---- end
