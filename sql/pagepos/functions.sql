-- Language
CREATE LANGUAGE 'plpgsql';

DROP VIEW geonames_cities CASCADE;
CREATE VIEW geonames_cities AS
  SELECT geonameid, ascii_name, name, latitude, longitude, population, country_code
  FROM geonames WHERE feature_class = 'P' AND feature_code ~ '^PPL';
GRANT SELECT ON geonames_cities TO pagepos_admin;


DROP VIEW geonames_cities_details CASCADE;
CREATE VIEW geonames_cities_details AS
  SELECT geonames.geonameid AS geonameid, geonames.name AS name, geonames.ascii_name AS ascii_name,
         geonames.latitude AS latitude, geonames.longitude AS longitude, 
         geonames.population AS population, geonames.country_code AS country_code,
         country_codes.country_name AS country_name, admin1_codes.admin1_name AS admin1_name, 
         geonames2users.link AS link, geonames2users.status AS status,
         geonames2users.price AS price
  FROM geonames INNER JOIN country_codes USING(country_code) 
                INNER JOIN admin1_codes USING(country_code, admin1_code) 
                LEFT  JOIN geonames2users USING(geonameid)
  WHERE geonames.feature_class = 'P' AND geonames.feature_code ~ '^PPL';
GRANT SELECT ON geonames_cities_details TO pagepos_admin;


DROP VIEW geonames_cities_users CASCADE;
CREATE VIEW geonames_cities_users AS
  SELECT geonames.geonameid AS geonameid, geonames.name AS name, geonames.ascii_name AS ascii_name, 
         geonames.latitude AS latitude, geonames.longitude AS longitude, 
         geonames.population AS population, geonames.country_code AS country_code,
         country_codes.country_name AS country_name, admin1_codes.admin1_name AS admin1_name,
         geonames2users.link AS link, geonames2users.status AS status,
         geonames2users.sold_time AS sold_time, geonames2users.counts AS counts
  FROM geonames INNER JOIN country_codes USING(country_code) 
                INNER JOIN admin1_codes USING(country_code, admin1_code)
                INNER JOIN geonames2users USING(geonameid)
  WHERE geonames.feature_class = 'P' AND geonames.feature_code ~ '^PPL' 
  AND ( geonames2users.status = 'sold' OR geonames2users.status = 'demo' );
GRANT SELECT ON geonames_cities_users TO pagepos_admin;


-- stored procedures
CREATE OR REPLACE FUNCTION get_geonames( nelat numeric, swlat numeric, nelng numeric, swlng numeric, zl int ) RETURNS SETOF geonames_cities_details AS $$
  DECLARE
    _exception varchar := 'SQL::get_geonames';
    _result geonames_cities_details%ROWTYPE;
    _poplimit int := 0;
  BEGIN
        IF    zl < 4 THEN
          _poplimit := 500000;
        ELSIF zl < 6 THEN
          _poplimit := 250000;
        ELSIF zl < 8 THEN
          _poplimit := 80000;
        ELSIF zl < 10  THEN
          _poplimit := 20000;
        END IF;
        
	FOR _result IN SELECT * FROM geonames_cities_details 
		    WHERE latitude <= nelat AND latitude >= swlat AND longitude <= nelng AND longitude >= swlng
		    AND population >= _poplimit ORDER BY population 
	LOOP
	  RETURN NEXT _result;
	END LOOP;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION get_geonames( numeric, numeric, numeric, numeric, int ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION get_geonames( numeric, numeric, numeric, numeric, int ) TO GROUP pagepos;



CREATE OR REPLACE FUNCTION get_geonames_users( nelat numeric, swlat numeric, nelng numeric, swlng numeric, zl int ) RETURNS SETOF geonames_cities_users AS $$
  DECLARE
    _exception varchar := 'SQL::get_users';
    _result geonames_cities_users%ROWTYPE;
    _poplimit int := 0;
  BEGIN
        
	FOR _result IN SELECT * FROM geonames_cities_users 
		    WHERE latitude <= nelat AND latitude >= swlat AND longitude <= nelng AND longitude >= swlng
                    ORDER BY population ASC
	LOOP
	  RETURN NEXT _result;
	END LOOP;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION get_geonames_users( numeric, numeric, numeric, numeric, int ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION get_geonames_users( numeric, numeric, numeric, numeric, int ) TO GROUP pagepos;


CREATE OR REPLACE FUNCTION gen_price( _pop int ) RETURNS int AS $$
  DECLARE
    _max real := 100.0;
    _kt  real := 1500000.0;
    _d   real := 7.0;
    _mu  real := 14608512.0;
    _min real := 1000000.0;
  BEGIN
    IF _pop > _min THEN
      RETURN ceil( _max / ( exp( ( _mu - _pop ) / _kt - _d ) + 1.0 ) );
    END IF;
    RETURN 10;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION gen_price( int ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION gen_price( int ) TO GROUP pagepos;


-- DROP FUNCTION gen_flat_price( int ) CASCADE;
CREATE OR REPLACE FUNCTION gen_flat_price( _pop int ) RETURNS int AS $$
  DECLARE
    _price int := 5;
  BEGIN
    IF _pop >= 1000000 THEN
      _price = 100;
    ELSIF _pop >= 500000 THEN
      _price = 10;
    ELSE
      _price = 5;
    END IF;
    RETURN _price;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION gen_flat_price( int ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION gen_flat_price( int ) TO GROUP pagepos;


-- DROP FUNCTION get_price( int ) CASCADE;
CREATE OR REPLACE FUNCTION get_price( _geonameid int ) RETURNS int AS $$
  DECLARE
    _exception varchar := 'SQL::get_price';
    _price int := 5;
    _pop   int := 0;
  BEGIN
    SELECT INTO _pop population FROM geonames_cities_details 
           WHERE geonameid = _geonameid;
    IF NOT FOUND THEN 
      RAISE EXCEPTION '%', _exception;
    END IF;
    SELECT INTO _price gen_flat_price( _pop );
    RETURN _price;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION get_price( int ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION get_price( int ) TO GROUP pagepos;



CREATE OR REPLACE FUNCTION get_geoinfo( _geonameid int ) RETURNS SETOF geonames_cities_details AS $$
  DECLARE
    _exception varchar := 'SQL::get_geoinfo';
    _result geonames_cities_details%ROWTYPE;
    _price int := 5;
  BEGIN
	FOR _result IN SELECT * FROM geonames_cities_details 
		    WHERE geonameid = _geonameid
	LOOP
	  IF _result.price IS NULL THEN
	    IF _result.population > 0 THEN
	      -- SELECT INTO _price gen_price( _result.population );
	      SELECT INTO _price gen_flat_price( _result.population );
	      _result.price := _price;
	    END IF;
	  END IF;
	  RETURN NEXT _result;
	END LOOP;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION get_geoinfo( int ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION get_geoinfo( int ) TO GROUP pagepos;


CREATE OR REPLACE FUNCTION get_geoinfo_user( _geonameid int ) RETURNS SETOF geonames_cities_users AS $$
  DECLARE
    _exception varchar := 'SQL::get_geoinfo_user';
    _result geonames_cities_users%ROWTYPE;
  BEGIN
	FOR _result IN SELECT * FROM geonames_cities_users 
		    WHERE geonameid = _geonameid
	LOOP
	  RETURN NEXT _result;
	END LOOP;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION get_geoinfo_user( int ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION get_geoinfo_user( int ) TO GROUP pagepos;


CREATE OR REPLACE FUNCTION get_geonames_latest( _limit int ) RETURNS SETOF geonames_cities_users AS $$
  DECLARE
    _exception varchar := 'SQL::get_geonames_latest';
    _result geonames_cities_users%ROWTYPE;
  BEGIN
	FOR _result IN SELECT * FROM geonames_cities_users 
		    WHERE status = 'sold' OR status = 'demo' ORDER BY sold_time DESC LIMIT _limit
	LOOP
	  RETURN NEXT _result;
	END LOOP;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION get_geonames_latest( int ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION get_geonames_latest( int ) TO GROUP pagepos;


CREATE OR REPLACE FUNCTION get_geonames_popular( _limit int ) RETURNS SETOF geonames_cities_users AS $$
  DECLARE
    _exception varchar := 'SQL::get_geonames_popular';
    _result geonames_cities_users%ROWTYPE;
  BEGIN
	FOR _result IN SELECT * FROM geonames_cities_users 
		    WHERE status = 'sold' OR status = 'demo' ORDER BY counts DESC LIMIT _limit
	LOOP
	  RETURN NEXT _result;
	END LOOP;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION get_geonames_popular( int ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION get_geonames_popular( int ) TO GROUP pagepos;




CREATE OR REPLACE FUNCTION search_geoname( _name varchar, _limit int, _offset int ) RETURNS SETOF geonames_cities_details AS $$
  DECLARE
    _exception varchar := 'SQL::search_geoname';
    _result geonames_cities_details%ROWTYPE;
  BEGIN
	FOR _result IN SELECT * FROM geonames_cities_details 
		    WHERE name ~* _name OR ascii_name ~* _name ORDER BY ascii_name LIMIT _limit OFFSET _offset
		    -- WHERE name ~* _name ORDER BY name LIMIT _limit OFFSET _offset
	LOOP
	  RETURN NEXT _result;
	END LOOP;
	IF NOT FOUND THEN
	  RAISE EXCEPTION '%', _exception;
	END IF;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION search_geoname( varchar ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION search_geoname( varchar ) TO GROUP pagepos;


CREATE OR REPLACE FUNCTION increment_counts( _geonameid int ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'SQL::increment_counts';
    _time int          := cobra_time();
  BEGIN
    UPDATE geonames2users SET counts = (counts + 1), last_visit_time = _time WHERE geonameid = _geonameid;
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION increment_counts( int ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION increment_counts( int ) TO GROUP pagepos;


CREATE OR REPLACE FUNCTION status_lock( _geonameid int, _email varchar, _url varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'SQL::status_lock';
    _time int          := cobra_time();
    _price int := 5;
  BEGIN
    SELECT INTO _price get_price( _geonameid );
    INSERT INTO geonames2users (geonameid,email,link,status,price) VALUES(_geonameid,_email,_url,'lock',_price);
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION status_lock( int, varchar, varchar ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION status_lock( int, varchar, varchar ) TO GROUP pagepos;


CREATE OR REPLACE FUNCTION verify_lock( _geonameid int, _price float ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'SQL::verify_lock';
    _time int          := cobra_time();
    _status varchar    := '';
  BEGIN
    SELECT INTO _status status FROM geonames2users 
           WHERE geonameid = _geonameid AND price = _price AND status = 'lock';
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION verify_lock( int, float ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION verify_lock( int, float ) TO GROUP pagepos;


CREATE OR REPLACE FUNCTION sell_city( _verify_sign varchar, _txn_id varchar, _txn_type varchar, 
                                      _payment_date varchar, _item_number int, _payment_gross real, 
                                      _first_name varchar, _last_name varchar, _payer_email varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'SQL::sell_city';
    _time int          := cobra_time();
  BEGIN
    UPDATE geonames2users SET sold_time = _time, status = 'sold', 
                              pp_verify_sign = _verify_sign, pp_txn_id = _txn_id, pp_txn_type = _txn_type,
                              pp_payment_date = _payment_date,
                              pp_first_name = _first_name, pp_last_name = _last_name, pp_payer_email = _payer_email
                          WHERE geonameid = _item_number AND status = 'lock';
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION sell_city( varchar, varchar, varchar, varchar, int, real, varchar, varchar, varchar ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION sell_city( varchar, varchar, varchar, varchar, int, real, varchar, varchar, varchar ) TO GROUP pagepos;


-- garbage collector
CREATE OR REPLACE FUNCTION gc_city( _expires int ) RETURNS int AS $$
  DECLARE
    _exception varchar := 'SQL::gc_city';
    _time      integer := cobra_time();
    _affrows   integer := 0;
  BEGIN
    DELETE FROM geonames2users WHERE status = 'lock' AND lock_time + _expires < _time;
    GET DIAGNOSTICS _affrows = ROW_COUNT;
    RETURN _affrows;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION gc_city( int ) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION gc_city( int ) TO GROUP pagepos;
