------------------------------------------------------
-- Groups                                           --
------------------------------------------------------

DROP TABLE groups CASCADE;
CREATE TABLE groups (
  group_id			varchar(128)	PRIMARY KEY NOT NULL DEFAULT 'users',
  valid				bool			NOT NULL DEFAULT 'f',
  valid_from		int				NOT NULL DEFAULT cobra_time() CHECK ( valid_from  >= 0 ),
  valid_until		int				NOT NULL DEFAULT cobra_seconds( '10 years' ) CHECK ( valid_until >= 0 AND valid_until > valid_from ),
  description		varchar(256)
);


-- views
CREATE VIEW valid_groups AS
  SELECT group_id, description, valid_from, valid_until 
  FROM groups WHERE valid = 't';
-- END VIEW


-- stored functions
-- DROP FUNCTION groups_force_read( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION groups_force_read( _group_id varchar ) RETURNS SETOF groups AS $$
  DECLARE
    _exception varchar := 'groups_force_read';
    _time      integer := cobra_time();
	_result    groups%ROWTYPE;
  BEGIN
	FOR _result IN SELECT * FROM groups 
			    WHERE group_id = _group_id
	LOOP
	  RETURN NEXT _result;
	END LOOP;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION groups_force_read( varchar ) FROM PUBLIC;


-- DROP FUNCTION groups_valid( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION groups_valid( _group_id varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'groups_valid';
    _time      integer := cobra_time();
	_temp      varchar := '';
  BEGIN
    SELECT INTO _temp group_id FROM valid_groups 
		   WHERE group_id = _group_id AND valid_from < _time AND valid_until > _time;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION groups_valid( varchar ) FROM PUBLIC;


-- DROP FUNCTION groups_read( varchar ) CASCADE;
CREATE OR REPLACE FUNCTION groups_read( _group_id varchar ) RETURNS SETOF valid_groups AS $$
  DECLARE
    _exception varchar := 'groups_read';
    _time      integer := cobra_time();
	_result    valid_groups%ROWTYPE;
  BEGIN
	FOR _result IN SELECT * FROM valid_groups 
			    WHERE group_id = _group_id AND valid_from < _time AND valid_until > _time 
	LOOP
	  RETURN NEXT _result;
	END LOOP;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION groups_read( varchar ) FROM PUBLIC;
