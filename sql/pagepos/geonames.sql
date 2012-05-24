-- TAB:4
--

-- Begin geonames
--
-- DROP TABLE geonames CASCADE;
CREATE TABLE geonames (
  -- integer id of record in geonames database
  geonameid         int             PRIMARY KEY NOT NULL CHECK (geonameid > 0),
  -- name of geographical point (utf8) varchar(200)
  name              varchar(200),
  -- name of geographical point in plain ascii characters, varchar(200)
  ascii_name        varchar(200),
  -- alternatenames, comma separated varchar(4000) (varchar(5000) for SQL Server)
  alternate_names   varchar(5000),
  -- latitude in decimal degrees (wgs84)
  latitude          numeric NOT NULL,
  -- longitude in decimal degrees (wgs84)
  longitude         numeric NOT NULL,
  -- feature class     : see http://www.geonames.org/export/codes.html, char(1)
  feature_class     varchar(1),
  -- feature code      : see http://www.geonames.org/export/codes.html, varchar(10)
  feature_code      varchar(10),
  -- ISO-3166 2-letter country code, 2 characters
  country_code      varchar(2) NOT NULL,
  -- alternate country codes, comma separated, ISO-3166 2-letter country code, 60 characters
  alternate_country_codes varchar(60),
  -- fipscode (subject to change to iso code), isocode for the us and ch, 
  -- see file admin1Codes.txt for display names of this code; varchar(20)
  admin1_code       varchar(20),
  -- code for the second administrative division, a county in the US, see file admin2Codes.txt; varchar(80) 
  admin2_code       varchar(80), 
  -- code for third level administrative division, varchar(20)
  admin3_code       varchar(20),
  -- code for fourth level administrative division, varchar(20)
  admin4_code       varchar(20),
  -- population integer
  population        int NOT NULL DEFAULT '0',
  -- in meters, integer
  elevation         int DEFAULT '0',
  -- average elevation of 30'x30' (ca 900mx900m) area in meters, integer
  gtopo30           int DEFAULT '0',
  -- the timezone id (see file timeZone.txt)
  timezone          varchar(200),
  -- date of last modification in yyyy-MM-dd format
  modification_date timestamp
);

--
-- access grants
--
GRANT SELECT, INSERT, UPDATE, DELETE ON geonames TO pagepos_admin;
-- GRANT SELECT ON geonames TO pagepos_user;



-- DROP TABLE admin1_codes CASCADE;
CREATE TABLE admin1_codes (
  country_code		varchar(2)	NOT NULL,
  admin1_code		varchar(20)	NOT NULL,
  admin1_name		varchar(200),
  UNIQUE( country_code, admin1_code )
);
GRANT SELECT, INSERT, UPDATE, DELETE ON admin1_codes TO pagepos_admin;
-- GRANT SELECT ON geonames TO pagepos_user;


-- DROP TABLE country_codes CASCADE;
CREATE TABLE country_codes (
  country_code		varchar(2)	PRIMARY KEY NOT NULL,
  country_name		varchar(200)	NOT NULL  
);
GRANT SELECT, INSERT, UPDATE, DELETE ON country_codes TO pagepos_admin;
-- GRANT SELECT ON geonames TO pagepos_user;
