-- 1. Step: Allow cobra users to connect
-- 1.1 modify pg_hba.conf
-- host		pagepos		pagepos_user		127.0.0.1/32	md5
-- host		pagepos		pagepos_admin		127.0.0.1/32	md5
-- 1.2 restart postgres
--
--
-- 2. Step: Reset the database (run this file on the db)
-- psql -f dbinit.sql -U postgres -h localhost
--
--
-- clean the shit
DROP DATABASE pagepos;


-- admin user
DROP USER pagepos_admin;
-- cobra user
DROP USER pagepos_user;

-- create the database
-- maybe LOCATION = '/path/to/db'
CREATE DATABASE pagepos WITH OWNER = postgres ENCODING = 'UTF8';

-- create the users
-- change the passwords
-- maybe VALID UNTIL ''

-- cobra admin user
CREATE USER pagepos_admin WITH PASSWORD 'pagepos_admin' NOCREATEDB NOCREATEUSER;

-- cobra user
CREATE USER pagepos_user WITH PASSWORD 'pagepos_user' NOCREATEDB NOCREATEUSER;

-- groups
DROP GROUP pagepos;

CREATE GROUP pagepos WITH USER pagepos_admin,pagepos_user;
