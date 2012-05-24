-- TAB:4
--

-- DROP TABLE users CASCADE;

DROP TABLE geonames2users CASCADE;
CREATE TABLE geonames2users (
  geonameid		int PRIMARY KEY NOT NULL,
  email			varchar(200) NOT NULL,
  link			varchar(256) NOT NULL,
  -- lock / sold
  status		varchar(32) NOT NULL DEFAULT 'lock',
  price			real NOT NULL DEFAULT '5.00',
  lock_time		int NOT NULL DEFAULT cobra_time(),
  sold_time		int NOT NULL DEFAULT cobra_time(),
  counts		int NOT NULL DEFAULT '0',
  last_visit_time	int NOT NULL DEFAULT cobra_time(),
  -- paypal extensions
  pp_payer_id		varchar(128),
  pp_payer_email	varchar(128),
  pp_first_name		varchar(128),
  pp_last_name		varchar(128),
  pp_txn_id		varchar(128),
  pp_txn_type		varchar(128),
  pp_verify_sign	varchar(128),
  pp_payment_date	varchar(128)
);
GRANT SELECT, INSERT, UPDATE, DELETE ON geonames2users TO pagepos_admin;
-- GRANT SELECT ON geonames2users TO pagepos_user;
