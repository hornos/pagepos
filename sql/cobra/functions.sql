-- Language
CREATE LANGUAGE 'plpgsql';

------------------------------------------------------
-- Time Related Functions                           --
------------------------------------------------------

-- Return seconds from now shifted by a time string
CREATE OR REPLACE FUNCTION cobra_seconds( _shift varchar ) RETURNS int AS $$
  BEGIN
	RETURN extract( epoch FROM now() + _shift::interval )::integer;
  END;
$$ LANGUAGE plpgsql;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION cobra_seconds( varchar ) FROM PUBLIC;


-- Return time from epoch in seconds
CREATE OR REPLACE FUNCTION cobra_time() RETURNS int AS $$
  BEGIN
    RETURN extract( EPOCH FROM CURRENT_TIMESTAMP(0) );
  END;
$$ LANGUAGE plpgsql;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION cobra_time() FROM PUBLIC;


-- Return time from epoch in seconds with microseconds
CREATE OR REPLACE FUNCTION cobra_microtime() RETURNS timestamp AS $$
  BEGIN
    RETURN CURRENT_TIMESTAMP(6);
  END;
$$ LANGUAGE plpgsql;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION cobra_microtime() FROM PUBLIC;


-- Return the current timestamp
CREATE OR REPLACE FUNCTION cobra_timestamp() RETURNS timestamp AS $$
  BEGIN
    RETURN CURRENT_TIMESTAMP(0);
  END;
$$ LANGUAGE plpgsql;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION cobra_timestamp() FROM PUBLIC;
