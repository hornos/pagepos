------------------------------------------------------
-- Applications                                     --
------------------------------------------------------


DROP TABLE applications CASCADE;
CREATE TABLE applications (
  app_id			varchar(128)    PRIMARY KEY NOT NULL DEFAULT 'core',
  valid				bool			NOT NULL DEFAULT 'f',
  valid_from		int				NOT NULL DEFAULT cobra_time() CHECK ( valid_from  >= 0 ),
  valid_until		int				NOT NULL DEFAULT cobra_seconds( '10 years' ) CHECK ( valid_until >= 0 AND valid_until > valid_from ),  
  description		varchar(256)   	NOT NULL
);

-- views
CREATE VIEW valid_applications AS
  SELECT app_id, description, valid_from, valid_until 
  FROM applications WHERE valid = 't';
-- END VIEW


-- stored functions
-- DROP FUNCTION applications_force_read( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION applications_force_read( _app_id varchar ) RETURNS SETOF applications AS $$
  DECLARE
    _exception varchar := 'applications_force_read';
    _time      integer := cobra_time();
	_result    applications%ROWTYPE;
  BEGIN
	FOR _result IN SELECT * FROM applications WHERE app_id = _app_id
	LOOP
	  RETURN NEXT _result;
	END LOOP;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION applications_force_read( varchar ) FROM PUBLIC;



-- DROP FUNCTION applications_valid( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION applications_valid( _app_id varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'applications_valid';
    _time      integer := cobra_time();
	_temp      varchar := '';
  BEGIN
    SELECT INTO _temp app_id FROM valid_applications 
		   WHERE app_id = _app_id AND valid_from < _time AND valid_until > _time;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION applications_valid( varchar ) FROM PUBLIC;



-- DROP FUNCTION applications_read( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION applications_read( _app_id varchar ) RETURNS SETOF valid_applications AS $$
  DECLARE
    _exception varchar := 'applications_read';
    _time      integer := cobra_time();
	_result    valid_applications%ROWTYPE;
  BEGIN
	FOR _result IN SELECT * FROM valid_applications 
			    WHERE app_id = _app_id AND valid_from < _time AND valid_until > _time 
	LOOP
	  RETURN NEXT _result;
	END LOOP;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION applications_read( varchar ) FROM PUBLIC;
