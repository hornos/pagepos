--
-- tables
--
GRANT SELECT, INSERT, UPDATE, DELETE ON groups TO cobra_admin;
--
-- functions
--
GRANT EXECUTE ON FUNCTION groups_read( varchar )       TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION groups_valid( varchar )      TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION groups_force_read( varchar ) TO       cobra_admin;
