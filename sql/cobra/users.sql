------------------------------------------------------
-- Users                                            --
------------------------------------------------------

-- begin
DROP TABLE users	  CASCADE;
CREATE TABLE users (
  user_id			varchar(128)	PRIMARY KEY NOT NULL,
  group_id			varchar(128)	REFERENCES groups(group_id) ON DELETE RESTRICT NOT NULL DEFAULT 'users',
  valid				bool			NOT NULL DEFAULT 'f',
  valid_from		int				NOT NULL DEFAULT cobra_time() CHECK ( valid_from  >= 0 ),
  valid_until		int				NOT NULL DEFAULT cobra_seconds( '10 years' ) CHECK ( valid_until >= 0 AND valid_until > valid_from ),
  description		varchar(256),
--
  passcode			varchar(512)	NOT NULL,
  passtype			varchar(8)		NOT NULL DEFAULT 'SHA1',
--
  grace_time 		int				NOT NULL DEFAULT '300',
  online  		    bool			NOT NULL DEFAULT 'f',
  app_id			varchar(128)	REFERENCES applications(app_id),
  login_time  		int				NOT NULL DEFAULT '0' CHECK ( login_time  >= 0 ),
  last_action_time 	int				NOT NULL DEFAULT '0' CHECK ( last_action_time >= 0 ),
  logout_time 		int				NOT NULL DEFAULT '0' CHECK ( logout_time >= 0 ),
  login_tries 		int				NOT NULL DEFAULT '0',
  last_try_time		int				NOT NULL DEFAULT '0' CHECK ( last_try_time >= 0 ),  
-- ISO 639
  locale			char(8)			NOT NULL DEFAULT 'EN'
);
-- views
CREATE VIEW valid_users AS
  SELECT user_id, group_id, valid_from, valid_until, 
         description, grace_time, online, app_id, 
         login_time, last_action_time, logout_time, 
         login_tries, last_try_time, locale
  FROM users WHERE valid = 't';
-- END VIEW



-- stored procedures
-- DROP FUNCTION users_force_read( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION users_force_read( _user_id varchar ) RETURNS SETOF users AS $$
  DECLARE
    _exception varchar := 'users_force_read';
    _time      integer := cobra_time();
	_result    users%ROWTYPE;
  BEGIN
	FOR _result IN SELECT * FROM users WHERE user_id = _user_id 
	LOOP
	  RETURN NEXT _result;
	END LOOP;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION users_force_read( varchar ) FROM PUBLIC;



-- DROP FUNCTION users_group_id( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION users_group_id( _user_id varchar ) RETURNS varchar AS $$
  DECLARE
    _exception varchar := 'users_group_id';
    _group_id  varchar := '';
    _check     bool    := false;
  BEGIN
    SELECT INTO _group_id group_id FROM valid_users WHERE user_id = _user_id;
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    SELECT INTO _check groups_valid( _group_id );
    RETURN _group_id;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION users_group_id( varchar ) FROM PUBLIC;



-- DROP FUNCTION users_valid( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION users_valid( _user_id varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'users_valid';
    _time      integer := cobra_time();
    _temp      varchar := '';
  BEGIN
    -- group check
    SELECT INTO _temp users_group_id( _user_id );
    SELECT INTO _temp user_id FROM valid_users 
		   WHERE user_id = _user_id AND valid_from < _time AND valid_until > _time;        
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION users_valid( varchar ) FROM PUBLIC;



-- DROP FUNCTION users_read( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION users_read( _user_id varchar ) RETURNS SETOF valid_users AS $$
  DECLARE
    _exception varchar := 'users_read';
    _time      integer := cobra_time();
	_result    valid_users%ROWTYPE;
    _temp      varchar := '';
  BEGIN
    -- group check
    SELECT INTO _temp users_group_id( _user_id );
	FOR _result IN SELECT * FROM valid_users 
			    WHERE user_id = _user_id AND valid_from < _time AND valid_until > _time 
	LOOP
	  RETURN NEXT _result;
	END LOOP;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION users_read( varchar ) FROM PUBLIC;



-- DROP FUNCTION users_increment_login_tries( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION users_increment_login_tries( _user_id varchar ) RETURNS int AS $$
  DECLARE
    _exception   varchar := 'users_increment_login_tries';
    _time        integer := cobra_time();
    _login_tries integer := 0;
    _check       bool    := false;
  BEGIN
    -- user and group check
    SELECT INTO _check users_valid( _user_id );
    SELECT INTO _login_tries login_tries FROM valid_users WHERE user_id = _user_id;
	UPDATE users SET login_tries = (_login_tries + 1), last_try_time = _time WHERE user_id = _user_id;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
	RETURN _login_tries;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER; 
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION users_increment_login_tries( varchar ) FROM PUBLIC;



-- DROP FUNCTION users_reset_login_tries( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION users_reset_login_tries( _user_id varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'users_reset_login_tries';
    _time      integer := cobra_time();
    _check     bool    := false;
  BEGIN
    -- user and group check
    SELECT INTO _check users_valid( _user_id );
	UPDATE users SET login_tries = '0', last_try_time = '0' WHERE user_id = _user_id;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
	RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER; 
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION users_reset_login_tries( varchar ) FROM PUBLIC;



-- DROP FUNCTION users_login( varchar, varchar ) CASCADE;
CREATE OR REPLACE FUNCTION users_login( _app_id varchar, _user_id varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'users_login';
    _time      integer := cobra_time();  
    _check     bool    := false;
  BEGIN
    -- user and group check
    SELECT INTO _check users_valid( _user_id );
    -- application check
    SELECT INTO _check applications_valid( _app_id );
    UPDATE users SET online = 't', login_time = _time, last_action_time = _time,
	                app_id = _app_id, login_tries = 0 
	             WHERE user_id = _user_id;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
	RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION users_login( varchar, varchar ) FROM PUBLIC;



-- DROP FUNCTION users_logout( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION users_logout( _user_id varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'users_logout';
    _time      integer := cobra_time();
    _check     bool    := false;
  BEGIN
    -- user and group check
    SELECT INTO _check users_valid( _user_id );
    UPDATE users SET online = 'f', logout_time = _time, app_id = NULL WHERE user_id = _user_id;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
	RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION users_logout( varchar ) FROM PUBLIC;



-- DROP FUNCTION users_gc() CASCADE;
CREATE OR REPLACE FUNCTION users_gc() RETURNS int AS $$
  DECLARE
    _exception varchar := 'users_gc';
    _time      integer := cobra_time();
	_affrows   integer := 0;
  BEGIN
    UPDATE users SET online = 'f', logout_time = _time  
	             WHERE valid = 't' AND valid_from < _time AND valid_until > _time 
				 AND online = 't' AND last_action_time + grace_time < _time;
	GET DIAGNOSTICS _affrows = ROW_COUNT;
	RETURN _affrows;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION users_gc() FROM PUBLIC;



-- DROP FUNCTION users_check_online( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION users_check_online( _user_id varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'users_check_online';
    _time      integer := cobra_time();
	_online    bool    := false;
    _check     bool    := false;
  BEGIN
    -- user and group check
    SELECT INTO _check users_valid( _user_id );
    SELECT INTO _online online FROM valid_users WHERE online = 't' AND user_id = _user_id;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
	RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION users_check_online( varchar ) FROM PUBLIC;



-- DROP FUNCTION users_check_passcode( varchar, varchar ) CASCADE;
CREATE OR REPLACE FUNCTION users_check_passcode( _user_id varchar, _passcode varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'users_check_passcode';
    _time      integer := cobra_time();  
    __passcode varchar := '';
    _check     bool    := false;
  BEGIN
    -- user and group check
    SELECT INTO _check users_valid( _user_id );
    SELECT INTO __passcode passcode FROM users WHERE user_id = _user_id;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
	IF __passcode = _passcode THEN
	  RETURN FOUND;
	END IF;
	RAISE EXCEPTION '%', _exception;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION users_check_passcode( varchar, varchar ) FROM PUBLIC;



-- DROP FUNCTION users_set_last_action_time( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION users_set_last_action_time( _user_id varchar ) RETURNS int AS $$
  DECLARE
    _exception varchar := 'users_set_last_action_time';
    _time      integer := cobra_time();
    _check     bool    := false;
  BEGIN
    -- user and group check
    SELECT INTO _check users_valid( _user_id );
    UPDATE users SET last_action_time = _time WHERE user_id = _user_id;
    IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
	RETURN _time;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION users_set_last_action_time( varchar ) FROM PUBLIC;
-- end
